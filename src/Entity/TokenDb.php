<?php

namespace App\Entity;

use App\Repository\TokenDbRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TokenDbRepository::class)]
class TokenDb
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25000)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $ServiceName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getServiceName(): ?string
    {
        return $this->ServiceName;
    }

    public function setServiceName(string $ServiceName): static
    {
        $this->ServiceName = $ServiceName;

        return $this;
    }
}
