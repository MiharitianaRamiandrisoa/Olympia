<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait PhotoUploadTrait
{
    private ?UploadedFile $photoUpload = null;

    public function getPhotoUpload(): ?UploadedFile
    {
        return $this->photoUpload;
    }

    public function setPhotoUpload(?UploadedFile $photoUpload): static
    {
        $this->photoUpload = $photoUpload;

        return $this;
    }
}
