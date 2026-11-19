<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Repositories\DashboardRepository;

/**
 * Tableau de bord : gain du mois, alertes de paiement, rappels de renouvellement.
 * Rappels de renouvellement (cahier des charges §2.1) : 1 mois, 2 semaines, 1 semaine avant.
 */
class DashboardService
{
    private const DUE_SOON_DAYS = 7;     // échéances signalées une semaine à l'avance
    private const RENEWAL_DAYS = 30;     // premier rappel : 1 mois avant

    public function __construct(
        private DashboardRepository $dashboardRepo,
        private MaintainService $maintainService,
        private StockiService $stockiService,
        private StatsService $statsService
    ) {}

    // Nombre de jours entre aujourd'hui et $date (négatif si dépassée)
    private function daysFromToday(string $date): int
    {
        $today = new \DateTimeImmutable('today');
        $d = new \DateTimeImmutable(substr($date, 0, 10));
        return (int) $today->diff($d)->format('%r%a');
    }

    private function alert(string $kind, string $label, string $client, float $montant, string $date, string $link, bool $recurring): array
    {
        $days = $this->daysFromToday($date);
        return [
            'kind' => $kind,
            'label' => $label,
            'client' => $client,
            'montant' => round($montant, 3),
            'date' => substr($date, 0, 10),
            'days' => $days,
            // un abonnement non payé pour la période en cours est "à payer" ; une tranche dépassée est "en retard"
            'level' => $days < 0 && !$recurring ? 'overdue' : ($days <= 0 ? 'due' : 'soon'),
            'link' => $link,
        ];
    }

    private function renewalLevel(int $days): string
    {
        return match (true) {
            $days < 0 => 'overdue',
            $days <= 7 => 'week',
            $days <= 14 => 'twoweeks',
            default => 'month',
        };
    }

    public function getDashboard(): array
    {
        $soon = (new \DateTimeImmutable('today'))->modify('+' . self::DUE_SOON_DAYS . ' days')->format('Y-m-d');
        $renewalLimit = (new \DateTimeImmutable('today'))->modify('+' . self::RENEWAL_DAYS . ' days')->format('Y-m-d');

        $payments = [];
        foreach ($this->dashboardRepo->getUnpaidProjectInstallments($soon) as $p) {
            $payments[] = $this->alert('project', $p['titre'] . ' — ' . $p['commentaire'], $p['client'], (float) $p['echeance'], $p['dateech'], 'p-listeprojects?id=' . $p['idclient'], false);
        }
        foreach ($this->dashboardRepo->getUnpaidLicenceInstallments($soon) as $p) {
            $payments[] = $this->alert('licence', 'Licence à vie — ' . $p['commentaire'], $p['client'], (float) $p['echeance'], $p['dateech'], 'p-abonnvie', false);
        }
        // getMaintains() applique aussi le passage au nouveau mois
        foreach ($this->maintainService->getMaintains() as $m) {
            if ($m['status'] === PaymentStatus::UNPAID) {
                $payments[] = $this->alert('maintain', 'Maintenance — ' . $m['titre'], $m['client_nom'], (float) $m['echeance'], $m['dateech'], 'p-maintains', true);
            }
        }
        // getStockis() applique aussi le renouvellement des périodes échues
        foreach ($this->stockiService->getStockis() as $s) {
            if ($s['status'] === PaymentStatus::UNPAID) {
                $payments[] = $this->alert('stocki', 'Abonnement Stocki ' . $s['payment_type'], $s['client_name'], (float) $s['echeance'], $s['dateech'], 'p-abonn', true);
            }
        }
        usort($payments, fn($a, $b) => [$a['date'], $a['client']] <=> [$b['date'], $b['client']]);

        $renewals = array_map(function ($r) {
            $days = $this->daysFromToday($r['daterenouv']);
            return [
                'idproj' => (int) $r['idproj'],
                'titre' => $r['titre'],
                'client' => $r['client'],
                'tel' => $r['tel'],
                'date' => $r['daterenouv'],
                'days' => $days,
                'level' => $this->renewalLevel($days),
            ];
        }, $this->dashboardRepo->getRenewals($renewalLimit));

        $stats = $this->statsService->getStats();
        $months = $stats['series']['month'];
        $current = end($months);
        $previous = $months[count($months) - 2] ?? ['dev' => 0, 'stocki' => 0];

        $sum = fn(array $items, array $levels) => array_sum(array_map(fn($a) => in_array($a['level'], $levels, true) ? $a['montant'] : 0, $items));
        $count = fn(array $items, array $levels) => count(array_filter($items, fn($a) => in_array($a['level'], $levels, true)));

        return [
            'kpis' => [
                'month' => $stats['month'],
                'monthDev' => round($current['dev'], 3),
                'monthStocki' => round($current['stocki'], 3),
                'previousMonth' => round($previous['dev'] + $previous['stocki'], 3),
                'toCollect' => round($sum($payments, ['overdue', 'due']), 3),
                'toCollectCount' => $count($payments, ['overdue', 'due']),
                'overdueCount' => $count($payments, ['overdue']),
                'soon' => round($sum($payments, ['soon']), 3),
                'soonCount' => $count($payments, ['soon']),
                'renewalsCount' => count(array_filter($renewals, fn($r) => $r['days'] >= 0)),
                'renewalsOverdue' => count(array_filter($renewals, fn($r) => $r['days'] < 0)),
                'activeProjects' => $this->dashboardRepo->countActiveProjects(),
            ],
            'payments' => $payments,
            'renewals' => $renewals,
        ];
    }
}
