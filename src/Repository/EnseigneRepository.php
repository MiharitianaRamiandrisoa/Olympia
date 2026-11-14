<?php

namespace App\Repository;

use App\Entity\Enseigne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EnseigneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enseigne::class);
    }

    public function findFlexibleByName(string $value): ?Enseigne
    {
        $needle = $this->normalizeSearchText($value);
        $needleTokens = $this->searchTokens($needle);
        if ($needleTokens === []) {
            return null;
        }

        $bestMatch = null;
        $bestScore = 0.0;
        $bestCoverage = 0.0;

        foreach ($this->findAll() as $enseigne) {
            $name = $this->normalizeSearchText((string) $enseigne->getNom());
            $nameTokens = $this->searchTokens($name);
            if ($nameTokens === []) {
                continue;
            }

            $matchedTokens = count(array_intersect($needleTokens, $nameTokens));
            if ($matchedTokens === 0) {
                continue;
            }

            $score = $matchedTokens / count($needleTokens);
            $coverage = $matchedTokens / count($nameTokens);
            if ($score > $bestScore || ($score === $bestScore && $coverage > $bestCoverage)) {
                $bestMatch = $enseigne;
                $bestScore = $score;
                $bestCoverage = $coverage;
            }
        }

        return $bestMatch;
    }

    private function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($transliterated)) {
            $value = $transliterated;
        }

        return trim((string) preg_replace('/[^a-z0-9]+/i', ' ', $value));
    }

    /** @return list<string> */
    private function searchTokens(string $value): array
    {
        $ignoredWords = [
            'a', 'au', 'aux', 'chez', 'de', 'des', 'du', 'en', 'et', 'la', 'le', 'les', 'l', 'un', 'une',
            'and', 'the', 'boutique', 'enseigne', 'restaurant', 'shop', 'store', 'olympia',
        ];

        return array_values(array_filter(
            explode(' ', $value),
            static fn (string $token): bool => $token !== '' && !in_array($token, $ignoredWords, true),
        ));
    }
}
