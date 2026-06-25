<?php
class Users
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function count()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table);
        $stmt->execute();
        return (int)$stmt->fetchColumn(); // retourne un nombre
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT iduser,
                        email,
                        nom,
                        prenom,
                        tel,
                        role
                FROM " . $this->table
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE iduser=:id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function update($id, $email, $password, $nom, $prenom, $tel, $role)
    {
        if (!empty($password)) {
            $stmt  = $this->conn->prepare(
                "UPDATE  {$this->table}  
                SET email=:email,
                password=:password,
                nom=:nom,
                prenom=:prenom,
                tel=:tel,
                role=:role
                WHERE iduser=:id"
            );
            return $stmt->execute([
                ':id' => $id,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':tel' => $tel,
                ':role' => $role
            ]);
        } else {
            $stmt  = $this->conn->prepare(
                "UPDATE  {$this->table}  
                SET email=:email,
                nom=:nom,
                prenom=:prenom,
                tel=:tel,
                role=:role
                WHERE iduser=:id"
            );
            return $stmt->execute([
                ':id' => $id,
                ':email' => $email,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':tel' => $tel,
                ':role' => $role
            ]);
        }
    }

    public function add($email, $password, $nom, $prenom, $tel, $role)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (email,password,nom,prenom,tel,role) 
             VALUES (:email,:password,:nom,:prenom,:tel,:role)"
        );

        return $stmt->execute([
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':tel' => $tel,
            ':role' => $role
        ]);
    }
}
