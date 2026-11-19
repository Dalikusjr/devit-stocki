<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\HistoricRepository;
use App\Repositories\MaintainRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\StatsRepository;
use App\Repositories\StockiRepository;
use App\Services\DashboardService;
use App\Services\MaintainService;
use App\Services\StatsService;
use App\Services\StockiService;
use App\Validators\Validator;

class DashboardController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->dashboardService = new DashboardService(
            new DashboardRepository($db),
            new MaintainService($db, new MaintainRepository($db), new PaymentRepository($db), new HistoricRepository($db), new ProjectRepository($db)),
            new StockiService($db, new StockiRepository($db), new HistoricRepository($db), new PaymentRepository($db), new ClientRepository($db)),
            new StatsService(new StatsRepository($db))
        );
    }

    public function getDashboard()
    {
        try {
            Validator::validateMethod('GET');
            Response::success($this->dashboardService->getDashboard(), 'Tableau de bord récupéré avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de charger le tableau de bord');
        }
    }
}
