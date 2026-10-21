<?php

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ImageExtension extends AbstractExtension
{
    private const VARIANT_WIDTHS = [400, 800, 1200, 1600];
    private const FALLBACK_IMAGE = 'images/Minimal Storefront Badge Illustration.png';

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Packages $assets,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('responsive_srcset', [$this, 'responsiveSrcset']),
            new TwigFunction('optimized_image', [$this, 'optimizedImage']),
        ];
    }

    public function responsiveSrcset(?string $path): string
    {
        if (!$path || $this->isExternal($path)) {
            return '';
        }

        $path = $this->resolveLocalPath($path);

        $srcset = [];
        foreach (self::VARIANT_WIDTHS as $width) {
            $variant = $this->variantPath($path, $width);
            if ($variant && is_file($this->kernel->getProjectDir().'/public/'.$variant)) {
                $srcset[] = $this->assets->getUrl($variant).' '.$width.'w';
            }
        }

        return implode(', ', $srcset);
    }

    public function optimizedImage(?string $path): string
    {
        if (!$path) {
            return '';
        }

        if ($this->isExternal($path)) {
            $path = self::FALLBACK_IMAGE;
        }

        $path = $this->resolveLocalPath($path);

        $variant = $this->variantPath($path, 1600);
        if ($variant && is_file($this->kernel->getProjectDir().'/public/'.$variant)) {
            $source = $this->kernel->getProjectDir().'/public/'.ltrim(str_replace('\\', '/', $path), '/');
            if (!is_file($source) || filesize($variant) < filesize($source)) {
                return $this->assets->getUrl($variant);
            }
        }

        return $this->assets->getUrl(ltrim(str_replace('\\', '/', $path), '/'));
    }

    private function resolveLocalPath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $source = $this->kernel->getProjectDir().'/public/'.$path;

        if (is_file($source)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension !== '') {
            $basePath = substr($path, 0, -strlen($extension) - 1);
            foreach (['webp', 'jpg', 'jpeg', 'png', 'JPG', 'PNG'] as $alternateExtension) {
                $alternatePath = $basePath.'.'.$alternateExtension;
                if (is_file($this->kernel->getProjectDir().'/public/'.$alternatePath)) {
                    return $alternatePath;
                }
            }
        }

        return self::FALLBACK_IMAGE;
    }

    private function variantPath(string $path, int $width): ?string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($path, 'uploads/media/') && str_ends_with(strtolower($path), '.webp')) {
            return dirname($path).'/'.pathinfo($path, PATHINFO_FILENAME).'-'.$width.'.webp';
        }

        if (!str_starts_with($path, 'images/') || !is_file($this->kernel->getProjectDir().'/public/'.$path)) {
            return null;
        }

        $relativePath = substr($path, strlen('images/'));
        $directory = pathinfo($relativePath, PATHINFO_DIRNAME);
        $basename = pathinfo($relativePath, PATHINFO_FILENAME);
        $prefix = 'images/optimized'.($directory !== '.' ? '/'.$directory : '');

        return $prefix.'/'.$basename.'-'.$width.'.webp';
    }

    private function isExternal(string $path): bool
    {
        return (bool) preg_match('#^(?:https?:)?//#i', $path);
    }
}
