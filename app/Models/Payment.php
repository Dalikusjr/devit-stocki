<?php

namespace App\Models;

class Payment
{
    private ?int $id;
    private int $idproj;
    private float $echeance;
    private string $commentaire;
    private string $date_echeance;
    private ?string $date_payement;
    private string $status;

    public function __construct() {}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getEcheance(): float
    {
        return $this->echeance;
    }
    public function setEcheance(float $echeance): void
    {
        $this->echeance = $echeance;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }
    public function getDateEcheance(): string
    {
        return $this->date_echeance;
    }
    public function setDateEcheance(string $date_echeance): void
    {
        $this->date_echeance = $date_echeance;
    }

    public function getDatePayement(): string
    {
        return $this->date_payement;
    }
    public function setDatePayement(string $date_payement): void
    {
        $this->date_payement = $date_payement;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function getIdProj(): int
    {
        return $this->idproj;
    }
    public function setIdProj(int $idproj): void
    {
        $this->idproj = $idproj;
    }
}
