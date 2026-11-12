<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait GalleryUploadTrait
{
    /** @var list<UploadedFile> */
    private array $galleryUploads = [];

    /** @return list<UploadedFile> */
    public function getGalleryUploads(): array
    {
        return $this->galleryUploads;
    }

    /** @param list<UploadedFile> $galleryUploads */
    public function setGalleryUploads(array $galleryUploads): static
    {
        $this->galleryUploads = $galleryUploads;

        return $this;
    }
}
