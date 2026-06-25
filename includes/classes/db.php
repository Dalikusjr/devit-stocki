<?php
class Database {
    private $host = "localhost";   // Adresse du serveur
    private $dbName = "db_devitstocki"; // Nom de la base
    private $username = "dali";    // Utilisateur
    private $password = "root";        // Mot de passe
    private $conn;

    // Méthode pour se connecter
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=".$this->host.";dbname=".$this->dbName,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }

        return $this->conn;
    }

    public function deconnect(){
        $this->conn=null;
    }
}
?>
