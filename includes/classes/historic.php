<?php
class Historic
{
    private $conn;
    private $table = "historic";

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

    public function getAllById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT montant,payed_at 
            FROM " . $this->table .   
            " WHERE id_pay=:id AND type='maintenance'"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->table
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByType($id_pay, $type)
    {
        // Calculer le premier jour du mois courant
        $startOfMonth = date('Y-m-01 00:00:00');

        // Calculer le premier jour du mois prochain (pour la limite supérieure)
        $startOfNextMonth = date('Y-m-01 00:00:00', strtotime('first day of next month'));

        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table}
            WHERE id_pay = :id 
            AND type = :type 
            AND payed_at >= :start 
            AND payed_at < :end"
        );

        return $stmt->execute([
            ':id'    => $id_pay,
            ':type'  => $type,
            ':start' => $startOfMonth,
            ':end'   => $startOfNextMonth
        ]);
    }

    public function delete($id)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE id=:id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function update($id, $id_pay, $type, $id_client)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET id_pay=:id_pay,
                type=:type,
                id_client=:id_client,
                WHERE id=:id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':id_pay' => $id_pay,
            ':type' => $type,
            ':id_client' => $id_client
        ]);
    }

    public function add($id_pay,$montant, $type, $id_client)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (id_pay,montant,type,id_client) 
             VALUES (:idpay,:montant,:type,:idclient)"
        );

        return $stmt->execute([
            ':idpay' => $id_pay,
            ':montant' => $montant,
            ':type' => $type,
            ':idclient' => $id_client
        ]);
    }
}
