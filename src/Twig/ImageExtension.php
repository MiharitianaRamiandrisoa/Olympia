<?php

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ImageExtension extends AbstractExtension
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Packages $assets,
    ) {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('responsive_srcset', [$this, 'responsiveSrcset'])];
    }

    public function responsiveSrcset(?string $path): string
    {
        if (!$path || !str_starts_with($path, 'uploads/media/') || !str_ends_with($path, '.webp')) {
            return '';
        }

        $relativeDirectory = dirname($path);
        $basename = pathinfo($path, PATHINFO_FILENAME);
        $srcset = [];
        foreach ([400, 800, 1200, 1600] as $width) {
            $variant = $relativeDirectory.'/'.$basename.'-'.$width.'.webp';
            if (is_file($this->kernel->getProjectDir().'/public/'.$variant)) {
                $srcset[] = $this->assets->getUrl($variant).' '.$width.'w';
            }
        }

        return implode(', ', $srcset);
    }
}
