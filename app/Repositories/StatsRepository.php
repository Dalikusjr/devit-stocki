<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use PDO;

class StatsRepository
{
    public function __construct(private PDO $connection) {}

    /**
     * Tous les encaissements : [type, date (Y-m-d), montant].
     * - Projets : tranches payées (la table payments les conserve) ;
     * - Maintenance, Stocki, Licences : historique (leur ligne de paiement est
     *   réutilisée à chaque période, seul l'historique garde le passé).
     * L'avance d'un projet est aussi dans l'historique : on ne la compte qu'une fois.
     */
    public function getGains(): array
    {
        $stmt = $this->connection->prepare(
            "SELECT :project AS type, COALESCE(datepay, dateech) AS d, echeance AS montant
            FROM payments
            WHERE parent_type=:project2 AND status=:paid
            UNION ALL
            SELECT parent_type AS type, DATE(payed_at) AS d, montant
            FROM historic
            WHERE parent_type IN (:maintain, :stocki, :premium)"
        );
        $stmt->execute([
            ':project' => SubscriptionType::PROJECT,
            ':project2' => SubscriptionType::PROJECT,
            ':paid' => PaymentStatus::PAID,
            ':maintain' => SubscriptionType::MAINTAIN,
            ':stocki' => SubscriptionType::STOCKI,
            ':premium' => SubscriptionType::STOCKI_PREMIUM,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Clients ayant au moins un projet de développement
    public function countDevClients(): int
    {
        return (int) $this->connection->query("SELECT COUNT(DISTINCT idclient) FROM projects")->fetchColumn();
    }

    // Clients ayant un abonnement Stocki ou une licence à vie
    public function countStockiClients(): int
    {
        return (int) $this->connection->query(
            "SELECT COUNT(*) FROM (
                SELECT id_client FROM stocki
                UNION
                SELECT id_client FROM stocki_premium
            ) AS c"
        )->fetchColumn();
    }
}
