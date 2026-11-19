<?php

namespace App\Services;

use App\Enums\SubscriptionType;
use App\Repositories\StatsRepository;

class StatsService
{
    private const MONTHS = 12;   // mois affichés
    private const QUARTERS = 8;  // trimestres affichés
    private const MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    public function __construct(private StatsRepository $statsRepo) {}

    // Projets et maintenance = Dev ; abonnements et licences = Stocki
    private function family(string $type): string
    {
        return in_array($type, [SubscriptionType::STOCKI, SubscriptionType::STOCKI_PREMIUM], true) ? 'stocki' : 'dev';
    }

    // Périodes vides : [clé => ['label' => ..., 'dev' => 0, 'stocki' => 0]]
    private function emptyPeriods(string $granularity, int $firstYear): array
    {
        $now = new \DateTimeImmutable('first day of this month');
        $periods = [];
        if ($granularity === 'month') {
            for ($i = self::MONTHS - 1; $i >= 0; $i--) {
                $d = $now->modify("-$i months");
                $periods[$d->format('Y-m')] = ['label' => self::MOIS[(int) $d->format('n') - 1] . ' ' . $d->format('Y')];
            }
        } elseif ($granularity === 'quarter') {
            $q = intdiv((int) $now->format('n') - 1, 3);
            $y = (int) $now->format('Y');
            for ($i = 0; $i < self::QUARTERS; $i++) {
                $periods[sprintf('%d-T%d', $y, $q + 1)] = ['label' => 'T' . ($q + 1) . ' ' . $y];
                if (--$q < 0) {
                    $q = 3;
                    $y--;
                }
            }
            $periods = array_reverse($periods, true);
        } else {
            for ($y = $firstYear; $y <= (int) $now->format('Y'); $y++) {
                $periods[(string) $y] = ['label' => (string) $y];
            }
        }
        foreach ($periods as &$p) {
            $p += ['dev' => 0.0, 'stocki' => 0.0];
        }
        return $periods;
    }

    private function periodKey(string $date, string $granularity): string
    {
        $y = substr($date, 0, 4);
        $m = (int) substr($date, 5, 2);
        return match ($granularity) {
            'month' => substr($date, 0, 7),
            'quarter' => $y . '-T' . (intdiv($m - 1, 3) + 1),
            default => $y,
        };
    }

    public function getStats(): array
    {
        $gains = $this->statsRepo->getGains();
        $byType = array_fill_keys([SubscriptionType::PROJECT, SubscriptionType::MAINTAIN, SubscriptionType::STOCKI, SubscriptionType::STOCKI_PREMIUM], 0.0);
        $thisMonth = date('Y-m');
        $monthTotal = 0.0;
        $firstYear = (int) date('Y');
        foreach ($gains as $g) {
            $firstYear = min($firstYear, (int) substr($g['d'], 0, 4));
        }

        $series = [];
        foreach (['month', 'quarter', 'year'] as $granularity) {
            $series[$granularity] = $this->emptyPeriods($granularity, $firstYear);
        }
        foreach ($gains as $g) {
            $montant = (float) $g['montant'];
            $byType[$g['type']] += $montant;
            if (str_starts_with($g['d'], $thisMonth)) {
                $monthTotal += $montant;
            }
            foreach ($series as $granularity => &$periods) {
                $key = $this->periodKey($g['d'], $granularity);
                if (isset($periods[$key])) {
                    $periods[$key][$this->family($g['type'])] += $montant;
                }
            }
            unset($periods);
        }

        $round = fn(float $v) => round($v, 3);
        foreach ($series as &$periods) {
            $periods = array_values(array_map(fn($p) => ['label' => $p['label'], 'dev' => $round($p['dev']), 'stocki' => $round($p['stocki'])], $periods));
        }
        unset($periods);

        $dev = $byType[SubscriptionType::PROJECT] + $byType[SubscriptionType::MAINTAIN];
        $stocki = $byType[SubscriptionType::STOCKI] + $byType[SubscriptionType::STOCKI_PREMIUM];
        return [
            'total' => $round($dev + $stocki),
            'month' => $round($monthTotal),
            'dev' => $round($dev),
            'stocki' => $round($stocki),
            'byType' => array_map($round, $byType),
            'clients' => [
                'dev' => $this->statsRepo->countDevClients(),
                'stocki' => $this->statsRepo->countStockiClients(),
            ],
            'series' => $series,
        ];
    }
}
