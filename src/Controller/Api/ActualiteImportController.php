<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Actualite;
use App\Entity\ActualiteImage;
use App\Repository\ActualiteRepository;
use App\Service\FacebookActualiteImageImporter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ActualiteImportController extends AbstractController
{
    public function __construct(
        private readonly string $apiKey,
        private readonly SluggerInterface $slugger,
        private readonly FacebookActualiteImageImporter $imageImporter,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/integrations/make/actualites', name: 'api_make_actualites_import', methods: ['POST'])]
    public function import(
        Request $request,
        ActualiteRepository $actualiteRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        if ($this->apiKey === '' || !hash_equals($this->apiKey, trim((string) $request->headers->get('X-Actualite-Api-Key')))) {
            return $this->json(['success' => false, 'message' => 'Clé API invalide.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['success' => false, 'message' => 'Le corps de la requête doit être un JSON valide.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($payload) || array_is_list($payload)) {
            return $this->json(['success' => false, 'message' => 'Le JSON doit contenir un objet de publication.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $externalId = $this->requiredText($payload, ['external_id', 'post_id', 'facebook_post_id', 'id'], 'external_id');
            $message = $this->optionalText($payload, ['content', 'message', 'text', 'description']) ?? '';
            $title = $this->optionalText($payload, ['title', 'titre']) ?? $this->titleFromContent($message);

            if ($title === '') {
                throw new \InvalidArgumentException('Le champ title ou content est obligatoire.');
            }

            $category = $this->optionalText($payload, ['category', 'categorie']) ?: 'Facebook';
            $excerpt = $this->optionalText($payload, ['excerpt', 'chapeau', 'summary']) ?? $this->excerptFromContent($message);
            $publishedAt = $this->parseDate($this->optionalText($payload, ['published_at', 'date_publication', 'created_time']));
            $externalUrl = $this->optionalUrl($payload, ['permalink_url', 'facebook_url', 'url']);
            $imageUrls = $this->imageUrlsFromPayload($payload);
            $active = $this->optionalBoolean($payload, ['active', 'est_actif'], true);
            $featured = $this->optionalBoolean($payload, ['featured', 'est_mis_en_avant'], false);

            $actualite = $actualiteRepository->findOneBy([
                'origine' => 'facebook',
                'identifiantExterne' => $externalId,
            ]);
            $action = $actualite instanceof Actualite ? 'updated' : 'created';

            if (!$actualite instanceof Actualite) {
                $actualite = (new Actualite())
                    ->setOrigine('facebook')
                    ->setIdentifiantExterne($externalId)
                    ->setSlug($this->buildSlug($title, $externalId));
                $entityManager->persist($actualite);
            }

            $actualite
                ->setTitre($this->truncate($title, 200))
                ->setCategorie($this->truncate($category, 100))
                ->setChapeau($excerpt !== '' ? $this->truncate($excerpt, 255) : null)
                ->setContenu($message !== '' ? $message : null)
                ->setDatePublication($publishedAt)
                ->setEstActif($active)
                ->setEstMisEnAvant($featured)
                ->setUrlExterne($externalUrl)
                ->setDateModification(new \DateTimeImmutable());

            if ($imageUrls !== null) {
                $this->synchronizeImages($actualite, $imageUrls, $externalId, $entityManager);
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'action' => $action,
                'id' => $actualite->getId(),
                'external_id' => $actualite->getIdentifiantExterne(),
                'slug' => $actualite->getSlug(),
                'url' => '/actualites/'.$actualite->getSlug(),
            ], $action === 'created' ? Response::HTTP_CREATED : Response::HTTP_OK);
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->logger->warning('Import Make d’une actualité refusé.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->json(['success' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Import Make d’une actualité impossible.', [
                'exception' => $exception,
            ]);

            return $this->json(['success' => false, 'message' => 'L’actualité n’a pas pu être enregistrée.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /** @param array<string, mixed> $payload */
    private function requiredText(array $payload, array $keys, string $fieldName): string
    {
        $value = $this->optionalText($payload, $keys);
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException(sprintf('Le champ %s est obligatoire.', $fieldName));
        }

        return $this->truncate($value, 255);
    }

    /** @param array<string, mixed> $payload */
    private function optionalText(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload) || $payload[$key] === null) {
                continue;
            }
            if (!is_scalar($payload[$key])) {
                throw new \InvalidArgumentException(sprintf('Le champ %s doit être textuel.', $key));
            }

            $value = trim(strip_tags((string) $payload[$key]));
            return $value === '' ? null : $value;
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function optionalUrl(array $payload, array $keys): ?string
    {
        $value = $this->optionalText($payload, $keys);
        if ($value === null) {
            return null;
        }

        return $this->normalizeUrl($value);
    }

    /** @param array<string, mixed> $payload */
    /** @return list<string>|null */
    private function imageUrlsFromPayload(array $payload): ?array
    {
        $values = [];
        $hasImageField = false;

        foreach (['image_url', 'image', 'image_urls', 'images', 'attachments'] as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $hasImageField = true;
            $this->collectImageUrls($payload[$key], $values);
        }

        if (!$hasImageField) {
            return null;
        }

        $urls = [];
        foreach ($values as $value) {
            $url = $this->normalizeUrl($value);
            if (!in_array($url, $urls, true)) {
                $urls[] = $url;
            }
        }

        if (count($urls) > 10) {
            throw new \InvalidArgumentException('Une publication ne peut pas contenir plus de 10 images.');
        }

        return $urls;
    }

    /** @param list<string> $urls */
    private function collectImageUrls(mixed $value, array &$urls, int $depth = 0): void
    {
        if ($depth > 4 || $value === null) {
            return;
        }

        if (is_scalar($value)) {
            $text = trim((string) $value);
            if ($text === '') {
                return;
            }

            if (str_starts_with($text, '[') || str_starts_with($text, '{')) {
                try {
                    $decoded = json_decode($text, true, 20, JSON_THROW_ON_ERROR);
                    $this->collectImageUrls($decoded, $urls, $depth + 1);
                    return;
                } catch (\JsonException) {
                    // Le texte sera traité comme une URL et rejeté avec un message explicite.
                }
            }

            $urls[] = $text;
            return;
        }

        if (!is_array($value)) {
            return;
        }

        foreach (['url', 'image_url', 'src', 'full_picture', 'media_url'] as $key) {
            if (array_key_exists($key, $value)) {
                $this->collectImageUrls($value[$key], $urls, $depth + 1);
            }
        }

        foreach (['data', 'attachments', 'subattachments', 'images'] as $key) {
            if (array_key_exists($key, $value)) {
                $this->collectImageUrls($value[$key], $urls, $depth + 1);
            }
        }
    }

    private function normalizeUrl(string $value): string
    {
        $parts = parse_url($value);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
            throw new \InvalidArgumentException('Une URL fournie par Make est invalide.');
        }

        return $value;
    }

    /** @param list<string> $imageUrls */
    private function synchronizeImages(
        Actualite $actualite,
        array $imageUrls,
        string $externalId,
        EntityManagerInterface $entityManager,
    ): void {
        $existingByUrl = [];
        foreach ($actualite->getImages() as $image) {
            $existingByUrl[$image->getUrlExterne()] = $image;
        }

        foreach ($existingByUrl as $url => $image) {
            if (!in_array($url, $imageUrls, true)) {
                $actualite->removeImage($image);
            }
        }

        $primaryMedia = null;
        $primaryUrl = null;
        foreach ($imageUrls as $ordre => $url) {
            $image = $existingByUrl[$url] ?? null;

            // Reprend l’ancienne image principale des publications importées
            // avant l’ajout de la galerie, sans la télécharger une seconde fois.
            if (!$image && $url === $actualite->getUrlImageExterne() && $actualite->getImageMedia() !== null) {
                $image = (new ActualiteImage())
                    ->setMedia($actualite->getImageMedia())
                    ->setUrlExterne($url);
                $actualite->addImage($image);
                $entityManager->persist($image);
            }

            if (!$image) {
                $media = $this->imageImporter->import($url, $externalId.'-'.$ordre);
                $entityManager->persist($media);
                $image = (new ActualiteImage())
                    ->setMedia($media)
                    ->setUrlExterne($url);
                $actualite->addImage($image);
                $entityManager->persist($image);
            }

            $image->setOrdre($ordre);
            if ($ordre === 0) {
                $primaryMedia = $image->getMedia();
                $primaryUrl = $url;
            }
        }

        $actualite
            ->setImageMedia($primaryMedia)
            ->setUrlImageExterne($primaryUrl);
    }

    private function parseDate(?string $value): \DateTimeImmutable
    {
        if ($value === null) {
            return new \DateTimeImmutable();
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new \InvalidArgumentException('La date de publication est invalide.');
        }
    }

    /** @param array<string, mixed> $payload */
    private function optionalBoolean(array $payload, array $keys, bool $default): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = filter_var($payload[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) {
                throw new \InvalidArgumentException(sprintf('Le champ %s doit être booléen.', $key));
            }

            return $value;
        }

        return $default;
    }

    private function titleFromContent(string $content): string
    {
        foreach (preg_split('/\R/u', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                return $line;
            }
        }

        return '';
    }

    private function excerptFromContent(string $content): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $content));
    }

    private function buildSlug(string $title, string $externalId): string
    {
        $base = $this->slugger->slug($title)->lower()->toString();
        $base = $this->truncate($base !== '' ? $base : 'publication', 180);

        return 'facebook-'.$base.'-'.substr(hash('sha256', $externalId), 0, 10);
    }

    private function truncate(string $value, int $length): string
    {
        return mb_substr(trim($value), 0, $length);
    }
}
