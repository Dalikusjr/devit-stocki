<?php

namespace App\Services;

use PDO;
use App\Repositories\HistoricRepository;
use App\Enums\PaymentStatus;
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
        $stocki = $this->stockiRepo->getStockiById($stockiId);
        if ($stocki['status'] === PaymentStatus::PAID) {
            throw ValidationException::notAllowed(
                "Cet abonnement a déjà été payé pour la période en cours. Les modifications seront possibles après le renouvellement."
            );
        }
        return  $stocki;
    }

    private function getFirstDayOfCurrentMonthIfPaid(string $status): string
    {
        return $status === PaymentStatus::PAID ? new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d') : '';
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
        return $this->stockiRepo->getStockis();
    }

    public function getClientsForAutoComplete(): array
    {
        return $this->clientRepo->getClients();
    }

    public function getStockiHistorics(int $stockiId): array
    {
        return $this->historicRepo->getHistoricsByParentIdAndType($stockiId, SubscriptionType::STOCKI);
    }
    public function createStocki(int $clientId, string $payment_type, float $montant, string $status): void
    {
        if ($this->stockiRepo->isStockiExists($clientId)) {
            throw ValidationException::NotAllowed("Abonnement déja existe");
        }
        $this->connection->beginTransaction();
        try {
            $stockiId = $this->stockiRepo->addStocki($clientId, $payment_type);
            $date = new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d');
            $this->paymentRepo->addInstallment($stockiId, SubscriptionType::STOCKI, $montant, "Abonnement - Stocki #{$stockiId}", $date, $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateStocki(int $stockiId, string $payment_type, float $montant, string $status): void
    {
        $stocki = $this->ensureCanModifyOrDelete($stockiId);

        $this->connection->beginTransaction();
        try {
            $this->stockiRepo->updateStocki($stockiId, $payment_type);
            $this->paymentRepo->updateInstallment($stocki['idpay'], $montant, "Abonnement - Stocki #{$stockiId}", $stocki['dateech'], $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($stockiId,   $stocki['id_client'], $montant,  $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateStockiStatus(int $stockiId, float $montant, string $status): void
    {
        $stocki = $this->ensureCanModifyOrDelete($stockiId);
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
