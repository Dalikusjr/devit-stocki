<?php

namespace App\Models;

class Client
{
    private ?int $id;
    private string $email;
    private string $nom;
    private string $adresse;
    private string $telephone;
    public function __construct() {}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }
    public function setAdresse(string $adresse): void
    {
        $this->adresse = $adresse;
    }
    public function getTelephone(): string
    {
        return $this->telephone;
    }
    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }
}
