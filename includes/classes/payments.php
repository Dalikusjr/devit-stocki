<?php
class Payments
{
    private $conn;
    private $table = "payments";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function count()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        return (int)$stmt->fetchColumn(); // retourne un nombre
    }

    public function calculerResteProjet($idproj)
    {
        $stmt = $this->conn->prepare("SELECT calculerResteProjet(:idproj)");
        $stmt->execute([':idproj' => $idproj]);
        return (float)$stmt->fetchColumn(); // retourne un nombre
    }

    public function estMontantExcedant($idproj,$montant,$idpay = 0)
    {
        $stmt = $this->conn->prepare("SELECT estMontantExcedant(:idproj,:idpay,:montant)");
        $stmt->execute([':idproj' => $idproj,':idpay' => $idpay,':montant' => $montant]);
        return (bool)$stmt->fetchColumn(); // retourne un nombre
    }

    public function countProjPerClient()
    {
        $stmt = $this->conn->query("SELECT idclient, COUNT(*) as nb_projects FROM projects GROUP BY idclient");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function countTranches($idproj)
    {
        $stmt = $this->conn->prepare("SELECT 
                CalculerNombreTranchesPayer(:idproj) AS tranches_paye,
                CalculerNombreTranches(:idproj) AS tranches_total");
        $stmt->execute([':idproj' => $idproj]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPay($idpay)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE idpay=:idpay");
        $stmt->execute([':idpay' => $idpay]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function ListeProjPerClient($idclient)
    {
        $stmt = $this->conn->prepare("SELECT 
            pr.idproj, 
            pr.titre, 
            pr.couttot,
            calculerResteProjet(pr.idproj) AS restepaye,
            calculerNombreTranchesPayer(pr.idproj) AS tranches_paye,
            calculerNombreTranches(pr.idproj) AS tranches_total,
            -- Agrégation JSON isolée pour éviter l'erreur 1064
            COALESCE(
                    (SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'idpay', p.idpay,
                            'commentaire', p.commentaire,
                            'echeance', p.echeance,
                            'dateech', p.dateech,
                            'status', p.status,
                            'datepay', p.datepay 
                        )
                    ) FROM payments p WHERE p.idproj = pr.idproj),
                    JSON_ARRAY()
                ) AS tranches

        FROM projects pr
        WHERE pr.idclient = :idclient
        GROUP BY pr.idproj");

        $stmt->execute(['idclient' => $idclient]);
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($projets as &$projet) {
            $projet['tranches'] = json_decode($projet['tranches'] ?? '[]', true);
        }
        unset($projet);
        return $projets;
    }

    public function getAll()
    {
        $stmt = $this->conn->query(
            "SELECT 
            pr.idproj,
            pr.titre,
            pr.datedebut,
            pr.daterenouv,
            pr.couttot,
            pr.acces,
            cl.nom AS clnom,
            cl.tel AS cltel,
            -- Correction ici : on passe l'ID dynamique du projet
            calculerResteProjet(pr.idproj) AS restepaye
        FROM " . $this->table . " pr
        INNER JOIN clients cl ON pr.idclient = cl.idclient         
        GROUP BY pr.idproj"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($idpay)
    {
        $stmt  = $this->conn->prepare(
            "DELETE FROM {$this->table}
                WHERE idpay=:idpay"
        );
        return $stmt->execute([':idpay' => $idpay]);
    }

    public function update($idpay, $montant, $comment, $dateech, $status, $datepay)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET echeance=:montant,
                commentaire=:comment,
                dateech=:dateech,
                status=:status,
                datepay=:datepay
                WHERE idpay=:idpay"
        );
        return $stmt->execute([
            ':montant' => $montant,
            ':comment' => $comment,
            ':dateech' => $dateech,
            ':status' => $status,
            ':datepay' => $datepay,
            ':idpay' => $idpay
        ]);
    }

    public function updateStatus($status, $idpay)
    {
        $stmt  = $this->conn->prepare(
            "UPDATE  {$this->table}  
                SET status=:status
                WHERE idpay=:idpay"
        );
        return $stmt->execute([
            ':status' => $status,
            ':idpay' => $idpay
        ]);
    }

    public function add($montant, $comment, $dateech, $status, $idproj, $datepay)
    {

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (echeance,commentaire,dateech,status,datepay,idproj) 
             VALUES (:montant,:comment,:dateech,:status,:datepay,:idproj)"
        );

        return $stmt->execute([
            ':montant' => $montant,
            ':comment' => $comment,
            ':dateech' => $dateech,
            ':status' => $status,
            ':datepay' => $datepay,
            ':idproj' => $idproj
        ]);
    }
}
