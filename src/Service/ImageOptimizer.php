<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

final class ImageOptimizer
{
    public const VARIANT_WIDTHS = [400, 800, 1200, 1600];
    private const MASTER_WIDTH = 1600;
    private const WEBP_QUALITY = 82;

    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    /** @return array{filename: string, path: string, mime: string, size: int} */
    public function convertToWebp(UploadedFile $file): array
    {
        $this->assertWebpSupport();

        $directory = $this->kernel->getProjectDir().'/public/uploads/media';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le dossier de stockage des images.');
        }

        $basename = bin2hex(random_bytes(16));
        $masterPath = $this->writeVariants($file->getPathname(), $directory, $basename);
        $filename = $basename.'.webp';

        return [
            'filename' => $filename,
            'path' => 'uploads/media/'.$filename,
            'mime' => 'image/webp',
            'size' => filesize($masterPath) ?: 0,
        ];
    }

    /**
     * Creates WebP variants for an existing image under public/images.
     * The source file is deliberately kept untouched.
     *
     * @return array{skipped: bool, sourceBytes: int, masterBytes: int, masterPath: string}
     */
    public function optimizeStaticImage(string $relativePath, bool $force = false): array
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (!str_starts_with($relativePath, 'images/')) {
            throw new \InvalidArgumentException('Une image statique doit se trouver dans public/images.');
        }

        $sourcePath = $this->kernel->getProjectDir().'/public/'.$relativePath;
        if (!is_file($sourcePath)) {
            throw new \InvalidArgumentException(sprintf('Image introuvable : %s', $relativePath));
        }

        $sourceRelativePath = substr($relativePath, strlen('images/'));
        $sourceDirectory = pathinfo($sourceRelativePath, PATHINFO_DIRNAME);
        $sourceBasename = pathinfo($sourceRelativePath, PATHINFO_FILENAME);
        $optimizedDirectory = $this->kernel->getProjectDir().'/public/images/optimized';
        if ($sourceDirectory !== '.' && $sourceDirectory !== '') {
            $optimizedDirectory .= '/'.$sourceDirectory;
        }
        $masterPath = $optimizedDirectory.'/'.$sourceBasename.'-'.self::MASTER_WIDTH.'.webp';

        if (!$force && is_file($masterPath) && filemtime($masterPath) >= filemtime($sourcePath)) {
            return [
                'skipped' => true,
                'sourceBytes' => (int) filesize($sourcePath),
                'masterBytes' => (int) filesize($masterPath),
                'masterPath' => $masterPath,
            ];
        }

        if (!is_dir($optimizedDirectory) && !mkdir($optimizedDirectory, 0775, true) && !is_dir($optimizedDirectory)) {
            throw new \RuntimeException('Impossible de créer le dossier des images optimisées.');
        }

        $writtenMasterPath = $this->writeVariants($sourcePath, $optimizedDirectory, $sourceBasename, false);

        return [
            'skipped' => false,
            'sourceBytes' => (int) filesize($sourcePath),
            'masterBytes' => (int) filesize($writtenMasterPath),
            'masterPath' => $writtenMasterPath,
        ];
    }

    private function assertWebpSupport(): void
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            throw new \RuntimeException('La conversion WebP nécessite l’extension PHP GD. Activez extension=gd dans le php.ini utilisé par Apache.');
        }
    }

    private function writeVariants(string $sourcePath, string $directory, string $basename, bool $writeCanonical = true): string
    {
        $this->assertWebpSupport();
        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if ($source === false) {
            throw new \RuntimeException('Le fichier envoyé n’est pas une image lisible par PHP GD.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        if ($width < 1 || $height < 1) {
            imagedestroy($source);
            throw new \RuntimeException('Les dimensions de l’image sont invalides.');
        }

        try {
            foreach (self::VARIANT_WIDTHS as $requestedWidth) {
                $variantWidth = min($requestedWidth, $width);
                $variantHeight = max(1, (int) round($height * ($variantWidth / $width)));
                $variant = imagecreatetruecolor($variantWidth, $variantHeight);
                imagealphablending($variant, false);
                imagesavealpha($variant, true);
                $transparent = imagecolorallocatealpha($variant, 0, 0, 0, 127);
                imagefilledrectangle($variant, 0, 0, $variantWidth, $variantHeight, $transparent);
                imagecopyresampled($variant, $source, 0, 0, 0, 0, $variantWidth, $variantHeight, $width, $height);

                $variantPath = $directory.'/'.$basename.'-'.$requestedWidth.'.webp';
                if (!imagewebp($variant, $variantPath, self::WEBP_QUALITY)) {
                    imagedestroy($variant);
                    throw new \RuntimeException('Impossible de créer une variante WebP.');
                }
                imagedestroy($variant);
            }
        } finally {
            imagedestroy($source);
        }

        $masterPath = $directory.'/'.$basename.'-'.self::MASTER_WIDTH.'.webp';
        if (!$writeCanonical) {
            return $masterPath;
        }

        $canonicalPath = $directory.'/'.$basename.'.webp';
        if (!copy($masterPath, $canonicalPath)) {
            throw new \RuntimeException('Impossible de créer l’image WebP principale.');
        }

        return $canonicalPath;
    }
}
