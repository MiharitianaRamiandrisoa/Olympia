<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
#[ORM\Index(name: 'idx_reservation_event_status', columns: ['evenement_id', 'statut'])]
class Reservation
{
    public const STATUT_CONFIRMEE = 'confirmee';
    public const STATUT_EN_ATTENTE_PAIEMENT = 'en_attente_paiement';
    public const STATUT_ANNULEE = 'annulee';
    public const PAIEMENT_NON_APPLICABLE = 'non_applicable';
    public const PAIEMENT_EN_ATTENTE = 'en_attente';
    public const PAIEMENT_PAYE = 'paye';

    public function __toString(): string { return $this->reference ?: 'Reservation'; }

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Evenement $evenement = null;

    #[ORM\Column(length: 32, unique: true)]
    private string $reference;

    #[ORM\Column(length: 160)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    private string $nom;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 20)]
    private int $nombrePlaces;

    #[ORM\Column(length: 32)]
    private string $statut = self::STATUT_CONFIRMEE;

    #[ORM\Column(length: 32)]
    private string $statutPaiement = self::PAIEMENT_NON_APPLICABLE;

    #[ORM\Column(length: 64, unique: true)]
    private string $jetonAccesHash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateConfirmation = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateExpirationPaiement = null;

    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: Billet::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $billets;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->reference = '';
        $this->nom = '';
        $this->email = '';
        $this->nombrePlaces = 1;
        $this->jetonAccesHash = '';
        $this->billets = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getEvenement(): ?Evenement { return $this->evenement; }
    public function setEvenement(?Evenement $value): static { $this->evenement = $value; return $this; }
    public function getReference(): string { return $this->reference; }
    public function setReference(string $value): static { $this->reference = $value; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $value): static { $this->nom = trim($value); return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $value): static { $this->email = mb_strtolower(trim($value)); return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $value): static { $this->telephone = $value ? trim($value) : null; return $this; }
    public function getNombrePlaces(): int { return $this->nombrePlaces; }
    public function setNombrePlaces(int $value): static { $this->nombrePlaces = $value; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $value): static { $this->statut = $value; return $this; }
    public function getStatutPaiement(): string { return $this->statutPaiement; }
    public function setStatutPaiement(string $value): static { $this->statutPaiement = $value; return $this; }
    public function getJetonAccesHash(): string { return $this->jetonAccesHash; }
    public function setJetonAccesHash(string $value): static { $this->jetonAccesHash = $value; return $this; }
    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }
    public function getDateConfirmation(): ?\DateTimeImmutable { return $this->dateConfirmation; }
    public function setDateConfirmation(?\DateTimeImmutable $value): static { $this->dateConfirmation = $value; return $this; }
    public function getDateExpirationPaiement(): ?\DateTimeImmutable { return $this->dateExpirationPaiement; }
    public function setDateExpirationPaiement(?\DateTimeImmutable $value): static { $this->dateExpirationPaiement = $value; return $this; }
    public function isPaiementExpire(?\DateTimeImmutable $now = null): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE_PAIEMENT
            && $this->dateExpirationPaiement !== null
            && ($now ?? new \DateTimeImmutable()) >= $this->dateExpirationPaiement;
    }
    public function getBillets(): Collection { return $this->billets; }
    public function addBillet(Billet $billet): static
    {
        if (!$this->billets->contains($billet)) {
            $this->billets->add($billet);
            $billet->setReservation($this);
        }
        return $this;
    }
}
