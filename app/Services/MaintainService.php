<?php

namespace App\Services;

use PDO;
use App\Repositories\HistoricRepository;
use App\Repositories\MaintainRepository;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Exceptions\ValidationException;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;

class MaintainService
{
    private function handlePaymentStatus(int $id_proj, float $montant, int $idclient, string $status): void
    {
        if ($status === PaymentStatus::PAID) {
            $this->historicRepo->addHistoric($id_proj, SubscriptionType::MAINTAIN, $montant, $idclient);
        }
    }

    private function getFirstDayOfCurrentMonthIfPaid(string $status): string
    {
        return $status === PaymentStatus::PAID ? new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d') : '';
    }

    private function getFirstDayOfCurrentMonth(): string
    {
        return new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d');
    }

    private function synchronizeMaintainPayment(array $maintain, int $idproj, float $montant, string $status): void
    {
        $date = $this->getFirstDayOfCurrentMonthIfPaid($status);
        if ($maintain['id_proj'] === $idproj) {
            $this->paymentRepo->updateInstallment($maintain['idpay'], $montant, "Maintenance - Projet #{$idproj}", $maintain['dateech'], $status, $date);
        } else {
            $this->paymentRepo->deleteInstallment($maintain['idpay']);
            $this->paymentRepo->addInstallment($idproj, SubscriptionType::MAINTAIN, $montant, "Maintenance - Projet #{$idproj}", $maintain['dateech'], $status, $date);
        }
    }

    private function ensureCanModifyOrDelete(int $maintainId): array
    {
        $maintain = $this->maintainRepo->getMaintainById($maintainId);
        if ($maintain['status'] === PaymentStatus::PAID) {
            throw ValidationException::notAllowed(
                "Cet abonnement a déjà été payé pour la période en cours. Les modifications seront possibles après le renouvellement."
            );
        }
        return  $maintain;
    }

    public function __construct(
        private PDO $connection,
        private MaintainRepository $maintainRepo,
        private PaymentRepository $paymentRepo,
        private HistoricRepository $historicRepo,
        private ProjectRepository $projectRepo
    ) {}

    public function getMaintains(): array
    {
        return $this->maintainRepo->getMaintains();
    }

    public function getProjectsForAutoComplete(): array
    {
        return $this->projectRepo->getProjects();
    }

    public function getMaintainHistorics(int $maintainId): array
    {
        return $this->historicRepo->getHistoricsByParentIdAndType($maintainId, SubscriptionType::MAINTAIN);
    }

    public function createMaintain(int $idproj, int $idclient, float $montant, string $status): void
    {
        if ($this->maintainRepo->isMaintainExists($idproj)) {
            throw ValidationException::NotAllowed("Abonnement déja existe");
        }
        $this->connection->beginTransaction();
        try {

            $this->maintainRepo->addMaintain($idproj);
            $this->paymentRepo->addInstallment($idproj, SubscriptionType::MAINTAIN, $montant, "Maintenance - Projet #{$idproj}", $this->getFirstDayOfCurrentMonth(), $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($idproj,  $montant,  $idclient,  $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateMaintain(int $maintainId, int $idproj, int $idclient, float $montant, string $status): void
    {
        $maintain = $this->ensureCanModifyOrDelete($maintainId);
        if ($this->maintainRepo->isMaintainExistsExceptUpdated($maintainId, $idproj)) {
            throw ValidationException::NotAllowed("Abonnement déja existe");
        }
        $this->connection->beginTransaction();
        try {
            $this->maintainRepo->updateMaintain($maintainId, $idproj);
            $this->synchronizeMaintainPayment($maintain, $idproj, $montant, $status);
            $this->handlePaymentStatus($idproj, $montant,  $idclient,   $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateMaintainStatus(int $maintainId, float $montant, string $status): void
    {
        $maintain = $this->ensureCanModifyOrDelete($maintainId);
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->updateInstallmentStatus($maintain['idpay'],  $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($maintain['id_proj'],  $montant,  $maintain['idclient'],  $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteMaintain(int $id): void
    {
        $maintain = $this->ensureCanModifyOrDelete($id);
        $this->connection->beginTransaction();
        try {
            $this->maintainRepo->deleteMaintain($id);
            $this->paymentRepo->deleteInstallment($maintain['idpay']);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
