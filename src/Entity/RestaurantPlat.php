<?php

namespace App\Entity;

use App\Repository\RestaurantPlatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantPlatRepository::class)]
#[ORM\Table(name: 'restaurant_plat', indexes: [
    new ORM\Index(name: 'IDX_RESTAURANT_PLAT_RESTAURANT', columns: ['restaurant_id']),
    new ORM\Index(name: 'IDX_RESTAURANT_PLAT_ORDRE', columns: ['restaurant_id', 'ordre']),
])]
class RestaurantPlat
{
    use ImageUploadTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'plats')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Media $imageMedia = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $estActif = true;

    public function __toString(): string
    {
        return $this->nom ?: 'Plat';
    }

    public function getId(): ?int { return $this->id; }
    public function getRestaurant(): ?Restaurant { return $this->restaurant; }
    public function setRestaurant(?Restaurant $restaurant): static { $this->restaurant = $restaurant; return $this; }
    public function getImageMedia(): ?Media { return $this->imageMedia; }
    public function setImageMedia(?Media $imageMedia): static { $this->imageMedia = $imageMedia; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPrix(): ?string { return $this->prix; }
    public function setPrix(?string $prix): static { $this->prix = $prix; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $estActif): static { $this->estActif = $estActif; return $this; }
}
