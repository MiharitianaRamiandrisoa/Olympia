<?php

namespace App\Entity;

use App\Repository\ExpositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpositionRepository::class)]
class Exposition
{
    use ImageUploadTrait;
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 200)] private ?string $titre = null;
    #[ORM\Column(length: 220, unique: true)] private ?string $slug = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)] private ?string $description = null;
    #[ORM\Column(length: 180, nullable: true)] private ?string $lieu = null;
    #[ORM\Column(type: Types::DATE_IMMUTABLE)] private ?\DateTimeImmutable $dateDebut = null;
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)] private ?\DateTimeImmutable $dateFin = null;
    #[ORM\ManyToOne(targetEntity: Media::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Media $imageMedia = null;
    #[ORM\Column] private bool $estActive = true;
    #[ORM\Column] private bool $estMiseEnAvant = false;
    public function __toString(): string { return $this->titre ?? 'Exposition'; }
    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre = $v; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $v): static { $this->slug = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $v): static { $this->lieu = $v; return $this; }
    public function getDateDebut(): ?\DateTimeImmutable { return $this->dateDebut; }
    public function setDateDebut(\DateTimeImmutable $v): static { $this->dateDebut = $v; return $this; }
    public function getDateFin(): ?\DateTimeImmutable { return $this->dateFin; }
    public function setDateFin(?\DateTimeImmutable $v): static { $this->dateFin = $v; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $v): static { $this->imageMedia = $v; return $this; }
    public function isEstActive(): bool { return $this->estActive; }
    public function setEstActive(bool $v): static { $this->estActive = $v; return $this; }
    public function isEstMiseEnAvant(): bool { return $this->estMiseEnAvant; }
    public function setEstMiseEnAvant(bool $v): static { $this->estMiseEnAvant = $v; return $this; }
}
