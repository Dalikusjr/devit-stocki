<?php

namespace App\Models;

class User
{
    private ?int $id;
    private string $email;
    private string $password;
    private string $nom;
    private string $prenom;
    private string $role;
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

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }
    public function getPrenom(): string
    {
        return $this->prenom;
    }
    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }
    public function getRole(): string
    {
        return $this->role;
    }
    public function setRole(string $role): void
    {
        $this->role = $role;
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
