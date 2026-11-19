<?php

namespace App\Repositories;

use App\Enums\PaymentStatus;
use App\Enums\ProjectStatus;
use App\Enums\SubscriptionType;
use PDO;

class DashboardRepository
{
    public function __construct(private PDO $connection) {}

    // Tranches de projets (non archivés) non payées, échéance au plus tard le $until
    public function getUnpaidProjectInstallments(string $until): array
    {
        $stmt = $this->connection->prepare(
            "SELECT p.idpay, p.echeance, p.dateech, p.commentaire,
                pr.idproj, pr.titre, c.idclient, c.nom AS client
            FROM payments AS p
            INNER JOIN projects AS pr ON pr.idproj=p.parent_id
            INNER JOIN clients AS c ON c.idclient=pr.idclient
            WHERE p.parent_type=:type AND p.status=:unpaid
            AND pr.status!=:archived AND p.dateech<=:until"
        );
        $stmt->execute([
            ':type' => SubscriptionType::PROJECT,
            ':unpaid' => PaymentStatus::UNPAID,
            ':archived' => ProjectStatus::ARCHIVED,
            ':until' => $until,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Échéances de licences à vie non payées, échéance au plus tard le $until
    public function getUnpaidLicenceInstallments(string $until): array
    {
        $stmt = $this->connection->prepare(
            "SELECT p.idpay, p.echeance, p.dateech, p.commentaire,
                s.id, c.idclient, c.nom AS client
            FROM payments AS p
            INNER JOIN stocki_premium AS s ON s.id=p.parent_id
            INNER JOIN clients AS c ON c.idclient=s.id_client
            WHERE p.parent_type=:type AND p.status=:unpaid AND p.dateech<=:until"
        );
        $stmt->execute([
            ':type' => SubscriptionType::STOCKI_PREMIUM,
            ':unpaid' => PaymentStatus::UNPAID,
            ':until' => $until,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Projets non archivés dont le renouvellement tombe au plus tard le $until
    public function getRenewals(string $until): array
    {
        $stmt = $this->connection->prepare(
            "SELECT pr.idproj, pr.titre, pr.daterenouv, pr.status, c.idclient, c.nom AS client, c.tel
            FROM projects AS pr
            INNER JOIN clients AS c ON c.idclient=pr.idclient
            WHERE pr.status!=:archived AND pr.daterenouv<=:until
            ORDER BY pr.daterenouv"
        );
        $stmt->execute([':archived' => ProjectStatus::ARCHIVED, ':until' => $until]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActiveProjects(): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM projects WHERE status=:active");
        $stmt->execute([':active' => ProjectStatus::ACTIVE]);
        return (int) $stmt->fetchColumn();
    }
}
