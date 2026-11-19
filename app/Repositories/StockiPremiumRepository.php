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

    // Montant payé, reste à payer et nombre d'échéances, calculés depuis la table payments
    private const TOTALS = "
                (SELECT IFNULL(SUM(p.echeance),0)
                    FROM payments AS p
                    WHERE p.parent_id=s.id
                    AND p.parent_type=:parent_type
                    AND p.status=:paid
                ) AS paye,
                s.montant - (SELECT IFNULL(SUM(p.echeance),0)
                    FROM payments AS p
                    WHERE p.parent_id=s.id
                    AND p.parent_type=:parent_type2
                    AND p.status=:paid2
                ) AS reste,
                (SELECT COUNT(*)
                    FROM payments AS p
                    WHERE p.parent_id=s.id
                    AND p.parent_type=:parent_type3
                    AND p.status=:unpaid
                ) AS nbimpayees";

    private const TOTALS_PARAMS = [
        ':parent_type' => SubscriptionType::STOCKI_PREMIUM,
        ':parent_type2' => SubscriptionType::STOCKI_PREMIUM,
        ':parent_type3' => SubscriptionType::STOCKI_PREMIUM,
        ':paid' => PaymentStatus::PAID,
        ':paid2' => PaymentStatus::PAID,
        ':unpaid' => PaymentStatus::UNPAID,
    ];

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
            "SELECT 1 
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
                c.tel AS client_tel," . self::TOTALS . "
            FROM  " . self::TABLE . " AS s
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient
            WHERE s.id=:id LIMIT 1"
        );
        $stmt->execute([':id' => $id] + self::TOTALS_PARAMS);
        $stocki = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stocki === false) {
            throw DatabaseException::operationFailed("La licence est introuvable.");
        }
        return $stocki;
    }

    public function getStockiPremiums(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                s.id,
                s.id_client,
                s.montant,
                s.datedebut,
                c.nom AS client_name,
                c.tel AS client_tel," . self::TOTALS . "
            FROM  " . self::TABLE . " AS s
            LEFT JOIN clients AS c
                ON s.id_client=c.idclient
            ORDER BY s.id"
        );
        $stmt->execute(self::TOTALS_PARAMS);
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
            throw DatabaseException::operationFailed("La licence n'existe pas ou n'a pas pu être supprimée.");
        }
    }

    public function updateStockiPremium(int $id, float $montant, string $datedebut): void
    {
        $stmt  = $this->connection->prepare(
            "UPDATE  " . self::TABLE . "  
                SET montant=:montant,
                datedebut=:datedebut
            WHERE id=:id"
        );
        $stmt->execute([
            ':id' => $id,
            ':montant' => $montant,
            ':datedebut' => $datedebut,
        ]);
    }

    public function addStockiPremium(int $id_client, float $montant, string $datedebut): int
    {

        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (id_client,montant,datedebut) 
            VALUES (:id_client,:montant,:datedebut)"
        );

        $stmt->execute([
            ':id_client' => $id_client,
            ':montant' => $montant,
            ':datedebut' => $datedebut
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout de la licence.");
        }
        return $id;
    }
}
