<?php

namespace App\Controllers;

use App\Config\Database;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Logging\Logger;
use App\Repositories\StatsRepository;
use App\Services\StatsService;
use App\Validators\Validator;

class StatsController
{
    private StatsService $statsService;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->statsService = new StatsService(new StatsRepository($db));
    }

    public function getStats()
    {
        try {
            Validator::validateMethod('GET');
            Response::success($this->statsService->getStats(), 'Statistiques récupérées avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de calculer les statistiques');
        }
    }
}
