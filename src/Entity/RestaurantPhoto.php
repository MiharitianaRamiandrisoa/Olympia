<?php

namespace App\Entity;

use App\Repository\RestaurantPhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantPhotoRepository::class)]
#[ORM\Table(name: 'restaurant_photo', indexes: [
    new ORM\Index(name: 'IDX_RESTAURANT_PHOTO_RESTAURANT', columns: ['restaurant_id']),
    new ORM\Index(name: 'IDX_RESTAURANT_PHOTO_ORDRE', columns: ['restaurant_id', 'ordre']),
])]
class RestaurantPhoto
{
    use ImageUploadTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Media $imageMedia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legende = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $estActif = true;

    public function __toString(): string
    {
        return $this->legende ?: 'Photo du restaurant';
    }

    public function getId(): ?int { return $this->id; }
    public function getRestaurant(): ?Restaurant { return $this->restaurant; }
    public function setRestaurant(?Restaurant $restaurant): static { $this->restaurant = $restaurant; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $imageMedia): static { $this->imageMedia = $imageMedia; return $this; }
    public function getLegende(): ?string { return $this->legende; }
    public function setLegende(?string $legende): static { $this->legende = $legende; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): static { $this->estActif = $estActif; return $this; }
}
