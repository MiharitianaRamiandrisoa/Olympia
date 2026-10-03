<?php

namespace App\Entity;

use App\Repository\CategorieBoutiqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieBoutiqueRepository::class)]
class CategorieBoutique
{
    public function __toString(): string { return $this->nom ?? 'Catégorie'; }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private bool $estActif = true;

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): static { $this->estActif = $estActif; return $this; }
}
