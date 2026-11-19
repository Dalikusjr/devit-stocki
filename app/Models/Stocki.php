<?php

namespace App\Models;

class Stocki
{
    private ?int $id;
    private int $clientId;
    private string $paymentType;
    private float $montant;
    private string $status;
    private string $startDate;

    public function __construct() {}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getclientId(): int
    {
        return $this->clientId;
    }

    public function setclientId(int $id_Client): void
    {
        $this->clientId = $id_Client;
    }

    public function getPaymentType(): string
    {
        return $this->paymentType;
    }
    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }
    public function setMontant(float $montant): void
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

    public function getStartDate(): string
    {
        return $this->startDate;
    }
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }
}
