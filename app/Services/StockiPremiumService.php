<?php

namespace App\Services;

use PDO;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StockiPremiumRepository;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Exceptions\ValidationException;
use App\Repositories\HistoricRepository;

class StockiPremiumService
{
    private const MIN_INSTALLMENTS = 1;
    private const MAX_INSTALLMENTS = 3;
    private const PAYMENT_INTERVAL_MONTHS = 1;
    private const TYPE = SubscriptionType::STOCKI_PREMIUM;

    private function createAdvancePayment(int $stockiPId, int $idclient, string $comment, float $avancepaye, string $date): void
    {
        $this->paymentRepo->addInstallment($stockiPId, self::TYPE, $avancepaye, $comment !== '' ? $comment : 'Avance', $date, PaymentStatus::PAID, $date);
        $this->historicRepo->addHistoric($stockiPId, self::TYPE, $avancepaye, $idclient);
    }

    // Reste à payer réparti en échéances mensuelles à partir de la date de début
    private function createUnpaidPayments(int $stockiPId, float $remaining, int $nbrEcheance, string $datedebut): void
    {
        $base = round($remaining / $nbrEcheance, 2);
        $date = new \DateTimeImmutable($datedebut);
        for ($i = 1; $i <= $nbrEcheance; $i++) {
            $amount = $i === $nbrEcheance ? round($remaining - ($base * ($nbrEcheance - 1)), 2) : $base;
            $date = $date->modify('+' . self::PAYMENT_INTERVAL_MONTHS . ' month');
            $this->paymentRepo->addInstallment($stockiPId, self::TYPE, $amount, "Échéance {$i} - Licence à vie", $date->format('Y-m-d'), PaymentStatus::UNPAID, null);
        }
    }

    private function valideNumberOfInstallment(int $nbrEcheance): void
    {
        if ($nbrEcheance > self::MAX_INSTALLMENTS || $nbrEcheance < self::MIN_INSTALLMENTS) {
            throw ValidationException::invalidValue("Le nombre d'échéances doit être compris entre " . self::MIN_INSTALLMENTS . " et " . self::MAX_INSTALLMENTS . ".");
        }
    }

    private function validateAmounts(float $montant, float $avance): void
    {
        if ($montant <= 0) {
            throw ValidationException::invalidValue("Le montant doit être supérieur à 0.");
        }
        if ($avance > $montant) {
            throw ValidationException::invalidValue("L'avance ne peut pas dépasser le montant de la licence.");
        }
    }

    // Une licence est soldée quand plus rien ne reste à payer
    private function isCompleted(array $stockiP): bool
    {
        return round((float) $stockiP['reste'], 3) <= 0;
    }

    public function __construct(
        private PDO $connection,
        private StockiPremiumRepository $stockiPRepo,
        private ClientRepository $clientRepo,
        private PaymentRepository $paymentRepo,
        private HistoricRepository $historicRepo
    ) {}

    public function getStockiPremiums(): array
    {
        return $this->stockiPRepo->getStockiPremiums();
    }

    public function getClientsForAutoComplete(): array
    {
        return $this->clientRepo->getClients();
    }

    public function getStockiPremiumHistorics(int $stockiId): array
    {
        return $this->historicRepo->getHistoricsByParentIdAndType($stockiId, self::TYPE);
    }

    public function getInstallments(int $stockiId): array
    {
        $this->stockiPRepo->getStockiPremiumById($stockiId);
        return $this->paymentRepo->getParentInstallments($stockiId, self::TYPE);
    }

    public function createStockiPremium(
        int $clientId,
        float $couttot,
        float $avancepaye,
        string $comment,
        int $nbrEcheance,
        string $datedebut
    ): void {
        $this->validateAmounts($couttot, $avancepaye);
        if (!$this->clientRepo->clientExists($clientId)) {
            throw ValidationException::NotFound("Client introuvable.");
        }
        if ($this->stockiPRepo->isStockiPremiumExists($clientId)) {
            throw ValidationException::NotAllowed("Ce client a déjà une licence à vie.");
        }
        $remaining = round($couttot - $avancepaye, 3);
        if ($remaining > 0) {
            $this->valideNumberOfInstallment($nbrEcheance);
        }
        $this->connection->beginTransaction();
        try {
            $stockiPId = $this->stockiPRepo->addStockiPremium($clientId, $couttot, $datedebut);
            if ($avancepaye > 0) {
                $this->createAdvancePayment($stockiPId, $clientId, $comment, $avancepaye, $datedebut);
            }
            if ($remaining > 0) {
                $this->createUnpaidPayments($stockiPId, $remaining, $nbrEcheance, $datedebut);
            }
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateStockiPremium(
        int $stockiPId,
        float $couttot,
        int $nbrEcheance,
        string $datedebut
    ): void {
        $old = $this->stockiPRepo->getStockiPremiumById($stockiPId);
        if ($this->isCompleted($old)) {
            throw ValidationException::NotAllowed("Cette licence est entièrement payée : elle n'est plus modifiable.");
        }
        $oldInstallments = (int) $old['nbimpayees'];
        // Les échéances restantes ne sont régénérées que si le montant ou leur nombre change
        $regenerate = $couttot !== (float) $old['montant'] || $nbrEcheance !== $oldInstallments;
        if ($regenerate && $this->paymentRepo->hasPaidInstallment($stockiPId, self::TYPE)) {
            throw ValidationException::NotAllowed("Vous ne pouvez pas modifier le montant ou le nombre d'échéances d'une licence ayant déjà un paiement.");
        }
        $this->validateAmounts($couttot, 0);
        if ($regenerate) {
            $this->valideNumberOfInstallment($nbrEcheance);
        }
        $this->connection->beginTransaction();
        try {
            if ($regenerate) {
                $this->paymentRepo->deleteUnpaidInstallments($stockiPId, self::TYPE);
                $this->createUnpaidPayments($stockiPId, $couttot, $nbrEcheance, $datedebut);
            }
            $this->stockiPRepo->updateStockiPremium($stockiPId, $couttot, $datedebut);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    // Paiement d'une échéance : passe à "payée" et entre dans l'historique
    public function payInstallment(int $stockiPId, int $idpay): void
    {
        $stockiP = $this->stockiPRepo->getStockiPremiumById($stockiPId);
        $payment = $this->paymentRepo->getInstallmentById($idpay);
        if ($payment === null || $payment['parent_type'] !== self::TYPE || (int) $payment['parent_id'] !== $stockiPId) {
            throw ValidationException::NotFound("Échéance introuvable.");
        }
        if ($payment['status'] === PaymentStatus::PAID) {
            throw ValidationException::NotAllowed("Cette échéance est déjà payée.");
        }
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->updateInstallmentStatus($idpay, PaymentStatus::PAID, date('Y-m-d'));
            $this->historicRepo->addHistoric($stockiPId, self::TYPE, (float) $payment['echeance'], (int) $stockiP['id_client']);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteStockiPremium(int $stockiPId): void
    {
        $this->stockiPRepo->getStockiPremiumById($stockiPId);
        if ($this->paymentRepo->hasPaidInstallment($stockiPId, self::TYPE)) {
            throw ValidationException::NotAllowed(
                "Vous ne pouvez pas supprimer une licence ayant déjà un paiement."
            );
        }
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->deleteUnpaidInstallments($stockiPId, self::TYPE);
            $this->stockiPRepo->deleteStockiPremium($stockiPId);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
