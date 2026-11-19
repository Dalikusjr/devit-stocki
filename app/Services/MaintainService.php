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
    // Les paiements et l'historique d'un abonnement sont rattachés à l'id de l'abonnement
    private function handlePaymentStatus(int $maintainId, float $montant, int $idclient, string $status): void
    {
        if ($status === PaymentStatus::PAID) {
            $this->historicRepo->addHistoric($maintainId, SubscriptionType::MAINTAIN, $montant, $idclient);
        }
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

    private function getFirstDayOfCurrentMonth(): string
    {
        return new \DateTimeImmutable()->modify('first day of this month')->format('Y-m-d');
    }

    private function synchronizeMaintainPayment(array $maintain, int $idproj, float $montant, string $status): void
    {
        $date = $this->getFirstDayOfCurrentMonthIfPaid($status);
        $this->paymentRepo->updateInstallment($maintain['idpay'], $montant, "Maintenance - Projet #{$idproj}", $maintain['dateech'], $status, $date);
    }

    /**
     * Période mensuelle (cahier des charges §2.3 : non payé par défaut chaque mois).
     * Au changement de mois, l'échéance passe au 1er du mois et l'abonnement repasse à « non payé »,
     * sauf s'il a déjà été payé pendant le mois en cours.
     */
    private function renewIfDue(array $maintain): array
    {
        $periodStart = $this->getFirstDayOfCurrentMonth();
        if ($maintain['dateech'] >= $periodStart) {
            return $maintain;
        }
        $paidThisPeriod = $maintain['status'] === PaymentStatus::PAID && ($maintain['datepay'] ?? '') >= $periodStart;
        $status = $paidThisPeriod ? PaymentStatus::PAID : PaymentStatus::UNPAID;
        $datepay = $paidThisPeriod ? $maintain['datepay'] : null;
        $this->paymentRepo->updateInstallment((int) $maintain['idpay'], (float) $maintain['echeance'], $maintain['commentaire'], $periodStart, $status, $datepay);
        return array_merge($maintain, ['dateech' => $periodStart, 'status' => $status, 'datepay' => $datepay]);
    }

    private function ensureCanModifyOrDelete(int $maintainId): array
    {
        $maintain = $this->renewIfDue($this->maintainRepo->getMaintainById($maintainId));
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
        return array_map(fn(array $maintain) => $this->renewIfDue($maintain), $this->maintainRepo->getMaintains());
    }

    public function getProjectsForAutoComplete(): array
    {
        return $this->projectRepo->getProjects();
    }

    public function getMaintainHistorics(int $maintainId): array
    {
        return $this->historicRepo->getHistoricsByParentIdAndType($maintainId, SubscriptionType::MAINTAIN);
    }

    public function createMaintain(int $idproj, float $montant, string $status): void
    {
        $this->validateAmount($montant);
        // Le client est celui du projet (lève "Projet introuvable." si le projet n'existe pas)
        $idclient = (int) $this->projectRepo->getProjectById($idproj)['idclient'];
        if ($this->maintainRepo->isMaintainExists($idproj)) {
            throw ValidationException::NotAllowed("Ce projet a déjà un abonnement de maintenance.");
        }
        $this->connection->beginTransaction();
        try {

            $maintainId = $this->maintainRepo->addMaintain($idproj);
            $this->paymentRepo->addInstallment($maintainId, SubscriptionType::MAINTAIN, $montant, "Maintenance - Projet #{$idproj}", $this->getFirstDayOfCurrentMonth(), $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($maintainId,  $montant,  $idclient,  $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateMaintain(int $maintainId, int $idproj, float $montant, string $status): void
    {
        $this->validateAmount($montant);
        $maintain = $this->ensureCanModifyOrDelete($maintainId);
        $idclient = (int) $this->projectRepo->getProjectById($idproj)['idclient'];
        // contrôle de doublon uniquement si on change de projet
        if ($idproj !== (int) $maintain['id_proj'] && $this->maintainRepo->isMaintainExistsExceptUpdated($maintainId, $idproj)) {
            throw ValidationException::NotAllowed("Ce projet a déjà un abonnement de maintenance.");
        }
        $this->connection->beginTransaction();
        try {
            $this->maintainRepo->updateMaintain($maintainId, $idproj);
            $this->synchronizeMaintainPayment($maintain, $idproj, $montant, $status);
            $this->handlePaymentStatus($maintainId, $montant,  $idclient,   $status);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function updateMaintainStatus(int $maintainId, string $status): void
    {
        $maintain = $this->ensureCanModifyOrDelete($maintainId);
        $montant = (float) $maintain['echeance'];
        $this->connection->beginTransaction();
        try {
            $this->paymentRepo->updateInstallmentStatus($maintain['idpay'],  $status, $this->getFirstDayOfCurrentMonthIfPaid($status));
            $this->handlePaymentStatus($maintainId,  $montant,  (int) $maintain['idclient'],  $status);
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
