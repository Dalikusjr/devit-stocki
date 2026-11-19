<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;
use PDO;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;

class PaymentRepository
{
    private PDO $connection;
    private const TABLE = "payments";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countInstallments(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function hasPaidInstallment(int $parent_id, string $parent_type): bool
    {
        $query =
            "SELECT * 
            FROM " . self::TABLE . "  
            WHERE parent_id=:parent_id 
            AND parent_type=:parent_type 
            AND status=:status LIMIT 1";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type, ':status' => PaymentStatus::PAID]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countParentInstallments(int $parent_id, string $parent_type): int
    {
        $sql =
            "SELECT IFNULL(count(*),0)
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type=:parent_type";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type]);
        return (int)$stmt->fetchColumn();
    }

    public function countParentPaidInstallments(int $parent_id, string $parent_type): int
    {
        $sql =
            "SELECT IFNULL(count(*),0)
            FROM " . self::TABLE . " 
            WHERE parent_id=:parent_id 
            AND parent_type=:parent_type 
            AND status=:status";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type, ':status' => PaymentStatus::PAID]);
        return (int)$stmt->fetchColumn();
    }

    public function countParentUnpaidInstallments(int $parent_id, string $parent_type): int
    {
        $sql =
            "SELECT IFNULL(count(*),0)
            FROM " . self::TABLE . "
            WHERE parent_id=:parent_id 
            AND parent_type=:parent_type 
            AND status=:status";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type, ':status' => PaymentStatus::UNPAID]);
        return (int)$stmt->fetchColumn();
    }

    public function sumParentInstallments(int $parent_id, string $parent_type): float
    {
        $sql =
            "SELECT IFNULL(SUM(echeance),0) 
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type=:parent_type";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type]);
        return (float) $stmt->fetchColumn();
    }

    public function sumParentInstallmentsExceptUpdated(int $parent_id, int $idpay, string $parent_type): float
    {
        $sql =
            "SELECT IFNULL(SUM(echeance),0) 
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type=:parent_type 
            AND idpay!=:idpay";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':parent_id' => $parent_id,
            ':parent_type' => $parent_type,
            ':idpay' => $idpay
        ]);
        return (float) $stmt->fetchColumn();
    }

    public function sumParentPaidInstallments(int $parent_id, string $parent_type): float
    {
        $sql =
            "SELECT IFNULL(SUM(echeance),0) 
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type = :parent_type 
            AND status = :status";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type, ':status' => PaymentStatus::PAID]);
        return (float) $stmt->fetchColumn();
    }

    public function sumParentUnpaidInstallments(int $parent_id, string $parent_type): float
    {
        $sql =
            "SELECT IFNULL(SUM(echeance),0) 
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type = :parent_type 
            AND status = :status";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type, ':status' => PaymentStatus::UNPAID]);
        return (float) $stmt->fetchColumn();
    }

    public function getInstallmentById(int $idpay): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE idpay=:idpay LIMIT 1"
        );
        $stmt->execute([':idpay' => $idpay]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getParentInstallments(int $parent_id, string $parent_type): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE parent_id = :parent_id 
            AND parent_type = :parent_type
            ORDER BY dateech, idpay"
        );
        $stmt->execute([':parent_id' => $parent_id, ':parent_type' => $parent_type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInstallments(): array
    {
        $stmt = $this->connection->query(
            "SELECT * 
            FROM " . self::TABLE
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteUnpaidInstallments(int $parent_id, string $parent_type): int
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . "
            WHERE parent_id=:parent_id 
            AND parent_type=:parent_type 
            AND status=:status"
        );
        $stmt->execute([
            ':parent_id' => $parent_id,
            ':parent_type' => $parent_type,
            ':status' => PaymentStatus::UNPAID
        ]);
        return (int)$stmt->rowCount();
    }
    public function deleteInstallment(int $idpay): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . "
            WHERE idpay=:idpay"
        );
        $stmt->execute([':idpay' => $idpay]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'échéance n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function updateInstallment(int $idpay, float $montant, string $comment, string $dateech, string $status, ?string $datepay): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET echeance=:montant,
                commentaire=:comment,
                dateech=:dateech,
                status=:status,
                datepay=:datepay
                WHERE idpay=:idpay"
        );
        $stmt->execute([
            ':montant' => $montant,
            ':comment' => $comment,
            ':dateech' => $dateech,
            ':status' => $status,
            ':datepay' => $datepay,
            ':idpay' => $idpay
        ]);
    }

    public function updateInstallmentStatus(int $idpay, string $status, ?string $datepay): bool
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
            SET status=:status,datepay=:datepay
            WHERE idpay=:idpay"
        );
        $stmt->execute([
            ':status' => $status,
            ':idpay' => $idpay,
            ':datepay' => $datepay
        ]);
        return $stmt->rowCount() > 0;
    }

    public function addInstallment(int $idproj, string $parent_type, float $montant, string $comment, string $dateech, string $status, ?string $datepay): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (parent_id,parent_type,echeance,commentaire,dateech,status,datepay) 
            VALUES (:parent_id,:parent_type,:montant,:comment,:dateech,:status,:datepay)"
        );

        $stmt->execute([
            ':montant' => $montant,
            ':comment' => $comment,
            ':dateech' => $dateech,
            ':status' => $status,
            ':datepay' => $datepay,
            ':parent_id' => $idproj,
            ':parent_type' => $parent_type
        ]);
        $id = (int)$this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout d'échéance.");
        }
        return $id;
    }
}
