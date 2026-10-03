<?php

namespace App\Entity;

use App\Repository\BoutiqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoutiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Boutique
{
    use EnseigneFormDataTrait;
    use HorairesFormDataTrait;
    use PhotoUploadTrait;
    public function __toString(): string { return (string) ($this->enseigne ?? 'Boutique'); }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Enseigne::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Enseigne $enseigne = null;

    #[ORM\ManyToOne(targetEntity: CategorieBoutique::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CategorieBoutique $categorie = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Media $photoMedia = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $horaires = null;

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
        $now = new \DateTimeImmutable();
        $this->dateCreation = $now;
        $this->dateModification = $now;
    }

    #[ORM\PreUpdate]
    public function mettreAJourDateModification(): void { $this->dateModification = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getEnseigne(): ?Enseigne { return $this->enseigne; }
    public function setEnseigne(Enseigne $enseigne): static { $this->enseigne = $enseigne; return $this; }
    public function getCategorie(): ?CategorieBoutique { return $this->categorie; }
    public function setCategorie(?CategorieBoutique $categorie): static { $this->categorie = $categorie; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPhotoMedia(): ?Media { return $this->photoMedia; }
    public function setPhotoMedia(?Media $photoMedia): static { $this->photoMedia = $photoMedia; return $this; }
    public function getHoraires(): ?array { return $this->horaires; }
    public function setHoraires(?array $horaires): static { $this->horaires = $horaires; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): static { $this->estActif = $estActif; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $estMisEnAvant): static { $this->estMisEnAvant = $estMisEnAvant; return $this; }
}
