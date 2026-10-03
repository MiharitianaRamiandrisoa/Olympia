<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/** Temporary form fields used to create/update an Enseigne with its location. */
trait EnseigneFormDataTrait
{
    private ?string $enseigneNom = null;
    private ?string $enseigneDescription = null;
    private ?string $enseigneTelephone = null;
    private ?string $enseigneEmail = null;
    private ?string $enseigneSiteWeb = null;
    private ?string $enseigneFacebook = null;
    private ?string $enseigneInstagram = null;
    private ?bool $enseigneEstActif = null;
    private ?Media $enseigneLogoMedia = null;
    private ?UploadedFile $enseigneLogoUpload = null;

    public function getEnseigneNom(): ?string { return $this->enseigneNom ?? $this->getEnseigne()?->getNom(); }
    public function setEnseigneNom(?string $value): static { $this->enseigneNom = $value; return $this; }
    public function getEnseigneDescription(): ?string { return $this->enseigneDescription ?? $this->getEnseigne()?->getDescription(); }
    public function setEnseigneDescription(?string $value): static { $this->enseigneDescription = $value; return $this; }
    public function getEnseigneTelephone(): ?string { return $this->enseigneTelephone ?? $this->getEnseigne()?->getTelephone(); }
    public function setEnseigneTelephone(?string $value): static { $this->enseigneTelephone = $value; return $this; }
    public function getEnseigneEmail(): ?string { return $this->enseigneEmail ?? $this->getEnseigne()?->getEmail(); }
    public function setEnseigneEmail(?string $value): static { $this->enseigneEmail = $value; return $this; }
    public function getEnseigneSiteWeb(): ?string { return $this->enseigneSiteWeb ?? $this->getEnseigne()?->getSiteWeb(); }
    public function setEnseigneSiteWeb(?string $value): static { $this->enseigneSiteWeb = $value; return $this; }
    public function getEnseigneFacebook(): ?string { return $this->enseigneFacebook ?? $this->getEnseigne()?->getFacebook(); }
    public function setEnseigneFacebook(?string $value): static { $this->enseigneFacebook = $value; return $this; }
    public function getEnseigneInstagram(): ?string { return $this->enseigneInstagram ?? $this->getEnseigne()?->getInstagram(); }
    public function setEnseigneInstagram(?string $value): static { $this->enseigneInstagram = $value; return $this; }
    public function isEnseigneEstActif(): bool { return $this->enseigneEstActif ?? $this->getEnseigne()?->isEstActif() ?? true; }
    public function setEnseigneEstActif(bool $value): static { $this->enseigneEstActif = $value; return $this; }
    public function getEnseigneLogoMedia(): ?Media { return $this->enseigneLogoMedia ?? $this->getEnseigne()?->getLogoMedia(); }
    public function setEnseigneLogoMedia(?Media $value): static { $this->enseigneLogoMedia = $value; return $this; }
    public function getEnseigneLogoUpload(): ?UploadedFile { return $this->enseigneLogoUpload; }
    public function setEnseigneLogoUpload(?UploadedFile $value): static { $this->enseigneLogoUpload = $value; return $this; }
}
