<?php

namespace App\Repositories;

use App\Enums\SubscriptionType;
use App\Exceptions\DatabaseException;
use PDO;

class StockiRepository
{
    private PDO $connection;
    private const TABLE = "stocki";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countStockis(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function isStockiExists(int $clientId): bool
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

    public function getStockiById(int $id): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                s.id,
                s.id_client,
                s.payment_type,
                s.startDate,
                p.idpay,
                p.echeance,
                p.commentaire,
                p.dateech,
                p.status,
                c.nom AS client_name,
                c.tel AS client_tel
            FROM  " . self::TABLE . " AS s
            INNER JOIN payments AS p
                ON s.id=p.parent_id
                AND p.parent_type=:parent_type
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient
            WHERE s.id=:id LIMIT 1"
        );
        $stmt->execute([
            ':id' => $id,
            ':parent_type' => SubscriptionType::STOCKI
        ]);
        $stocki = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stocki === false) {
            throw DatabaseException::operationFailed("L'abonnement est introuvable.");
        }
        return $stocki;
    }

    public function getStockis(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                s.id,
                s.id_client,
                s.payment_type,
                s.startDate,
                p.idpay,
                p.echeance,
                p.commentaire,
                p.dateech,
                p.datepay,
                p.status,
                c.nom AS client_name,
                c.tel AS client_tel
            FROM  " . self::TABLE . " AS s
            INNER JOIN payments AS p
                ON s.id=p.parent_id
                AND p.parent_type=:parent_type
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient"
        );
        $stmt->execute([':parent_type' => SubscriptionType::STOCKI]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteStocki(int $id): void
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

    public function updateStocki(int $id, string $payment_type, string $startDate): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET payment_type=:payment_type,
                startDate=:startDate
            WHERE id=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':payment_type' => $payment_type,
            ':startDate' => $startDate,
        ]);
    }

    public function addStocki(int $id_client, string $payment_type, string $startDate): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (id_client,payment_type,startDate) 
            VALUES (:id_client,:payment_type,:startDate)"
        );

        $stmt->execute([
            ':id_client' => $id_client,
            ':payment_type' => $payment_type,
            ':startDate' => $startDate,
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout de l'abonnement.");
        }
        return $id;
    }
}
