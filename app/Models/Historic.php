<?php

namespace App\Models;

class Historic
{
    private ?int $id;
    private int $subscriptionId;
    private int $clientId;
    private float $montant;
    private string $type;
    private ?string $payed_at;

    public function __construct() {}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }
    public function setSubscriptionId(int $id_Subscr): void
    {
        $this->subscriptionId = $id_Subscr;
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

    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDatePayement(): string
    {
        return $this->payed_at;
    }
    public function setDatePayement(string $payed_at): void
    {
        $this->payed_at = $payed_at;
    }
}
