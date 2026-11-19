<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\ProjectStatus;
use App\Enums\SubscriptionType;
use App\Exceptions\ValidationException;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;

class PaymentService
{
    private const TYPE = SubscriptionType::PROJECT;

    public function __construct(
        private PaymentRepository $paymentRepo,
        private ProjectRepository $projectRepo,
        private ClientRepository $clientRepo
    ) {}

    // Une tranche de projet, ou une erreur si elle n'existe pas
    private function getProjectInstallment(int $idpay): array
    {
        $payment = $this->paymentRepo->getInstallmentById($idpay);
        if ($payment === null || $payment['parent_type'] !== self::TYPE) {
            throw ValidationException::NotFound("Échéance introuvable.");
        }
        return $payment;
    }

    // Projet modifiable (non archivé), ou une erreur
    private function getEditableProject(int $idproj): array
    {
        $project = $this->projectRepo->getProjectById($idproj);
        if ($project['status'] === ProjectStatus::ARCHIVED) {
            throw ValidationException::NotAllowed("Ce projet est archivé : ses paiements ne sont plus modifiables.");
        }
        return $project;
    }

    private function validatePayment(float $montant, string $dateech, string $status, ?string $datepay): ?string
    {
        if ($montant <= 0) {
            throw ValidationException::invalidValue("Le montant doit être supérieur à 0.");
        }
        if ($status !== PaymentStatus::PAID) {
            return null;
        }
        // tranche payée sans date : date du jour
        return $datepay ?: date('Y-m-d');
    }

    // Le projet est "terminé" dès que tout son coût est payé, "actif" sinon
    private function syncProjectStatus(array $project): void
    {
        $idproj = (int) $project['idproj'];
        $paid = $this->paymentRepo->sumParentPaidInstallments($idproj, self::TYPE);
        $status = $paid >= (float) $project['couttot'] ? ProjectStatus::COMPLETED : ProjectStatus::ACTIVE;
        if ($status !== $project['status']) {
            $this->projectRepo->updateStatus($idproj, $status);
        }
    }

    public function getPayment(int $idpay): array
    {
        return $this->getProjectInstallment($idpay);
    }

    public function getProjectsByClient(int $idclient): array
    {
        $projets = $this->projectRepo->getProjectsByClient($idclient);
        foreach ($projets as &$projet) {
            $idproj = (int) $projet['idproj'];
            $projet['tranches'] = $this->paymentRepo->getParentInstallments($idproj, self::TYPE);
            $projet += $this->getPaymentDataForDisplay($idproj);
        }
        unset($projet);
        return $projets;
    }

    public function getClientsForPaymentDashboard(): array
    {
        $clients = $this->clientRepo->getClients();
        $counts = $this->projectRepo->countProjectsPerClients();
        $filteredRows = [];
        foreach ($clients as $client) {
            $nb = $counts[$client['idclient']] ?? 0;

            if ($nb > 0) {
                $client['nbprojects'] = $nb;
                $filteredRows[] = $client;
            }
        }
        return $filteredRows;
    }

    public function createPayment(float $montant, string $comment, string $dateech, string $status, int $idproj, ?string $datepay): int
    {
        $project = $this->getEditableProject($idproj);
        $datepay = $this->validatePayment($montant, $dateech, $status, $datepay);
        if (($this->paymentRepo->sumParentInstallments($idproj, self::TYPE) + $montant) > (float) $project['couttot']) {
            throw ValidationException::amountExceeded();
        }
        $idpay = $this->paymentRepo->addInstallment($idproj, self::TYPE, $montant, $comment, $dateech, $status, $datepay);
        $this->syncProjectStatus($project);
        return $idpay;
    }

    // Tranche (si demandée) + compteurs et reste à payer du projet
    public function getPaymentDataForDisplay(int $idproj, int $idpay = 0): array
    {
        $payment = [];
        if ($idpay != 0) $payment = $this->getProjectInstallment($idpay);
        $payment['tranches_paye'] = $this->paymentRepo->countParentPaidInstallments($idproj, self::TYPE);
        $payment['tranches_total'] = $this->paymentRepo->countParentInstallments($idproj, self::TYPE);
        $payment['restepaye'] = round((float) $this->projectRepo->getProjectById($idproj)['couttot'] - $this->paymentRepo->sumParentPaidInstallments($idproj, self::TYPE), 3);
        return $payment;
    }

    public function updatePayment(int $idpay, float $montant, string $comment, string $dateech, string $status, int $idproj, ?string $datepay): void
    {
        $project = $this->getEditableProject($idproj);
        $payment = $this->getProjectInstallment($idpay);
        if ((int) $payment['parent_id'] !== $idproj) {
            throw ValidationException::NotFound("Échéance introuvable.");
        }
        $datepay = $this->validatePayment($montant, $dateech, $status, $datepay);
        if (($this->paymentRepo->sumParentInstallmentsExceptUpdated($idproj, $idpay, self::TYPE) + $montant) > (float) $project['couttot']) {
            throw ValidationException::amountExceeded();
        }
        $this->paymentRepo->updateInstallment($idpay, $montant, $comment, $dateech, $status, $datepay);
        $this->syncProjectStatus($project);
    }

    public function deletePayment(int $idpay, int $idproj): void
    {
        $project = $this->getEditableProject($idproj);
        $payment = $this->getProjectInstallment($idpay);
        if ((int) $payment['parent_id'] !== $idproj) {
            throw ValidationException::NotFound("Échéance introuvable.");
        }
        $this->paymentRepo->deleteInstallment($idpay);
        $this->syncProjectStatus($project);
    }
}
