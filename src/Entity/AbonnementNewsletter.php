<?php
namespace App\Entity;
use App\Repository\AbonnementNewsletterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementNewsletterRepository::class)]
class AbonnementNewsletter
{
    public function __toString(): string { return $this->email ?? 'Abonnement'; }
    #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id = null;
    #[ORM\Column(length: 180, unique: true)] private ?string $email = null;
    #[ORM\Column] private ?\DateTimeImmutable $dateCreation = null;
    public function __construct() { $this->dateCreation = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = mb_strtolower(trim($email)); return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
}
