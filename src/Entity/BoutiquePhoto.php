<?php

namespace App\Entity;

use App\Repository\BoutiquePhotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoutiquePhotoRepository::class)]
#[ORM\Table(name: 'boutique_photo', indexes: [
    new ORM\Index(name: 'IDX_BOUTIQUE_PHOTO_BOUTIQUE', columns: ['boutique_id']),
    new ORM\Index(name: 'IDX_BOUTIQUE_PHOTO_ORDRE', columns: ['boutique_id', 'ordre']),
])]
class BoutiquePhoto
{
    use ImageUploadTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Boutique $boutique = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Media $imageMedia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legende = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $estActif = true;

    public function __toString(): string
    {
        return $this->legende ?: 'Photo de boutique';
    }

    public function getId(): ?int { return $this->id; }
    public function getBoutique(): ?Boutique { return $this->boutique; }
    public function setBoutique(?Boutique $boutique): static { $this->boutique = $boutique; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $imageMedia): static { $this->imageMedia = $imageMedia; return $this; }
    public function getLegende(): ?string { return $this->legende; }
    public function setLegende(?string $legende): static { $this->legende = $legende; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): static { $this->estActif = $estActif; return $this; }
}
