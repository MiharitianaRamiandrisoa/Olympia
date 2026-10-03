<?php
namespace App\Entity;
use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    use ImageUploadTrait;
    public function __toString(): string { return $this->titre ?? 'Événement'; }
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: CategorieEvenement::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?CategorieEvenement $categorie = null;
    #[ORM\ManyToOne(targetEntity: Enseigne::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Enseigne $enseigne = null;
    #[ORM\Column(length: 200)] private ?string $titre = null;
    #[ORM\Column(length: 220, unique: true)] private ?string $slug = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $description = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $informationsComplementaires = null;
    #[ORM\ManyToOne(targetEntity: Media::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?Media $imageMedia = null;
    #[ORM\Column] private ?\DateTimeImmutable $dateDebut = null;
    #[ORM\Column(nullable: true)] private ?\DateTimeImmutable $dateFin = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $lieu = null;
    #[ORM\Column] private bool $estActif = true;
    #[ORM\Column] private bool $estMisEnAvant = false;
    public function getId(): ?int { return $this->id; }
    public function getCategorie(): ?CategorieEvenement { return $this->categorie; }
    public function setCategorie(?CategorieEvenement $v): static { $this->categorie = $v; return $this; }
    public function getEnseigne(): ?Enseigne { return $this->enseigne; }
    public function setEnseigne(?Enseigne $v): static { $this->enseigne = $v; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $v): static { $this->titre = $v; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $v): static { $this->slug = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function getInformationsComplementaires(): ?string { return $this->informationsComplementaires; }
    public function setInformationsComplementaires(?string $v): static { $this->informationsComplementaires = $v; return $this; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $v): static { $this->imageMedia = $v; return $this; }
    public function getDateDebut(): ?\DateTimeImmutable { return $this->dateDebut; }
    public function setDateDebut(\DateTimeImmutable $v): static { $this->dateDebut = $v; return $this; }
    public function getDateFin(): ?\DateTimeImmutable { return $this->dateFin; }
    public function setDateFin(?\DateTimeImmutable $v): static { $this->dateFin = $v; return $this; }
    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $v): static { $this->lieu = $v; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $v): static { $this->estActif = $v; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $v): static { $this->estMisEnAvant = $v; return $this; }
}
