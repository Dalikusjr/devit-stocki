<?php
class Projects
{
    private $conn;
    private $table = "projects";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function count()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();
        return (int)$stmt->fetchColumn(); // retourne un nombre
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT 
            pr.idproj,
            pr.titre,
            pr.datedebut,
            pr.daterenouv,
            pr.couttot,
            pr.acces,
            cl.idclient,
            cl.nom clnom,
            cl.tel cltel,
            calculerResteProjet(pr.idproj) AS restepaye
        FROM " . $this->table . " pr
         INNER JOIN clients cl ON pr.idclient=cl.idclient         "
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE idproj=:id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function update($idproj,$titre, $datedebut, $daterenouv, $couttot, $acces)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET titre=:titre,
                datedebut=:datedebut,
                daterenouv=:daterenouv,
                couttot=:couttot,
                acces=:acces
                WHERE idproj=:idproj"
        );
        return $stmt->execute([
            ':idproj' => $idproj,
            ':titre' => $titre,
            ':datedebut' => $datedebut,
            ':daterenouv' => $daterenouv,
            ':couttot' => $couttot,
            ':acces' => $acces
        ]);
    }

    public function add($titre, $datedebut, $daterenouv,$couttot,$acces,$idclient)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (titre,datedebut,daterenouv,couttot,acces,idclient) 
             VALUES (:titre,:datedebut,:daterenouv,:couttot,:acces,:idclient)"
        );

        return $stmt->execute([
            ':titre' => $titre,
            ':datedebut' => $datedebut,
            ':daterenouv' => $daterenouv,
            ':couttot' => $couttot,            
            ':acces' => $acces,
            ':idclient' => $idclient
        ]);
    }
}
