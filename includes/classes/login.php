<?php
// ==========================
// CLASSE LOGIN
// ==========================
class Login {
    private $conn;
    private $table = "users";

    // Constructeur avec connexion à la base de données
    public function __construct($db) {
        $this->conn = $db;
    }

    // Méthode de connexion
    public function login($email, $password) {
        try{
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($user && password_verify($password, $user['password'])) ? $user : false;
        }catch(PDOException $e){
            echo "Erreur de connexion : " . $e->getMessage();
        }
    }
}
