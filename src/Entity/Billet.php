<?php

namespace App\Entity;

use App\Repository\BilletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BilletRepository::class)]
#[ORM\Table(name: 'billet')]
#[ORM\Index(name: 'idx_billet_statut', columns: ['statut'])]
class Billet
{
    public const STATUT_VALIDE = 'valide';
    public const STATUT_UTILISE = 'utilise';
    public const STATUT_ANNULE = 'annule';

    public function __toString(): string { return $this->reference ?: 'Billet'; }

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'billets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Reservation $reservation = null;

    #[ORM\Column(length: 28, unique: true)]
    private string $reference;

    #[ORM\Column(length: 64, unique: true)]
    private string $jeton;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_VALIDE;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateEmission;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateUtilisation = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    #[ORM\OneToMany(mappedBy: 'billet', targetEntity: ControleEntree::class, orphanRemoval: true)]
    private Collection $controles;

    public function __construct()
    {
        $this->reference = '';
        $this->jeton = '';
        $this->dateEmission = new \DateTimeImmutable();
        $this->controles = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getReservation(): ?Reservation { return $this->reservation; }
    public function setReservation(?Reservation $value): static { $this->reservation = $value; return $this; }
    public function getReference(): string { return $this->reference; }
    public function setReference(string $value): static { $this->reference = $value; return $this; }
    public function getJeton(): string { return $this->jeton; }
    public function setJeton(string $value): static { $this->jeton = $value; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $value): static { $this->statut = $value; return $this; }
    public function getDateEmission(): \DateTimeImmutable { return $this->dateEmission; }
    public function setDateEmission(\DateTimeImmutable $value): static { $this->dateEmission = $value; return $this; }
    public function getDateUtilisation(): ?\DateTimeImmutable { return $this->dateUtilisation; }
    public function setDateUtilisation(?\DateTimeImmutable $value): static { $this->dateUtilisation = $value; return $this; }
    public function getControles(): Collection { return $this->controles; }
    public function isUtilisable(): bool { return $this->statut === self::STATUT_VALIDE; }
}
