<?php

namespace App\Entity;

use App\Repository\MessageContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageContactRepository::class)]
class MessageContact
{
    public function __toString(): string { return $this->nom ?? 'Message'; }
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 150)] private ?string $nom = null;
    #[ORM\Column(length: 180)] private ?string $email = null;
    #[ORM\Column(length: 50, nullable: true)] private ?string $telephone = null;
    #[ORM\Column(length: 200, nullable: true)] private ?string $sujet = null;
    #[ORM\Column(type: 'text')] private ?string $message = null;
    #[ORM\Column] private bool $estLu = false;
    #[ORM\Column] private ?\DateTimeImmutable $dateCreation = null;

    public function __construct() { $this->dateCreation = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $value): static { $this->nom = $value; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $value): static { $this->email = $value; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $value): static { $this->telephone = $value; return $this; }
    public function getSujet(): ?string { return $this->sujet; }
    public function setSujet(?string $value): static { $this->sujet = $value; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $value): static { $this->message = $value; return $this; }
    public function isEstLu(): bool { return $this->estLu; }
    public function setEstLu(bool $value): static { $this->estLu = $value; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
}
