<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    use ImageUploadTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 150)]
    private ?string $nom = null;
    #[ORM\Column(length: 180, unique: true)]
    private ?string $slug = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Media $photoMedia = null;
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephone = null;
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horaires = null;
    #[ORM\Column]
    private bool $estActif = true;
    #[ORM\Column]
    private bool $estMisEnAvant = false;
    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $dateModification = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->dateModification = new \DateTimeImmutable();
    }

    public function __toString(): string { return $this->nom ?? 'Service'; }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $value): static { $this->nom = $value; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $value): static { $this->slug = $value; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $value): static { $this->description = $value; return $this; }
    public function getPhotoMedia(): ?Media { return $this->photoMedia; }
    public function setPhotoMedia(?Media $value): static { $this->photoMedia = $value; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $value): static { $this->telephone = $value; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $value): static { $this->email = $value; return $this; }
    public function getHoraires(): ?string { return $this->horaires; }
    public function setHoraires(?string $value): static { $this->horaires = $value; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $value): static { $this->estActif = $value; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $value): static { $this->estMisEnAvant = $value; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $value): static { $this->dateCreation = $value; return $this; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }
    public function setDateModification(\DateTimeImmutable $value): static { $this->dateModification = $value; return $this; }
}
