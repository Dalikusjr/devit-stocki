<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use PDO;
use App\Enums\ProjectStatus;
use App\Enums\SubscriptionType;
use App\Exceptions\DatabaseException;
use App\Security\Crypto;

class ProjectRepository
{
    private PDO $connection;
    private const TABLE = "projects";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countProjects(): int
    {
        $stmt = $this->connection->query(
            "
            SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }


    public function getProjectById(int $idproj): array
    {
        $stmt = $this->connection->prepare(
            "
            SELECT * 
            FROM " . self::TABLE . " 
            WHERE idproj=:idproj 
            LIMIT 1"
        );
        $stmt->execute([':idproj' => $idproj]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project === false) {
            throw DatabaseException::operationFailed(
                "Projet introuvable."
            );
        }

        return $project;
    }

    public function countProjectsPerClients(): array
    {
        $stmt = $this->connection->query(
            "
            SELECT idclient, COUNT(*) as nb_projects 
            FROM " . self::TABLE . " 
            GROUP BY idclient"
        );
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getProjects(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
            pr.idproj,
            pr.titre,
            pr.datedebut,
            pr.daterenouv,
            pr.couttot,
            pr.acces,
            pr.status,
            cl.idclient,
            cl.nom AS clnom,
            cl.tel AS cltel,
            IFNULL(SUM(CASE WHEN p.status = :statusp THEN p.echeance ELSE 0 END), 0) AS payer,
            IFNULL(SUM(CASE WHEN p.status = :statusu THEN p.echeance ELSE 0 END), 0) AS reste,
            COUNT(p.idpay) AS nbech
        FROM " . self::TABLE . " AS pr
        INNER JOIN clients AS cl ON pr.idclient = cl.idclient
        LEFT JOIN payments AS p ON p.parent_id = pr.idproj AND p.parent_type = :parent_type
        WHERE pr.status != :status
        GROUP BY pr.idproj"
        );
        $stmt->execute([
            ':parent_type' => SubscriptionType::PROJECT,
            ':statusu' => PaymentStatus::UNPAID,
            ':statusp' => PaymentStatus::PAID,
            ':status' => ProjectStatus::ARCHIVED
        ]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($projects as &$project) {
            $project['acces'] = Crypto::decrypt($project['acces']);
        }
        unset($project);
        return $projects;
    }

    public function deleteProject(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . " 
            WHERE idproj=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("Le projet n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE " . self::TABLE . "
                SET status=:status
                WHERE idproj=:id"
        );
        $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function getProjectsByClient(int $idclient): array
    {
        $stmt = $this->connection->prepare(
            "SELECT idproj, titre, couttot, status
            FROM " . self::TABLE . "
            WHERE idclient=:idclient
            ORDER BY idproj"
        );
        $stmt->execute([':idclient' => $idclient]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function archiveProject(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE " . self::TABLE . "
                SET status=:status
                WHERE idproj=:id"
        );
        $stmt->execute([':id' => $id, ':status' => ProjectStatus::ARCHIVED]);
    }

    public function updateProject(int $idproj, string $titre, string $datedebut, string $daterenouv, float $couttot, string $acces): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET titre=:titre,
                datedebut=:datedebut,
                daterenouv=:daterenouv,
                couttot=:couttot,
                acces=:acces
                WHERE idproj=:idproj"
        );
        $stmt->execute([
            ':idproj' => $idproj,
            ':titre' => $titre,
            ':datedebut' => $datedebut,
            ':daterenouv' => $daterenouv,
            ':couttot' => $couttot,
            ':acces' => Crypto::encrypt($acces)
        ]);
    }

    public function addProject(string $titre, string $datedebut, string $daterenouv, float $couttot, string $acces, int $idclient, string $status): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (titre,datedebut,daterenouv,couttot,acces,idclient,status) 
             VALUES (:titre,:datedebut,:daterenouv,:couttot,:acces,:idclient,:status)"
        );

        $stmt->execute([
            ':titre' => $titre,
            ':datedebut' => $datedebut,
            ':daterenouv' => $daterenouv,
            ':couttot' => $couttot,
            ':acces' => Crypto::encrypt($acces),
            ':idclient' => $idclient,
            ':status' => $status
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout du projet.");
        }
        return $id;
    }
}
