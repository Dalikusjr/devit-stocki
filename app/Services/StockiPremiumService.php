<?php

namespace App\Services;

use PDO;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StockiPremiumRepository;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Enums\StockiStatus;
use App\Exceptions\ValidationException;
use App\Repositories\HistoricRepository;

class StockiPremiumService
{
    private const MIN_INSTALLMENTS = 1;
    private const MAX_INSTALLMENTS = 3;
    private const PAYMENT_INTERVAL_MONTHS = 1;
    private function determineStockiPStatus(float $avancepaye, float $couttot): string
    {
        return $avancepaye === $couttot ? StockiStatus::COMPLETED : StockiStatus::PENDING;
    }

    private function createAdvancePayment(int $stockiPId, int $idclient, string $comment, float $avancepaye): void
    {
        $dateCourante = new \DateTimeImmutable('first day of this month')->format('Y-m-d');
        $this->paymentRepo->addInstallment((int) $stockiPId, SubscriptionType::STOCKI_PREMIUM, $avancepaye, $comment, $dateCourante, PaymentStatus::PAID,  $dateCourante);
        $this->historicRepo->addHistoric((int) $stockiPId, SubscriptionType::STOCKI_PREMIUM, $avancepaye, $idclient);
    }

    private function createUnpaidPayments(int $stockiPId, float $avancepaye, float $couttot, int $nbrEcheance, int $nbrMonths): void
    {
        $remaining = $couttot - $avancepaye;
        $base = round($remaining / $nbrEcheance, 2);
        $dateCourante = new \DateTimeImmutable('first day of this month');
        for ($i = 1; $i <= $nbrEcheance; $i++) {
            if ($i === $nbrEcheance) {
                $amount = round(
                    $remaining - ($base * ($nbrEcheance - 1)),
                    2
                );
            } else {
                $amount = $base;
            }
            $dateCourante = $dateCourante->modify('+' . $nbrMonths . ' month');
            $this->paymentRepo->addInstallment((int) $stockiPId, SubscriptionType::STOCKI_PREMIUM, $amount, "Échéance {$i} - Stocki Premium", $dateCourante->format('Y-m-d'), PaymentStatus::UNPAID,  '');
        }
    }
    private function valideNumberOfInstallment(int $nbrEcheance): void
    {
        if ($nbrEcheance > self::MAX_INSTALLMENTS || $nbrEcheance < self::MIN_INSTALLMENTS) {
            ValidationException::invalidValue("Nombre d'échéances doit etre >=" . self::MIN_INSTALLMENTS . " et <= " . self::MAX_INSTALLMENTS);
        }
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
        return $this->historicRepo->getHistoricsByParentIdAndType($stockiId, SubscriptionType::STOCKI_PREMIUM);
    }

    public function createStockiPremium(
        int $clientId,
        float $couttot,
        float $avancepaye,
        string $comment,
        int $nbrEcheance
    ): void {
        if ($this->stockiPRepo->isStockiPremiumExists($clientId)) {
            throw ValidationException::NotAllowed("Abonnement déja existe");
        }
        $this->valideNumberOfInstallment($nbrEcheance);
        $this->connection->beginTransaction();
        try {
            $status = $this->determineStockiPStatus($avancepaye,  $couttot);
            $stockiPId = $this->stockiPRepo->addStockiPremium($clientId,  $couttot, $status);
            if ($status === StockiStatus::COMPLETED) {
                $this->createAdvancePayment($stockiPId, $clientId, $comment,  $avancepaye);
            } else {
                if ($avancepaye > 0) {
                    $this->createAdvancePayment($stockiPId, $clientId, $comment,  $avancepaye);
                }
                $this->createUnpaidPayments($stockiPId,  $avancepaye, $couttot,  $nbrEcheance, self::PAYMENT_INTERVAL_MONTHS);
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
        int $nbrEcheance
    ): void {
        $oldStockiP = $this->stockiPRepo->getStockiPremiumById($stockiPId);
        $oldInstallments = $this->paymentRepo->countParentInstallments($stockiPId, SubscriptionType::STOCKI_PREMIUM);
        if ($oldStockiP && $oldStockiP['status'] != StockiStatus::PENDING) {
            throw ValidationException::NotAllowed("Vous ne pouvez pas modifiéer un abonnement complété");
        }
        if ($this->paymentRepo->hasPaidInstallment($stockiPId, SubscriptionType::STOCKI_PREMIUM) && ($couttot !== $oldStockiP['montant'] || $nbrEcheance !== $oldInstallments)) {
            throw ValidationException::NotAllowed("Vous ne pouvez pas modifiéer un abonnement déja a une écheance payer");
        }
        $this->valideNumberOfInstallment($nbrEcheance);
        $this->connection->beginTransaction();
        try {
            if ($couttot !== $oldStockiP['montant'] || $nbrEcheance !== $oldInstallments) {
                $this->paymentRepo->deleteUnpaidInstallments($stockiPId, SubscriptionType::STOCKI_PREMIUM);
                $this->createUnpaidPayments($stockiPId,  0, $couttot,  $nbrEcheance, self::PAYMENT_INTERVAL_MONTHS);
            }
            $this->stockiPRepo->updateStockiPremium($stockiPId,  $couttot);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteStockiPremium(int $stockiPId): void
    {
        $stockiP = $this->stockiPRepo->getStockiPremiumById($stockiPId);
        if ($stockiP['status'] !== StockiStatus::PENDING) {
            throw ValidationException::NotAllowed(
                "Vous ne pouvez pas supprimer un abonnement qui est complété."
            );
        }
        if ($this->paymentRepo->hasPaidInstallment($stockiPId, SubscriptionType::STOCKI_PREMIUM)) {
            throw ValidationException::NotAllowed(
                "Vous ne pouvez pas supprimer un abonnement ayant déjà une échéance payée."
            );
        }
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->deleteUnpaidInstallments($stockiPId, SubscriptionType::STOCKI_PREMIUM);
            $this->stockiPRepo->deleteStockiPremium($stockiPId);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
