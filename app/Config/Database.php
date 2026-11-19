<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    // Méthode pour se connecter
    private function __construct()
    {
        try {
            $host = $_ENV['DB_HOST'];
            $dbName = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASS'];

            $this->connection = new PDO("mysql:host={$host};dbname={$dbName}", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Méthode pour récupérer la connexion PDO existante
    public function getConnection()
    {
        return $this->connection;
    }

    public function disconnect()
    {
        $this->connection = null;
    }
}
