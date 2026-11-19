<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionType;
use App\Exceptions\ValidationException;
use App\Repositories\ClientHistoryRepository;
use App\Repositories\ClientRepository;

class ClientHistoryService
{
    public function __construct(
        private ClientHistoryRepository $historyRepo,
        private ClientRepository $clientRepo,
        private MaintainService $maintainService,
        private StockiService $stockiService
    ) {}

    public function getHistory(int $idclient): array
    {
        if (!$this->clientRepo->clientExists($idclient)) {
            throw ValidationException::NotFound("Client introuvable.");
        }
        $received = $this->historyRepo->getReceivedPayments($idclient);

        // impayés : tranches de projets / licences, et période en cours des abonnements récurrents
        $unpaid = $this->historyRepo->getUnpaidInstallments($idclient);
        foreach ($this->maintainService->getMaintains() as $m) {
            if ((int) $m['idclient'] === $idclient && $m['status'] === PaymentStatus::UNPAID) {
                $unpaid[] = ['kind' => SubscriptionType::MAINTAIN, 'objet' => 'Maintenance — ' . $m['titre'], 'd' => $m['dateech'], 'montant' => $m['echeance']];
            }
        }
        foreach ($this->stockiService->getStockis() as $s) {
            if ((int) $s['id_client'] === $idclient && $s['status'] === PaymentStatus::UNPAID) {
                $unpaid[] = ['kind' => SubscriptionType::STOCKI, 'objet' => 'Abonnement Stocki ' . $s['payment_type'], 'd' => substr($s['dateech'], 0, 10), 'montant' => $s['echeance']];
            }
        }
        usort($unpaid, fn($a, $b) => strcmp($a['d'], $b['d']));

        $sum = fn(array $rows) => round(array_sum(array_map(fn($r) => (float) $r['montant'], $rows)), 3);
        return [
            'received' => $received,
            'unpaid' => $unpaid,
            'totalReceived' => $sum($received),
            'totalUnpaid' => $sum($unpaid),
        ];
    }
}
