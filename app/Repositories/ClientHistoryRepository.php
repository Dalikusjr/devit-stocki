<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Enums\ProjectStatus;
use App\Enums\SubscriptionType;
use PDO;

/**
 * Historique d'un client, tous modules confondus (cahier des charges §5).
 * Mêmes sources que les statistiques : tranches payées pour les projets,
 * table historic pour la maintenance, les abonnements Stocki et les licences.
 */
class ClientHistoryRepository
{
    public function __construct(private PDO $connection) {}

    public function getReceivedPayments(int $idclient): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 'project' AS kind, CONCAT(pr.titre, ' — ', p.commentaire) AS objet,
                COALESCE(p.datepay, p.dateech) AS d, p.echeance AS montant
            FROM payments AS p
            INNER JOIN projects AS pr ON pr.idproj=p.parent_id
            WHERE p.parent_type=:project AND p.status=:paid AND pr.idclient=:c1
            UNION ALL
            SELECT h.parent_type AS kind,
                CASE h.parent_type
                    WHEN :maintain THEN CONCAT('Maintenance — ', IFNULL(pr.titre, CONCAT('abonnement #', h.parent_id)))
                    WHEN :stocki THEN 'Abonnement Stocki'
                    ELSE 'Licence à vie'
                END AS objet,
                DATE(h.payed_at) AS d, h.montant
            FROM historic AS h
            LEFT JOIN maintains AS m ON h.parent_type=:maintain2 AND m.id=h.parent_id
            LEFT JOIN projects AS pr ON pr.idproj=m.id_proj
            WHERE h.parent_type IN (:maintain3, :stocki2, :premium) AND h.id_client=:c2
            ORDER BY d DESC"
        );
        $stmt->execute([
            ':project' => SubscriptionType::PROJECT, ':paid' => PaymentStatus::PAID, ':c1' => $idclient,
            ':maintain' => SubscriptionType::MAINTAIN, ':maintain2' => SubscriptionType::MAINTAIN, ':maintain3' => SubscriptionType::MAINTAIN,
            ':stocki' => SubscriptionType::STOCKI, ':stocki2' => SubscriptionType::STOCKI,
            ':premium' => SubscriptionType::STOCKI_PREMIUM, ':c2' => $idclient,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tranches de projets (non archivés) et de licences encore dues, toutes dates
    public function getUnpaidInstallments(int $idclient): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 'project' AS kind, CONCAT(pr.titre, ' — ', p.commentaire) AS objet, p.dateech AS d, p.echeance AS montant
            FROM payments AS p
            INNER JOIN projects AS pr ON pr.idproj=p.parent_id
            WHERE p.parent_type=:project AND p.status=:unpaid AND pr.status!=:archived AND pr.idclient=:c1
            UNION ALL
            SELECT 'licence' AS kind, CONCAT('Licence à vie — ', p.commentaire) AS objet, p.dateech AS d, p.echeance AS montant
            FROM payments AS p
            INNER JOIN stocki_premium AS s ON s.id=p.parent_id
            WHERE p.parent_type=:premium AND p.status=:unpaid2 AND s.id_client=:c2"
        );
        $stmt->execute([
            ':project' => SubscriptionType::PROJECT, ':unpaid' => PaymentStatus::UNPAID, ':archived' => ProjectStatus::ARCHIVED, ':c1' => $idclient,
            ':premium' => SubscriptionType::STOCKI_PREMIUM, ':unpaid2' => PaymentStatus::UNPAID, ':c2' => $idclient,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
