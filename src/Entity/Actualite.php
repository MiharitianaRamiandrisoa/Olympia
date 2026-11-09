<?php

namespace App\Entity;

use App\Repository\ActualiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActualiteRepository::class)]
#[ORM\Table(
    name: 'actualite',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_ACTUALITE_ORIGINE_EXTERNE', columns: ['origine', 'identifiant_externe']),
    ],
)]
class Actualite
{
    use ImageUploadTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $titre = null;

    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(length: 30)]
    private string $origine = 'manuelle';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identifiantExterne = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $urlExterne = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $urlImageExterne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chapeau = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Media $imageMedia = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column]
    private bool $estActif = true;

    #[ORM\Column]
    private bool $estMisEnAvant = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateModification = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->datePublication = $now;
        $this->dateCreation = $now;
        $this->dateModification = $now;
    }

    public function __toString(): string { return $this->titre ?? 'Actualité'; }
    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $value): static { $this->titre = $value; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $value): static { $this->slug = $value; return $this; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(?string $value): static { $this->categorie = $value; return $this; }
    public function getOrigine(): string { return $this->origine; }
    public function setOrigine(string $value): static { $this->origine = $value; return $this; }
    public function getIdentifiantExterne(): ?string { return $this->identifiantExterne; }
    public function setIdentifiantExterne(?string $value): static { $this->identifiantExterne = $value; return $this; }
    public function getUrlExterne(): ?string { return $this->urlExterne; }
    public function setUrlExterne(?string $value): static { $this->urlExterne = $value; return $this; }
    public function getUrlImageExterne(): ?string { return $this->urlImageExterne; }
    public function setUrlImageExterne(?string $value): static { $this->urlImageExterne = $value; return $this; }
    public function getChapeau(): ?string { return $this->chapeau; }
    public function setChapeau(?string $value): static { $this->chapeau = $value; return $this; }
    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(?string $value): static { $this->contenu = $value; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $value): static { $this->imageMedia = $value; return $this; }
    public function getDatePublication(): ?\DateTimeImmutable { return $this->datePublication; }
    public function setDatePublication(\DateTimeImmutable $value): static { $this->datePublication = $value; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $value): static { $this->estActif = $value; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $value): static { $this->estMisEnAvant = $value; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $value): static { $this->dateCreation = $value; return $this; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }
    public function setDateModification(\DateTimeImmutable $value): static { $this->dateModification = $value; return $this; }
}
