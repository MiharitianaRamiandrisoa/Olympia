<?php
namespace App\Entity;
use App\Repository\CategoriePromotionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriePromotionRepository::class)]
class CategoriePromotion
{
    public function __toString(): string { return $this->nom ?? 'Catégorie'; }
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 150)] private ?string $nom = null;
    #[ORM\Column(length: 180, unique: true)] private ?string $slug = null;
    #[ORM\Column] private bool $estActif = true;
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $value): static { $this->nom = $value; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $value): static { $this->slug = $value; return $this; }
}
