<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    use EnseigneFormDataTrait;
    use HorairesFormDataTrait;
    use PhotoUploadTrait;
    use GalleryUploadTrait;
    public function __toString(): string { return (string) ($this->enseigne ?? 'Restaurant'); }
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: Enseigne::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Enseigne $enseigne = null;
    #[ORM\ManyToOne(targetEntity: CategorieRestaurant::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CategorieRestaurant $categorie = null;
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

    /** @var Collection<int, RestaurantPhoto> */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: RestaurantPhoto::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC', 'id' => 'ASC'])]
    private Collection $photos;

    /** @var Collection<int, RestaurantPlat> */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: RestaurantPlat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC', 'id' => 'ASC'])]
    private Collection $plats;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->plats = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getEnseigne(): ?Enseigne { return $this->enseigne; }
    public function setEnseigne(Enseigne $value): static { $this->enseigne = $value; return $this; }
    public function getCategorie(): ?CategorieRestaurant { return $this->categorie; }
    public function setCategorie(?CategorieRestaurant $value): static { $this->categorie = $value; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $value): static { $this->description = $value; return $this; }
    public function getPhotoMedia(): ?Media { return $this->photoMedia; }
    public function setPhotoMedia(?Media $value): static { $this->photoMedia = $value; return $this; }
    public function getHoraires(): ?array { return $this->horaires; }
    public function setHoraires(?array $value): static { $this->horaires = $value; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $value): static { $this->estActif = $value; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $value): static { $this->estMisEnAvant = $value; return $this; }
    /** @return Collection<int, RestaurantPhoto> */
    public function getPhotos(): Collection { return $this->photos; }
    public function addPhoto(RestaurantPhoto $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setRestaurant($this);
        }

        return $this;
    }
    public function removePhoto(RestaurantPhoto $photo): static
    {
        if ($this->photos->removeElement($photo) && $photo->getRestaurant() === $this) {
            $photo->setRestaurant(null);
        }

        return $this;
    }
    /** @return Collection<int, RestaurantPlat> */
    public function getPlats(): Collection { return $this->plats; }
    public function addPlat(RestaurantPlat $plat): static
    {
        if (!$this->plats->contains($plat)) {
            $this->plats->add($plat);
            $plat->setRestaurant($this);
        }

        return $this;
    }
    public function removePlat(RestaurantPlat $plat): static
    {
        if ($this->plats->removeElement($plat) && $plat->getRestaurant() === $this) {
            $plat->setRestaurant(null);
        }

        return $this;
    }
}
