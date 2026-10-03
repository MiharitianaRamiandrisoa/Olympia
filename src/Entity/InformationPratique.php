<?php

namespace App\Entity;

use App\Repository\InformationPratiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InformationPratiqueRepository::class)]
#[ORM\Table(name: 'information_pratique')]
class InformationPratique
{
    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->dateCreation = $now;
        $this->dateModification = $now;
    }

    public function __toString(): string { return $this->titre ?? 'Information pratique'; }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 100)] private ?string $type = null;
    #[ORM\Column(length: 200)] private ?string $titre = null;
    #[ORM\Column(type: Types::TEXT)] private ?string $contenu = null;
    #[ORM\Column] private int $ordreAffichage = 0;
    #[ORM\Column] private bool $estActif = true;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private ?\DateTimeImmutable $dateCreation = null;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private ?\DateTimeImmutable $dateModification = null;
    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $value): static { $this->type = $value; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $value): static { $this->titre = $value; return $this; }
    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $value): static { $this->contenu = $value; return $this; }
    public function getOrdreAffichage(): int { return $this->ordreAffichage; }
    public function setOrdreAffichage(int $value): static { $this->ordreAffichage = $value; return $this; }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $value): static { $this->estActif = $value; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $value): static { $this->dateCreation = $value; return $this; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }
    public function setDateModification(\DateTimeImmutable $value): static { $this->dateModification = $value; return $this; }
}
