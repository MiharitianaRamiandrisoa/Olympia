<?php

namespace App\Entity;

use App\Repository\OeuvreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OeuvreRepository::class)]
class Oeuvre
{
    use ImageUploadTrait;
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 200)] private ?string $titre = null;
    #[ORM\Column(length: 220, unique: true)] private ?string $slug = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)] private ?string $description = null;
    #[ORM\Column(length: 150, nullable: true)] private ?string $technique = null;
    #[ORM\Column(length: 100, nullable: true)] private ?string $dimensions = null;
    #[ORM\Column(nullable: true)] private ?int $annee = null;
    #[ORM\Column] private int $ordre = 0;
    #[ORM\ManyToOne(targetEntity: Artiste::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Artiste $artiste = null;
    #[ORM\ManyToOne(targetEntity: CategorieOeuvre::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?CategorieOeuvre $categorie = null;
    #[ORM\ManyToOne(targetEntity: Exposition::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Exposition $exposition = null;
    #[ORM\ManyToOne(targetEntity: Media::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Media $imageMedia = null;
    #[ORM\Column] private bool $estActive = true;
    #[ORM\Column] private bool $estMiseEnAvant = false;
    #[ORM\Column] private ?\DateTimeImmutable $dateCreation = null;
    #[ORM\Column] private ?\DateTimeImmutable $dateModification = null;
    public function __construct() { $this->dateCreation = new \DateTimeImmutable(); $this->dateModification = new \DateTimeImmutable(); }
    public function __toString(): string { return $this->titre ?? 'Œuvre'; }
    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre = $v; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $v): static { $this->slug = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getTechnique(): ?string { return $this->technique; }
    public function setTechnique(?string $v): static { $this->technique = $v; return $this; }
    public function getDimensions(): ?string { return $this->dimensions; }
    public function setDimensions(?string $v): static { $this->dimensions = $v; return $this; }
    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(?int $v): static { $this->annee = $v; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $v): static { $this->ordre = $v; return $this; }
    public function getArtiste(): ?Artiste { return $this->artiste; }
    public function setArtiste(?Artiste $v): static { $this->artiste = $v; return $this; }
    public function getCategorie(): ?CategorieOeuvre { return $this->categorie; }
    public function setCategorie(?CategorieOeuvre $v): static { $this->categorie = $v; return $this; }
    public function getExposition(): ?Exposition { return $this->exposition; }
    public function setExposition(?Exposition $v): static { $this->exposition = $v; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $v): static { $this->imageMedia = $v; return $this; }
    public function isEstActive(): bool { return $this->estActive; }
    public function setEstActive(bool $v): static { $this->estActive = $v; return $this; }
    public function isEstMiseEnAvant(): bool { return $this->estMiseEnAvant; }
    public function setEstMiseEnAvant(bool $v): static { $this->estMiseEnAvant = $v; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }
    public function setDateModification(\DateTimeImmutable $v): static { $this->dateModification = $v; return $this; }
}
