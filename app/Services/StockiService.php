<?php

namespace App\Services;

use PDO;
use App\Repositories\HistoricRepository;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\SubscriptionType;
use App\Repositories\StockiRepository;
use App\Exceptions\ValidationException;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;

class StockiService
{
    private function handlePaymentStatus(int $stockiId, int $idclient, float $montant, string $status): void
    {
        if ($status === PaymentStatus::PAID) {
            $this->historicRepo->addHistoric($stockiId, SubscriptionType::STOCKI, $montant, $idclient);
        }
    }


    private function ensureCanModifyOrDelete(int $stockiId): array
    {
        $stocki = $this->renewIfDue($this->stockiRepo->getStockiById($stockiId));
        if ($stocki['status'] === PaymentStatus::PAID) {
            throw ValidationException::notAllowed(
                "Cet abonnement a déjà été payé pour la période en cours. Les modifications seront possibles après le renouvellement."
            );
        }
        return  $stocki;
    }

    private function getFirstDayOfCurrentMonthIfPaid(string $status): ?string
    {
        return $status === PaymentStatus::PAID ? new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d') : null;
    }

    private function validateAmount(float $montant): void
    {
        if ($montant <= 0) {
            throw ValidationException::invalidValue("Le montant doit être supérieur à 0.");
        }
    }

    /**
     * Début de la période de facturation en cours :
     * - mensuel : chaque début de mois (la première période part de la date de début) ;
     * - trimestriel / annuel : tous les 3 / 12 mois à partir de la date de début.
     */
    private function currentPeriodStart(string $startDate, string $paymentType): string
    {
        $start = new \DateTimeImmutable(substr($startDate, 0, 10));
        $today = new \DateTimeImmutable('today');
        if ($today <= $start) {
            return $start->format('Y-m-d');
        }
        if ($paymentType === PaymentType::MONTHLY) {
            $firstOfMonth = $today->modify('first day of this month');
            return ($firstOfMonth < $start ? $start : $firstOfMonth)->format('Y-m-d');
        }
        $step = $paymentType === PaymentType::ANNUAL ? 12 : 3;
        $months = ((int) $today->format('Y') - (int) $start->format('Y')) * 12
            + (int) $today->format('n') - (int) $start->format('n');
        if ((int) $today->format('j') < (int) $start->format('j')) {
            $months--;
        }
        $periods = intdiv(max($months, 0), $step);
        return $start->modify('+' . ($periods * $step) . ' months')->format('Y-m-d');
    }

    // Nouvelle période commencée : l'échéance avance et repasse à "non payé"
    private function renewIfDue(array $stocki): array
    {
        $periodStart = $this->currentPeriodStart($stocki['startDate'], $stocki['payment_type']);
        if ($stocki['dateech'] < $periodStart) {
            $this->paymentRepo->updateInstallment((int) $stocki['idpay'], (float) $stocki['echeance'], $stocki['commentaire'], $periodStart, PaymentStatus::UNPAID, null);
            $stocki['dateech'] = $periodStart;
            $stocki['status'] = PaymentStatus::UNPAID;
            $stocki['datepay'] = null;
        }
        return $stocki;
    }

    public function __construct(
        private PDO $connection,
        private StockiRepository $stockiRepo,
        private HistoricRepository $historicRepo,
        private PaymentRepository $paymentRepo,
        private ClientRepository $clientRepo
    ) {}

    public function getStockis(): array
    {
        return array_map(fn(array $stocki) => $this->renewIfDue($stocki), $this->stockiRepo->getStockis());
    }

    public function getClientsForAutoComplete(): array
    {
        return $this->clientRepo->getClients();
    }

    public function getStockiHistorics(int $stockiId): array
    {
        return $this->historicRepo->getHistoricsByParentIdAndType($stockiId, SubscriptionType::STOCKI);
    }
    public function createStocki(int $clientId, string $payment_type, float $montant, string $status, string $startDate): void
    {
        $this->validateAmount($montant);
        if (!$this->clientRepo->clientExists($clientId)) {
            throw ValidationException::NotFound("Client introuvable.");
        }
        if ($this->stockiRepo->isStockiExists($clientId)) {
            throw ValidationException::NotAllowed("Ce client a déjà un abonnement Stocki.");
        }
        $this->connection->beginTransaction();
        try {
            $stockiId = $this->stockiRepo->addStocki($clientId, $payment_type, $startDate);
            $date = $this->currentPeriodStart($startDate, $payment_type);
            $this->paymentRepo->addInstallment($stockiId, SubscriptionType::STOCKI, $montant, "Abonnement - Stocki #{$stockiId}", $date, $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($stockiId, $clientId, $montant, $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateStocki(int $stockiId, string $payment_type, float $montant, string $status, string $startDate): void
    {
        $this->validateAmount($montant);
        $stocki = $this->ensureCanModifyOrDelete($stockiId);

        $this->connection->beginTransaction();
        try {
            $this->stockiRepo->updateStocki($stockiId, $payment_type, $startDate);
            $this->paymentRepo->updateInstallment($stocki['idpay'], $montant, "Abonnement - Stocki #{$stockiId}", $this->currentPeriodStart($startDate, $payment_type), $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($stockiId,   $stocki['id_client'], $montant,  $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateStockiStatus(int $stockiId, string $status): void
    {
        $stocki = $this->ensureCanModifyOrDelete($stockiId);
        $montant = (float) $stocki['echeance'];
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->updateInstallmentStatus($stocki['idpay'],  $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($stockiId,  $stocki['id_client'], $montant, $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteStocki(int $stockiId): void
    {
        $stocki = $this->ensureCanModifyOrDelete($stockiId);
        $this->connection->beginTransaction();
        try {
            $this->stockiRepo->deleteStocki($stockiId);
            $this->paymentRepo->deleteInstallment($stocki['idpay']);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
