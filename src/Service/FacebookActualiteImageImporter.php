<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Media;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FacebookActualiteImageImporter
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const ALLOWED_MIME_TYPES = [
        'image/avif',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];
    private const ALLOWED_HOST_SUFFIXES = [
        'facebook.com',
        'fbcdn.net',
        'fbsbx.com',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ImageOptimizer $imageOptimizer,
    ) {
    }

    public function import(string $url, string $postId): Media
    {
        $this->assertAllowedUrl($url);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'olympia_facebook_');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire pour l’image Facebook.');
        }

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'image/avif,image/webp,image/jpeg,image/png;q=0.9,*/*;q=0.1',
                ],
                'max_duration' => 20,
                // Les URLs sont fournies par Make : ne pas suivre une redirection
                // vers un domaine non contrôlé par Facebook.
                'max_redirects' => 0,
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Facebook n’a pas retourné l’image demandée.');
            }

            $headers = $response->getHeaders(false);
            $contentLength = (int) ($headers['content-length'][0] ?? 0);
            if ($contentLength > self::MAX_BYTES) {
                throw new \RuntimeException('L’image Facebook dépasse la taille maximale autorisée.');
            }

            $content = $response->getContent();
            if (strlen($content) > self::MAX_BYTES) {
                throw new \RuntimeException('L’image Facebook dépasse la taille maximale autorisée.');
            }

            if (file_put_contents($temporaryPath, $content) === false) {
                throw new \RuntimeException('Impossible d’enregistrer temporairement l’image Facebook.');
            }

            $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
            if (!is_string($mimeType) || !in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
                throw new \RuntimeException('Le fichier Facebook n’est pas une image autorisée.');
            }

            $dimensions = @getimagesize($temporaryPath);
            if ($dimensions === false) {
                throw new \RuntimeException('Les dimensions de l’image Facebook sont invalides.');
            }

            $uploadedFile = new UploadedFile(
                $temporaryPath,
                sprintf('facebook-%s.%s', $postId, $this->extensionForMimeType($mimeType)),
                $mimeType,
                null,
                true,
            );
            $optimized = $this->imageOptimizer->convertToWebp($uploadedFile);

            return (new Media())
                ->setNomOriginal($uploadedFile->getClientOriginalName())
                ->setNomFichier($optimized['filename'])
                ->setChemin($optimized['path'])
                ->setType('image')
                ->setTypeMime($optimized['mime'])
                ->setTaille((string) $optimized['size'])
                ->setLargeur((int) $dimensions[0])
                ->setHauteur((int) $dimensions[1])
                ->setTexteAlternatif('Publication Facebook Olympia')
                ->setEstActif(true)
                ->setDateCreation(new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        } finally {
            @unlink($temporaryPath);
        }
    }

    private function assertAllowedUrl(string $url): void
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new \InvalidArgumentException('L’URL de l’image Facebook est invalide.');
        }

        foreach (self::ALLOWED_HOST_SUFFIXES as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                return;
            }
        }

        throw new \InvalidArgumentException('L’image doit provenir d’un domaine Facebook autorisé.');
    }

    private function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/avif' => 'avif',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }
}
