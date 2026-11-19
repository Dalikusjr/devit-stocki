<?php

namespace App\Services;

use PDO;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Enums\ProjectStatus;
use App\Exceptions\ValidationException;
use App\Repositories\HistoricRepository;

class ProjectService
{
    private const MIN_INSTALLMENTS = 1;
    private const MAX_INSTALLMENTS = 3;
    private const PAYMENT_INTERVAL_MONTHS = 1;
    private function determineProjectStatus(float $avancepaye, float $couttot): string
    {
        return $avancepaye === $couttot ? ProjectStatus::COMPLETED : ProjectStatus::ACTIVE;
    }

    private function createClientIfNecessary(string $email, string $nom, string $tel, string $adresse): int
    {
        if ($client = $this->clientRepo->getClientByEmail($email)) {
            $idclient = $client['idclient'];
        } else {
            $idclient = $this->clientRepo->addClient($email, $nom, $tel, $adresse);
        }
        return $idclient;
    }

    private function createAdvancePayment(int $idproj, int $idclient, string $comment, float $avancepaye): void
    {
        $dateCourante = new \DateTimeImmutable('first day of this month')->format('Y-m-d');
        $this->paymentRepo->addInstallment((int) $idproj, SubscriptionType::PROJECT, $avancepaye, $comment !== '' ? $comment : 'Avance', $dateCourante, PaymentStatus::PAID,  $dateCourante);
        $this->historicRepo->addHistoric((int) $idproj, SubscriptionType::PROJECT, $avancepaye, $idclient);
    }

    private function createUnpaidPayments(int $idproj, float $avancepaye, float $couttot, int $nbrEcheance, int $nbrMonths): void
    {
        $remaining = $couttot - $avancepaye;
        $base = floor($remaining / $nbrEcheance);
        $dateCourante = new \DateTimeImmutable('first day of this month');
        for ($i = 1; $i <= $nbrEcheance; $i++) {
            if ($i === $nbrEcheance) {
                $amount = $remaining - ($base * ($nbrEcheance - 1));
            } else {
                $amount = $base;
            }
            $dateCourante = $dateCourante->modify('+' . $nbrMonths . ' month');
            $this->paymentRepo->addInstallment((int) $idproj, SubscriptionType::PROJECT, $amount, "Échéance #{$i} - Projet #{$idproj}", $dateCourante->format('Y-m-d'), PaymentStatus::UNPAID, null);
        }
    }
    private function valideNumberOfInstallment(int $nbrEcheance): void
    {
        if ($nbrEcheance > self::MAX_INSTALLMENTS || $nbrEcheance < self::MIN_INSTALLMENTS) {
            throw ValidationException::invalidValue("Le nombre d'échéances doit être compris entre " . self::MIN_INSTALLMENTS . " et " . self::MAX_INSTALLMENTS . ".");
        }
    }

    private function validateAmountsAndDates(float $couttot, float $avancepaye, string $datedebut, string $daterenouv): void
    {
        if ($couttot <= 0) {
            throw ValidationException::invalidValue("Le coût total doit être supérieur à 0.");
        }
        if ($avancepaye > $couttot) {
            throw ValidationException::invalidValue("L'avance ne peut pas dépasser le coût total.");
        }
        if ($daterenouv < $datedebut) {
            throw ValidationException::invalidValue("La date de renouvellement doit être postérieure à la date de début.");
        }
    }

    public function __construct(
        private PDO $connection,
        private ProjectRepository $projectRepo,
        private ClientRepository $clientRepo,
        private PaymentRepository $paymentRepo,
        private HistoricRepository $historicRepo
    ) {}

    public function getProjects(): array
    {
        return $this->projectRepo->getProjects();
    }

