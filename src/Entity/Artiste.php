<?php

namespace App\Entity;

use App\Repository\ArtisteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtisteRepository::class)]
class Artiste
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 150)] private ?string $nom = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $biographie = null;
    #[ORM\Column(length: 150, nullable: true)] private ?string $specialite = null;
    #[ORM\Column(length: 180, nullable: true)] private ?string $email = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $siteWeb = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $facebook = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $instagram = null;
    #[ORM\Column] private bool $estActif = true;
    #[ORM\Column] private ?\DateTimeImmutable $dateCreation = null;
    #[ORM\Column] private ?\DateTimeImmutable $dateModification = null;

    public function __construct() { $this->dateCreation = new \DateTimeImmutable(); $this->dateModification = new \DateTimeImmutable(); }
    public function __toString(): string { return $this->nom ?? 'Artiste'; }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }
    public function getBiographie(): ?string { return $this->biographie; }
    public function setBiographie(?string $v): static { $this->biographie = $v; return $this; }
    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(?string $v): static { $this->specialite = $v; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): static { $this->email = $v; return $this; }
    public function getSiteWeb(): ?string { return $this->siteWeb; }
    public function setSiteWeb(?string $v): static { $this->siteWeb = $v; return $this; }
    public function getFacebook(): ?string { return $this->facebook; }
    public function setFacebook(?string $v): static { $this->facebook = $v; return $this; }
    public function getInstagram(): ?string { return $this->instagram; }
    public function setInstagram(?string $v): static { $this->instagram = $v; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $v): static { $this->estActif = $v; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }
    public function setDateModification(\DateTimeImmutable $v): static { $this->dateModification = $v; return $this; }
}
