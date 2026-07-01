<?php
class Maintains
{
    private $conn;
    private $table = "maintains";

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

    public function getById($id){
        $stmt = $this->conn->prepare("SELECT * FROM ".$this->table." WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT m.id,
                        m.montant,
                        m.status,
                        p.idproj,
                        p.titre,
                        c.idclient,
                        c.nom,
                        c.tel
                FROM " . $this->table . " m"
                . " JOIN projects p ON m.id_proj=p.idproj"
                . " JOIN clients c ON m.id_client=c.idclient"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE id=:id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function update($id, $idproj, $idclient, $montant, $status)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET id_proj=:idproj,
                id_client=:idclient,
                montant=:montant,
                status=:status
                WHERE id=:id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':idproj' => $idproj,
            ':idclient' => $idclient,
            ':montant' => $montant,
            ':status' => $status
        ]);
    }

    public function updateStatus($id, $status)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET status=:status
                WHERE id=:id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status
        ]);
    }

    public function add($idproj, $idclient, $montant, $status)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (id_proj,id_client,montant,status) 
             VALUES (:idproj,:idclient,:montant,:status)"
        );

        return $stmt->execute([
            ':idproj' => $idproj,
            ':idclient' => $idclient,
            ':montant' => $montant,
            ':status' => $status
        ]);
    }
}
