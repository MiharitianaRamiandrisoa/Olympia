<?php
namespace App\Entity;
use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    public const TYPE_GRATUIT = 'gratuit';
    public const TYPE_PAYANT = 'payant';

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
    #[ORM\Column(nullable: true)] private ?int $capaciteMaximale = null;
    #[ORM\Column(nullable: true)] private ?\DateTimeImmutable $dateOuvertureReservations = null;
    #[ORM\Column(nullable: true)] private ?\DateTimeImmutable $dateClotureReservations = null;
    #[ORM\Column(length: 20, options: ['default' => self::TYPE_GRATUIT])] private string $typeBilletterie = self::TYPE_GRATUIT;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)] private ?string $tarif = null;
    #[ORM\Column] private bool $estActif = true;
    #[ORM\Column] private bool $estMisEnAvant = false;
    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Reservation::class, orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $reservations;

    public function __construct()
    {
        $this->reservations = new \Doctrine\Common\Collections\ArrayCollection();
    }
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
    public function getCapaciteMaximale(): ?int { return $this->capaciteMaximale; }
    public function setCapaciteMaximale(?int $v): static { $this->capaciteMaximale = $v; return $this; }
    public function getDateOuvertureReservations(): ?\DateTimeImmutable { return $this->dateOuvertureReservations; }
    public function setDateOuvertureReservations(?\DateTimeImmutable $v): static { $this->dateOuvertureReservations = $v; return $this; }
    public function getDateClotureReservations(): ?\DateTimeImmutable { return $this->dateClotureReservations; }
    public function setDateClotureReservations(?\DateTimeImmutable $v): static { $this->dateClotureReservations = $v; return $this; }
    public function getTypeBilletterie(): string { return $this->typeBilletterie; }
    public function setTypeBilletterie(string $v): static { $this->typeBilletterie = $v; return $this; }
    public function getTarif(): ?string { return $this->tarif; }
    public function setTarif(?string $v): static { $this->tarif = $v; return $this; }
    public function isGratuit(): bool { return $this->typeBilletterie === self::TYPE_GRATUIT; }
    public function isPublie(): bool { return $this->estActif; }
    public function setPublie(bool $v): static { $this->estActif = $v; return $this; }
    public function getReservations(): \Doctrine\Common\Collections\Collection { return $this->reservations; }
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setEvenement($this);
        }
        return $this;
    }
    public function isReservationOuverte(?\DateTimeImmutable $now = null): bool
    {
        $now ??= new \DateTimeImmutable();
        $nowAtMinute = $this->atMinute($now);

        return $this->isPublie()
            && $this->capaciteMaximale !== null
            && $this->dateOuvertureReservations !== null
            && $this->dateClotureReservations !== null
            && (!$this->dateDebut || $now <= $this->dateDebut)
            && $nowAtMinute >= $this->atMinute($this->dateOuvertureReservations)
            && $nowAtMinute <= $this->atMinute($this->dateClotureReservations)
            && (!$this->dateFin || $now <= $this->dateFin);
    }

    private function atMinute(\DateTimeImmutable $date): \DateTimeImmutable
    {
        return $date->setTime((int) $date->format('H'), (int) $date->format('i'));
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->dateDebut && $this->dateFin && $this->dateFin <= $this->dateDebut) {
            $context->buildViolation('La date de fin doit etre posterieure a la date de debut.')
                ->atPath('dateFin')->addViolation();
        }
        if ($this->dateOuvertureReservations && $this->dateClotureReservations
            && $this->dateClotureReservations < $this->dateOuvertureReservations) {
            $context->buildViolation('La cloture des reservations doit suivre leur ouverture.')
                ->atPath('dateClotureReservations')->addViolation();
        }
        if ($this->dateDebut && $this->dateClotureReservations && $this->dateClotureReservations > $this->dateDebut) {
            $context->buildViolation('La cloture des reservations doit preceder le debut de l evenement.')
                ->atPath('dateClotureReservations')->addViolation();
        }
        if ($this->capaciteMaximale !== null && $this->capaciteMaximale < 1) {
            $context->buildViolation('La capacite doit etre superieure a zero.')
                ->atPath('capaciteMaximale')->addViolation();
        }
        if ($this->typeBilletterie === self::TYPE_PAYANT && ($this->tarif === null || (float) $this->tarif < 0)) {
            $context->buildViolation('Un tarif valide est requis pour un evenement payant.')
                ->atPath('tarif')->addViolation();
        }
    }
    public function isEstActif(): bool { return $this->estActif; }
    public function setEstActif(bool $v): static { $this->estActif = $v; return $this; }
    public function isEstMisEnAvant(): bool { return $this->estMisEnAvant; }
    public function setEstMisEnAvant(bool $v): static { $this->estMisEnAvant = $v; return $this; }
}
