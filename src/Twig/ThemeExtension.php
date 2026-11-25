<?php

namespace App\Twig;

use App\Service\ThemeResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ThemeExtension extends AbstractExtension
{
    public function __construct(private readonly ThemeResolver $themeResolver)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('olympia_theme', [$this->themeResolver, 'resolve']),
        ];
    }
}
