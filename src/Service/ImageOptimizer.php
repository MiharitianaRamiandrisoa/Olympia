<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

final class ImageOptimizer
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    /** @return array{filename: string, path: string, mime: string, size: int} */
    public function convertToWebp(UploadedFile $file): array
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            throw new \RuntimeException('La conversion WebP nécessite l’extension PHP GD. Activez extension=gd dans le php.ini utilisé par Apache.');
        }

        $directory = $this->kernel->getProjectDir().'/public/uploads/media';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le dossier de stockage des images.');
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getPathname()));
        if ($source === false) {
            throw new \RuntimeException('Le fichier envoyé n’est pas une image lisible par PHP GD.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $basename = bin2hex(random_bytes(16));
        $filename = $basename.'.webp';
        $path = $directory.'/'.$filename;
        foreach ([400, 800, 1200, 1600] as $requestedWidth) {
            $variantWidth = min($requestedWidth, $width);
            $variantHeight = max(1, (int) round($height * ($variantWidth / $width)));
            $variant = imagecreatetruecolor($variantWidth, $variantHeight);
            imagealphablending($variant, false);
            imagesavealpha($variant, true);
            imagecopyresampled($variant, $source, 0, 0, 0, 0, $variantWidth, $variantHeight, $width, $height);
            $variantPath = $directory.'/'.$basename.'-'.$requestedWidth.'.webp';
            if (!imagewebp($variant, $variantPath, 80)) {
                imagedestroy($source);
                imagedestroy($variant);
                throw new \RuntimeException('Impossible de créer une variante WebP.');
            }
            imagedestroy($variant);
            if ($requestedWidth === 1600) {
                copy($variantPath, $path);
            }
        }
        imagedestroy($source);

        return [
            'filename' => $filename,
            'path' => 'uploads/media/'.$filename,
            'mime' => 'image/webp',
            'size' => filesize($path) ?: 0,
        ];
    }
}
