<?php

namespace App\Repositories;

use App\Enums\SubscriptionType;
use App\Exceptions\DatabaseException;
use App\Enums\PaymentStatus;
use PDO;

class StockiPremiumRepository
{
    private PDO $connection;
    private const TABLE = "stocki_premium";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countStockiPremiums(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function isStockiPremiumExists(int $clientId): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT * 
            FROM " . self::TABLE . " 
            WHERE id_client=:id_client 
            LIMIT 1"
        );
        $stmt->execute([
            ':id_client' => $clientId
        ]);
        return (bool)$stmt->fetchColumn();
    }

    public function getStockiPremiumById(int $id): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                s.id,
                s.id_client,
                s.montant,
                s.datedebut,
                c.nom AS client_name,
                c.tel AS client_tel
            FROM  " . self::TABLE . " AS s
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient
            WHERE s.id=:id LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id
        ]);
        $stocki = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stocki === false) {
            throw DatabaseException::operationFailed("L'abonnement est introuvable.");
        }
        return $stocki;
    }

    public function getStockiPremiums(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                s.id
                s.id_client,
                s.montant,
                s.datedebut,
                c.nom AS client_name,
                c.tel AS client_tel,
                (SELECT IFNULL(SUM(echeance),0) 
                    FROM payments AS p
                    WHERE p.parent_id=s.id 
                    AND p.parent_type=:parent_type 
                    AND p.status=:status
            ) AS reste
            FROM  " . self::TABLE . " AS s
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient"
        );
        $stmt->execute([':parent_type' => SubscriptionType::STOCKI_PREMIUM, ':status' => PaymentStatus::UNPAID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteStockiPremium(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . "
            WHERE id=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'abonnement n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function updateStockiPremium(int $id, float $montant): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET montant=:montant
            WHERE id=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':montant' => $montant,
        ]);
    }

    public function addStockiPremium(int $id_client, float $montant, string $status): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (id_client,montant,status) 
            VALUES (:id_client,:montant,:status)"
        );

        $stmt->execute([
            ':id_client' => $id_client,
            ':montant' => $montant,
            ':status' => $status
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout de l'abonnement.");
        }
        return $id;
    }
}
