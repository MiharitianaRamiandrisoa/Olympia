<?php

namespace App\Service;

use DateTimeImmutable;
use DateTimeZone;

final class ThemeResolver
{
    private const TIMEZONE = 'Indian/Antananarivo';

    /**
     * @param string $override auto, default or noel
     */
    public function __construct(private readonly string $override = 'auto')
    {
    }

    /**
     * @return array{key: string, label: string, color: string, seasonal: bool}
     */
    public function resolve(): array
    {
        $override = strtolower(trim($this->override));

        if ($override === 'default') {
            return $this->defaultTheme();
        }

        if ($override === 'noel') {
            return $this->christmasTheme();
        }

        $timezone = new DateTimeZone(self::TIMEZONE);
        $now = new DateTimeImmutable('now', $timezone);
        $year = (int) $now->format('Y');
        $startYear = $now->format('m-d') >= '11-15' ? $year : $year - 1;
        $start = new DateTimeImmutable($startYear.'-11-15 00:00:00', $timezone);
        $end = new DateTimeImmutable(($startYear + 1).'-01-10 23:59:59', $timezone);

        return $now >= $start && $now <= $end
            ? $this->christmasTheme()
            : $this->defaultTheme();
    }

    /** @return array{key: string, label: string, color: string, seasonal: bool} */
    private function defaultTheme(): array
    {
        return [
            'key' => 'default',
            'label' => 'Thème Olympia',
            'color' => '#154953',
            'seasonal' => false,
        ];
    }

    /** @return array{key: string, label: string, color: string, seasonal: bool} */
    private function christmasTheme(): array
    {
        return [
            'key' => 'noel',
            'label' => 'Noël à Olympia',
            'color' => '#154953',
            'seasonal' => true,
        ];
    }
}
