<?php

namespace App\Entity;

use App\Repository\ControleEntreeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ControleEntreeRepository::class)]
#[ORM\Table(name: 'controle_entree')]
#[ORM\UniqueConstraint(name: 'uniq_controle_entree_succes', columns: ['cle_succes'])]
#[ORM\Index(name: 'idx_controle_date', columns: ['date_controle'])]
class ControleEntree
{
    public const RESULTAT_VALIDE = 'valide';
    public const RESULTAT_DEJA_UTILISE = 'deja_utilise';
    public const RESULTAT_ANNULE = 'annule';
    public const RESULTAT_INCONNU = 'inconnu';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'controles')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Billet $billet = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 24)]
    private string $resultat;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $cleSucces = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateControle;

    public function __construct()
    {
        $this->dateControle = new \DateTimeImmutable();
        $this->resultat = self::RESULTAT_INCONNU;
    }

    public function getId(): ?int { return $this->id; }
    public function getBillet(): ?Billet { return $this->billet; }
    public function setBillet(?Billet $value): static { $this->billet = $value; return $this; }
    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $value): static { $this->utilisateur = $value; return $this; }
    public function getResultat(): string { return $this->resultat; }
    public function setResultat(string $value): static { $this->resultat = $value; return $this; }
    public function getCleSucces(): ?string { return $this->cleSucces; }
    public function setCleSucces(?string $value): static { $this->cleSucces = $value; return $this; }
    public function getDateControle(): \DateTimeImmutable { return $this->dateControle; }
    public function setDateControle(\DateTimeImmutable $value): static { $this->dateControle = $value; return $this; }
}
