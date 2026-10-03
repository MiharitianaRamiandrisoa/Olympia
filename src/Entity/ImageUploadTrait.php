<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImageUploadTrait
{
    private ?UploadedFile $imageUpload = null;

    public function getImageUpload(): ?UploadedFile
    {
        return $this->imageUpload;
    }

    public function setImageUpload(?UploadedFile $imageUpload): static
    {
        $this->imageUpload = $imageUpload;

        return $this;
    }
}
