<?php

namespace App\Entity;

use App\Repository\CategorieOeuvreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieOeuvreRepository::class)]
class CategorieOeuvre
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 150)] private ?string $nom = null;
    #[ORM\Column(length: 180, unique: true)] private ?string $slug = null;
    #[ORM\Column] private bool $estActif = true;
    public function __toString(): string { return $this->nom ?? 'Catégorie'; }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $v): static { $this->slug = $v; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $v): static { $this->estActif = $v; return $this; }
}
