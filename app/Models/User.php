<?php

namespace App\Models;

class User
{
    private array $userData;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    public function getId(): int
    {
        return (int)$this->userData['id'];
    }

    public function getUsername(): string
    {
        return $this->userData['username'];
    }

    public function getEmail(): string
    {
        return $this->userData['email'];
    }

    public function getRole(): string
    {
        return $this->userData['role'];
    }

    public function getFirstName(): string
    {
        return $this->userData['first_name'] ?? '';
    }

    public function getLastName(): string
    {
        return $this->userData['last_name'] ?? '';
    }

    public function toArray(): array
    {
        return $this->userData;
    }

    public function hasRole(string $role): bool
    {
        return $this->getRole() === $role;
    }
} 