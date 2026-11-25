<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'actualite_image', indexes: [
    new ORM\Index(name: 'IDX_ACTUALITE_IMAGE_ACTUALITE', columns: ['actualite_id']),
    new ORM\Index(name: 'IDX_ACTUALITE_IMAGE_ORDRE', columns: ['actualite_id', 'ordre']),
])]
class ActualiteImage
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Actualite::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Actualite $actualite = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Media $media = null;

    #[ORM\Column(length: 1000)]
    private string $urlExterne;

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int { return $this->id; }
    public function getActualite(): ?Actualite { return $this->actualite; }
    public function setActualite(?Actualite $actualite): static { $this->actualite = $actualite; return $this; }
    public function getMedia(): ?Media { return $this->media; }
    public function setMedia(Media $media): static { $this->media = $media; return $this; }
    public function getUrlExterne(): string { return $this->urlExterne; }
    public function setUrlExterne(string $urlExterne): static { $this->urlExterne = $urlExterne; return $this; }
    public function getOrdre(): int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
}