    public function createProject(
        string $email,
        string $nom,
        string $tel,
        string $adresse,
        string $titre,
        string $datedebut,
        string $daterenouv,
        float $couttot,
        float $avancepaye,
        string $acces,
        string $comment,
        int $nbrEcheance
    ): void {
        $this->valideNumberOfInstallment($nbrEcheance);
        $this->validateAmountsAndDates($couttot, $avancepaye, $datedebut, $daterenouv);
        $this->connection->beginTransaction();
        try {
            $idclient = $this->createClientIfNecessary($email, $nom,  $tel,  $adresse);
            $projectStatus = $this->determineProjectStatus($avancepaye, $couttot);
            $idproj = $this->projectRepo->addProject($titre,  $datedebut,  $daterenouv,  $couttot,  $acces,  $idclient, $projectStatus);
            if ($projectStatus === ProjectStatus::COMPLETED) {
                $this->createAdvancePayment($idproj, $idclient, $comment,  $avancepaye);
            } else {
                if ($avancepaye > 0) {
                    $this->createAdvancePayment($idproj, $idclient, $comment,  $avancepaye);
                }
                $this->createUnpaidPayments($idproj,  $avancepaye, $couttot,  $nbrEcheance, self::PAYMENT_INTERVAL_MONTHS);
            }
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    // $acces à null : accès inchangés (utilisateur non administrateur)
    public function updateProject(int $idproj, string $titre, string $datedebut, string $daterenouv, float $couttot, ?string $acces, int $nbrEcheance): void
    {
        $oldProject = $this->projectRepo->getProjectById($idproj);
        $oldInstallments = $this->paymentRepo->countParentInstallments($idproj, SubscriptionType::PROJECT);
        if ($oldProject['status'] != ProjectStatus::ACTIVE) {
            throw ValidationException::NotAllowed("Vous ne pouvez pas modifier un projet qui n'est plus actif.");
        }
        // Les échéances ne sont régénérées que si le coût ou leur nombre change
        $regenerate = $couttot !== (float) $oldProject['couttot'] || $nbrEcheance !== $oldInstallments;
        if ($regenerate && $this->paymentRepo->hasPaidInstallment($idproj, SubscriptionType::PROJECT)) {
            throw ValidationException::NotAllowed("Vous ne pouvez pas modifier le coût ou le nombre d'échéances d'un projet ayant déjà une échéance payée.");
        }
        if ($regenerate) {
            $this->valideNumberOfInstallment($nbrEcheance);
        }
        $this->validateAmountsAndDates($couttot, 0, $datedebut, $daterenouv);
        $this->connection->beginTransaction();
        try {
            if ($regenerate) {
                $this->paymentRepo->deleteUnpaidInstallments($idproj, SubscriptionType::PROJECT);
                $this->createUnpaidPayments($idproj,  0, $couttot,  $nbrEcheance, self::PAYMENT_INTERVAL_MONTHS);
            }
            // valeur déjà chiffrée : réécrite telle quelle
            $this->projectRepo->updateProject($idproj,  $titre,  $datedebut,  $daterenouv,  $couttot,  $acces ?? (string) $oldProject['acces']);
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function deleteProject(int $idproj): void
    {
        $project = $this->projectRepo->getProjectById($idproj);

        if ($project['status'] !== ProjectStatus::ACTIVE) {
            throw ValidationException::NotAllowed(
                "Vous ne pouvez pas supprimer un projet qui n'est plus actif."
            );
        }

        if ($this->paymentRepo->hasPaidInstallment($idproj, SubscriptionType::PROJECT)) {
            throw ValidationException::NotAllowed(
                "Vous ne pouvez pas supprimer un projet ayant déjà une échéance payée."
            );
        }

        $this->connection->beginTransaction();

        try {
            $this->paymentRepo->deleteUnpaidInstallments(
                $idproj,
                SubscriptionType::PROJECT
            );

            $this->projectRepo->deleteProject($idproj);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    public function archiveProject(int $idproj): void
    {
        $project = $this->projectRepo->getProjectById($idproj);

        if ($project['status'] !== ProjectStatus::COMPLETED) {
            throw ValidationException::NotAllowed(
                "Seuls les projets terminés peuvent être archivés."
            );
        }

        $this->projectRepo->archiveProject($idproj);
    }

    public function getInstallments(int $idproj): array
    {
        return $this->paymentRepo->getParentInstallments($idproj, SubscriptionType::PROJECT);
    }

    public function getClientForAutoComplete(string $email): ?array
    {
        return $this->clientRepo->getClientByEmail($email);
    }
}
