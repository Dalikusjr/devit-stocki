<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;

class PaymentService
{
    public function __construct(
        private PaymentRepository $paymentRepo,
        private ProjectRepository $projectRepo,
        private ClientRepository $clientRepo
    ) {}

    public function getPayment(int $idpay): array
    {
        return $this->paymentRepo->getInstallmentById($idpay);
    }

    public function getProjectsByClient(int $idclient): array
    {
        return $this->paymentRepo->getProjectsByClient($idclient);
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

    public function createPayment(float $montant, string $comment, string $dateech, string $status, int $idproj, string $datepay): int
    {
        if (($this->paymentRepo->sumParentInstallments($idproj) + $montant) > $this->projectRepo->getTotalCost($idproj)) {
            throw ValidationException::amountExceeded();
        }
        return $this->paymentRepo->addPayment($montant, $comment, $dateech, $status, $idproj, $datepay);
    }

    public function getPaymentDataForDisplay(int $idproj, int $idpay = 0): array
    {
        $payment = [];
        if ($idpay != 0) $payment = $this->paymentRepo->getPayment($idpay);
        $payment['tranches_paye'] = $this->paymentRepo->countProjectPaidInstallments($idproj);
        $payment['tranches_total'] = $this->paymentRepo->countProjectInstallments($idproj);
        $payment['reste'] = $this->projectRepo->getTotalCost($idproj) - $this->paymentRepo->sumProjectPaidInstallments($idproj);
        return $payment;
    }

    public function updatePayment(int $idpay, float $montant, string $comment, string $dateech, string $status, int $idproj, string $datepay): void
    {
        if (($this->paymentRepo->sumProjectInstallmentsExceptUpdated($idproj, $idpay) + $montant) > $this->projectRepo->getTotalCost($idproj)) {
            throw ValidationException::amountExceeded();
        }
        $this->paymentRepo->updatePayment($idpay, $montant, $comment, $dateech, $status, $datepay);
    }

    public function deletePayment(int $idpay): void
    {
        $this->paymentRepo->deletePayment($idpay);
    }
}
