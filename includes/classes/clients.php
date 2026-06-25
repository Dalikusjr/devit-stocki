<?php
class Clients
{
    private $conn;
    private $table = "clients";

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
            "SELECT idclient,
                        email,
                        nom,
                        tel,
                        adresse
                FROM " . $this->table
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE idclient=:id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function update($id, $email, $nom, $tel, $adresse)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET email=:email,
                adresse=:adresse,
                nom=:nom,
                tel=:tel
                WHERE idclient=:id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':email' => $email,
            ':nom' => $nom,
            ':tel' => $tel,
            ':adresse' => $adresse
        ]);
    }

    public function add($email, $nom, $tel, $adresse)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (email,nom,tel,adresse) 
             VALUES (:email,:nom,:tel,:adresse)"
        );

        return $stmt->execute([
            ':email' => $email,
            ':nom' => $nom,
            ':tel' => $tel,
            ':adresse' => $adresse
        ]);
    }
}
