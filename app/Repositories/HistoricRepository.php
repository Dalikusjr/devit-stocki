<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;
use PDO;

class HistoricRepository
{
    private PDO $connection;
    private const TABLE = "historic";

    public function __construct(PDO $db)
    {
        $this->connection = $db;
    }

    public function countHistorics(): int
    {
        $stmt = $this->connection->query(
            "SELECT COUNT(*) 
            FROM  " . self::TABLE
        );
        return (int)$stmt->fetchColumn();
    }

    public function getHistoricsByParentIdAndType(int $parent_id, string $parent_type): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                h.id,
                h.montant,
                h.payed_at,
                h.id_client,
                c.nom AS client_name 
            FROM  " . self::TABLE . " AS h
            LEFT JOIN clients AS c
                ON h.id_client=c.idclient
            WHERE parent_id=:parent_id 
            AND parent_type=:parent_type"
        );
        $stmt->execute([
            ':parent_id' => $parent_id,
            ':parent_type' => $parent_type
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistorics(): array
    {
        $stmt = $this->connection->query(
            "SELECT 
                h.id,
                h.montant,
                h.payed_at,
                h.id_client,
                c.nom AS client_name
            FROM  " . self::TABLE . " AS h
            LEFT JOIN clients AS c
                ON h.id_client=c.idclient"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteHistoric(int $id): void
    {
        $stmt  = $this->connection->prepare(
            "DELETE 
            FROM " . self::TABLE . " 
            WHERE id=:id"
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw DatabaseException::operationFailed("L'historique n'existe pas ou n'a pas pu être supprimé.");
        }
    }

    public function addHistoric(int $parent_id, string $parent_type, float $montant, int $id_client): int
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO " . self::TABLE . " 
            (parent_id,parent_type,montant,id_client) 
            VALUES (:parent_id,:parent_type,:montant,:idclient)"
        );

        $stmt->execute([
            ':parent_id' => $parent_id,
            ':parent_type' => $parent_type,
            ':montant' => $montant,
            ':idclient' => $id_client
        ]);
        $id = (int) $this->connection->lastInsertId();
        if ($id <= 0) {
            throw DatabaseException::operationFailed("Erreur lors de l'ajout de l'historique.");
        }
        return $id;
    }
}
