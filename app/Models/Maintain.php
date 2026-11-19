<?php

namespace App\Models;

class Maintain
{
    private ?int $id;
    private int $projectId;
    private int $clientId;
    private float $montant;
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
    public function getProjectId(): int
    {
        return $this->projectId;
    }
    public function setProjectId(int $id_proj): void
    {
        $this->projectId = $id_proj;
    }

    public function getclientId(): int
    {
        return $this->clientId;
    }

    public function setclientId(string $id_Client): void
    {
        $this->clientId = $id_Client;
    }
    public function getMontant(): float
    {
        return $this->montant;
    }
    public function setMontant(string $montant): void
    {
        $this->montant = $montant;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
