<?php

namespace App\Tests\Unit;

use App\Service\ThemeResolver;
use PHPUnit\Framework\TestCase;

final class ThemeResolverTest extends TestCase
{
    public function testDefaultOverrideKeepsTheOriginalTheme(): void
    {
        $theme = (new ThemeResolver('default'))->resolve();

        self::assertSame('default', $theme['key']);
        self::assertFalse($theme['seasonal']);
        self::assertSame('#154953', $theme['color']);
    }

    public function testChristmasOverrideOnlyActivatesDecorations(): void
    {
        $theme = (new ThemeResolver('noel'))->resolve();

        self::assertSame('noel', $theme['key']);
        self::assertTrue($theme['seasonal']);
        self::assertSame('#154953', $theme['color']);
    }
}
