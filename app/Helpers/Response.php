<?php

namespace App\Helpers;

class Response
{
    public static function success($data = [], $message = '')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }

    public static function failed($data = [], $message = '')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}
