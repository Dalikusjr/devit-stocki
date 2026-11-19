<?php

namespace App\Config;

use PDO;

class Auth
{
    private $conn;
    private $table = "users";

    // Constructeur avec connexion à la base de données
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Méthode de connexion
    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = :email");
        $stmt->execute([
            ':email' => $email
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }


    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
