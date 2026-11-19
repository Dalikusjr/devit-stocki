<?php

namespace App\Controllers;

use App\Config\Database;
use App\Enums\PaymentStatus;
use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Response;
use App\Validators\Validator;
use App\Logging\Logger;
use App\Repositories\ClientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProjectRepository;
use App\Services\PaymentService;

class PaymentController
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->paymentService = new PaymentService(new PaymentRepository($db), new ProjectRepository($db), new ClientRepository($db));
    }
    // Corps JSON (envoyé par la page) ou, à défaut, champs de formulaire
    private function input(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : $_POST;
    }

    public function createPayment()
    {
        try {
            Validator::validateMethod('POST');
            $data = $this->input();
            $idproj  = trim($data['idproject'] ?? '');
            $comment = trim($data['comment'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $status  = trim($data['status'] ?? PaymentStatus::UNPAID);
            $dateech = trim($data['dateech'] ?? '');
            $datepay = !empty($data['datepay']) ? trim($data['datepay']) : null;

            Validator::validateRequiredFields([
                'ID de projet' => $idproj,
                'Commentaire' => $comment,
                'Montant' => $montant,
                'Status' => $status,
                'Date d\'échéance' => $dateech
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateMaxLength($comment, 100, 'Commentaire');
            Validator::validateDate($dateech, "Date d'échéance");
            if ($datepay !== null) Validator::validateDate($datepay, 'Date de paiement');
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            Validator::validateValidId($idproj);
            $montant = (float) $montant;
            $idproj = (int) $idproj;
            $idpay = $this->paymentService->createPayment($montant, $comment, $dateech, $status, $idproj, $datepay);
            Response::success($this->paymentService->getPaymentDataForDisplay($idproj, $idpay), 'Échéance ajoutée avec succès');
        } catch (DatabaseException $e) {
            Response::failed(null, $e->getMessage());
        } catch (ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible d'ajouter l'échéance.");
        }
    }
    public function getPayment()
    {
        try {
            Validator::validateMethod('GET');

            $idpay = trim($_GET['id'] ?? '');
            Validator::validateRequiredFields(['ID payment' => $idpay]);
            Validator::validateValidId($idpay);
            $idpay = (int) $idpay;
            Response::success($this->paymentService->getPayment($idpay), 'Échéance récupérée avec succès');
        } catch (ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de récupérer l'échéance");
        }
    }

    public function getProjectsByClient()
    {
        try {
            Validator::validateMethod('GET');

            $idclient = trim($_GET['idclient'] ?? '');
            Validator::validateRequiredFields(['ID client' => $idclient]);
            Validator::validateValidId($idclient);
            $idclient = (int) $idclient;
            Response::success($this->paymentService->getProjectsByClient($idclient), 'Données récupérées avec succès');
        } catch (DatabaseException | ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les projets');
        }
    }

    public function getClientsForPaymentDashboard()
    {
        try {
            Validator::validateMethod('GET');
            Response::success($this->paymentService->getClientsForPaymentDashboard(), 'Données récupérés avec succès');
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, 'Impossible de récupérer les projets');
        }
    }

    public function deletePayment()
    {
        try {
            Validator::validateMethod('DELETE');
            $data = $this->input();
            $idpay = trim($data['dueToDelete'] ?? '');
            $idproj = trim($data['idproject'] ?? '');
            Validator::validateRequiredFields([
                'id de paiement' => $idpay,
                'id de projet' => $idproj
            ]);
            Validator::validateValidId($idpay);
            Validator::validateValidId($idproj);
            $idproj = (int) $idproj;
            $idpay = (int) $idpay;
            $this->paymentService->deletePayment($idpay, $idproj);
            Response::success($this->paymentService->getPaymentDataForDisplay($idproj), 'Échéance supprimée avec succès');
        } catch (DatabaseException $e) {
            Response::failed(null, $e->getMessage());
        } catch (ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de supprimer l'échéance.");
        }
    }

    public function updatePayment()
    {
        try {
            Validator::validateMethod('PATCH');
            $data = $this->input();
            $idproj  = trim($data['idproject'] ?? '');
            $idpay   = trim($data['editingTranche'] ?? '');
            $comment = trim($data['comment'] ?? '');
            $montant = trim($data['montant'] ?? '');
            $status  = trim($data['status'] ?? PaymentStatus::UNPAID);
            $dateech = trim($data['dateech'] ?? '');
            $datepay = !empty($data['datepay']) ? trim($data['datepay']) : null;
            Validator::validateRequiredFields([
                'ID de projet' => $idproj,
                'ID de paiement' => $idpay,
                'Commentaire' => $comment,
                'Montant' => $montant,
                'Status' => $status,
                'Date d\'échéance' => $dateech
            ]);
            Validator::validateValueInEnum($status, PaymentStatus::getValues());
            Validator::validateMaxLength($comment, 100, 'Commentaire');
            Validator::validateDate($dateech, "Date d'échéance");
            if ($datepay !== null) Validator::validateDate($datepay, 'Date de paiement');
            Validator::validatePositiveNumbers(['Montant' => $montant]);
            Validator::validateValidId($idpay);
            Validator::validateValidId($idproj);
            $montant = (float) $montant;
            $idpay = (int) $idpay;
            $idproj = (int) $idproj;
            $this->paymentService->updatePayment($idpay, $montant, $comment, $dateech, $status, $idproj, $datepay);

            Response::success($this->paymentService->getPaymentDataForDisplay($idproj, $idpay), 'Échéance modifiée avec succès');
        } catch (DatabaseException $e) {
            Response::failed(null, $e->getMessage());
        } catch (ValidationException $e) {
            Response::failed(null, $e->getMessage());
        } catch (\Throwable $e) {
            Logger::logError($e);
            Response::failed(null, "Impossible de modifier l'échéance.");
        }
    }
}
