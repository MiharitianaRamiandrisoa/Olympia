<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\CategorieEvenement;
use App\Entity\CategoriePromotion;
use App\Entity\Evenement;
use App\Entity\Promotion;
use App\Entity\Enseigne;
use App\Repository\CategorieEvenementRepository;
use App\Repository\CategoriePromotionRepository;
use App\Repository\EnseigneRepository;
use App\Repository\EvenementRepository;
use App\Repository\PromotionRepository;
use App\Service\FacebookActualiteImageImporter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class MakeEventPromotionImportController extends AbstractController
{
    public function __construct(
        private readonly string $evenementApiKey,
        private readonly string $promotionApiKey,
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
        private readonly FacebookActualiteImageImporter $imageImporter,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/integrations/make/evenements', name: 'api_make_evenements_import', methods: ['POST'])]
    public function importEvenement(
        Request $request,
        EvenementRepository $evenementRepository,
        CategorieEvenementRepository $categorieRepository,
        EnseigneRepository $enseigneRepository,
    ): JsonResponse {
        $unauthorized = $this->authorize($request, $this->evenementApiKey, 'X-Evenement-Api-Key');
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        try {
            $payload = $this->decodePayload($request);
            $externalId = $this->requiredText($payload, ['external_id', 'event_id', 'facebook_event_id', 'id'], 'external_id');
            $existing = $evenementRepository->findOneBy([
                'origine' => 'make',
                'identifiantExterne' => $externalId,
            ]);

            if ($existing instanceof Evenement) {
                return $this->skippedResponse($existing, $externalId, '/evenements/');
            }

            $title = $this->requiredText($payload, ['title', 'titre', 'name'], 'title');
            $dateDebut = $this->requiredDate($payload, ['start_at', 'date_debut', 'start_date', 'date'], 'date_debut');
            $dateFin = $this->optionalDate($payload, ['end_at', 'date_fin', 'end_date', 'end']);
            if ($dateFin !== null && $dateFin <= $dateDebut) {
                throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début.');
            }

            $event = (new Evenement())
                ->setOrigine('make')
                ->setIdentifiantExterne($externalId)
                ->setTitre($this->truncate($title, 200))
                ->setSlug($this->buildSlug('evenement', $title, $externalId))
                ->setDescription($this->optionalText($payload, ['description', 'content', 'message', 'text']))
                ->setInformationsComplementaires($this->optionalText($payload, ['additional_info', 'informations_complementaires', 'details']))
                ->setDateDebut($dateDebut)
                ->setDateFin($dateFin)
                ->setLieu($this->optionalText($payload, ['location', 'lieu', 'place']))
                ->setEstActif($this->optionalBoolean($payload, ['active', 'est_actif'], true))
                ->setEstMisEnAvant($this->optionalBoolean($payload, ['featured', 'est_mis_en_avant'], false))
                ->setCategorie($this->resolveEventCategory($this->optionalText($payload, ['category_slug', 'category', 'categorie']), $categorieRepository))
                ->setEnseigne($this->resolveEnseigne(
                    $this->optionalText($payload, ['enseigne_id', 'enseigne_slug', 'brand_slug', 'shop_slug', 'enseigne', 'brand', 'shop']),
                    $enseigneRepository,
                    false,
                ));

            $this->attachImage($event, $payload, $externalId);
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $this->createdResponse($event, $externalId, '/evenements/');
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->logger->warning('Import Make d’un événement refusé.', ['message' => $exception->getMessage()]);

            return $this->json(['success' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Import Make d’un événement impossible.', ['exception' => $exception]);

            return $this->json(['success' => false, 'message' => 'L’événement n’a pas pu être enregistré.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/integrations/make/promotions', name: 'api_make_promotions_import', methods: ['POST'])]
    public function importPromotion(
        Request $request,
        PromotionRepository $promotionRepository,
        CategoriePromotionRepository $categorieRepository,
        EnseigneRepository $enseigneRepository,
    ): JsonResponse {
        $unauthorized = $this->authorize($request, $this->promotionApiKey, 'X-Promotion-Api-Key');
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        try {
            $payload = $this->decodePayload($request);
            $externalId = $this->requiredText($payload, ['external_id', 'promotion_id', 'offer_id', 'id'], 'external_id');
            $existing = $promotionRepository->findOneBy([
                'origine' => 'make',
                'identifiantExterne' => $externalId,
            ]);

            if ($existing instanceof Promotion) {
                return $this->skippedResponse($existing, $externalId, '/promotions/');
            }

            $title = $this->requiredText($payload, ['title', 'titre', 'name'], 'title');
            $dateDebut = $this->requiredDate($payload, ['start_at', 'date_debut', 'start_date', 'date'], 'date_debut');
            $dateFin = $this->optionalDate($payload, ['end_at', 'date_fin', 'end_date', 'end']);
            if ($dateFin !== null && $dateFin < $dateDebut) {
                throw new \InvalidArgumentException('La date de fin doit être postérieure ou égale à la date de début.');
            }

            $promotion = (new Promotion())
                ->setOrigine('make')
                ->setIdentifiantExterne($externalId)
                ->setTitre($this->truncate($title, 200))
                ->setSlug($this->buildSlug('promotion', $title, $externalId))
                ->setDescription($this->optionalText($payload, ['description', 'content', 'message', 'text']))
                ->setConditions($this->optionalText($payload, ['conditions', 'terms', 'offer_details']))
                ->setDateDebut($dateDebut)
                ->setDateFin($dateFin)
                ->setEstActif($this->optionalBoolean($payload, ['active', 'est_actif'], true))
                ->setEstMisEnAvant($this->optionalBoolean($payload, ['featured', 'est_mis_en_avant'], false))
                ->setCategorie($this->resolvePromotionCategory($this->optionalText($payload, ['category_slug', 'category', 'categorie']), $categorieRepository))
                ->setEnseigne($this->resolveEnseigne(
                    $this->optionalText($payload, ['enseigne_id', 'enseigne_slug', 'brand_slug', 'shop_slug', 'enseigne', 'brand', 'shop']),
                    $enseigneRepository,
                    true,
                ));

            $this->attachImage($promotion, $payload, $externalId);
            $this->entityManager->persist($promotion);
            $this->entityManager->flush();

            return $this->createdResponse($promotion, $externalId, '/promotions/');
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            $this->logger->warning('Import Make d’une promotion refusé.', ['message' => $exception->getMessage()]);

            return $this->json(['success' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Import Make d’une promotion impossible.', ['exception' => $exception]);

            return $this->json(['success' => false, 'message' => 'La promotion n’a pas pu être enregistrée.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function authorize(Request $request, string $apiKey, string $header): ?JsonResponse
    {
        if ($apiKey === '' || !hash_equals($apiKey, trim((string) $request->headers->get($header)))) {
            return $this->json(['success' => false, 'message' => 'Clé API invalide.'], Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function decodePayload(Request $request): array
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \InvalidArgumentException('Le corps de la requête doit être un JSON valide.');
        }

        if (!is_array($payload) || array_is_list($payload)) {
            throw new \InvalidArgumentException('Le JSON doit contenir un objet de publication.');
        }

        return $payload;
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

    /** @param array<string, mixed> $payload */
    private function requiredDate(array $payload, array $keys, string $fieldName): \DateTimeImmutable
    {
        $value = $this->optionalText($payload, $keys);
        if ($value === null) {
            throw new \InvalidArgumentException(sprintf('Le champ %s est obligatoire.', $fieldName));
        }

        return $this->parseDate($value, $fieldName);
    }

    /** @param array<string, mixed> $payload */
    private function optionalDate(array $payload, array $keys): ?\DateTimeImmutable
    {
        $value = $this->optionalText($payload, $keys);

        return $value === null ? null : $this->parseDate($value, 'date');
    }

    private function parseDate(string $value, string $fieldName): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new \InvalidArgumentException(sprintf('La date %s est invalide.', $fieldName));
        }
    }

    /** @param array<string, mixed> $payload */
    private function optionalUrl(array $payload, array $keys): ?string
    {
        $value = $this->optionalText($payload, $keys);
        if ($value === null) {
            return null;
        }

        $parts = parse_url($value);
        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
            throw new \InvalidArgumentException('Une URL fournie par Make est invalide.');
        }

        return $value;
    }

    private function resolveEnseigne(?string $value, EnseigneRepository $repository, bool $required): ?Enseigne
    {
        if ($value === null) {
            if ($required) {
                throw new \InvalidArgumentException('Le champ enseigne est obligatoire pour une promotion.');
            }

            return null;
        }

        $enseigne = ctype_digit($value)
            ? $repository->find((int) $value)
            : $repository->findOneBy(['slug' => $this->slugger->slug($value)->lower()->toString()]);
        $enseigne ??= $repository->findOneBy(['nom' => $value]);

        if (!$enseigne instanceof Enseigne) {
            throw new \InvalidArgumentException(sprintf('Enseigne introuvable : %s.', $value));
        }

        return $enseigne;
    }

    private function resolveEventCategory(?string $value, CategorieEvenementRepository $repository): ?CategorieEvenement
    {
        if ($value === null) {
            return null;
        }

        $slug = $this->slugger->slug($value)->lower()->toString();
        $category = ctype_digit($value) ? $repository->find((int) $value) : $repository->findOneBy(['slug' => $slug]);
        if ($category instanceof CategorieEvenement) {
            return $category;
        }

        $category = (new CategorieEvenement())
            ->setNom($this->truncate($value, 150))
            ->setSlug($this->truncate($slug !== '' ? $slug : 'categorie', 180));
        $this->entityManager->persist($category);

        return $category;
    }

    private function resolvePromotionCategory(?string $value, CategoriePromotionRepository $repository): ?CategoriePromotion
    {
        if ($value === null) {
            return null;
        }

        $slug = $this->slugger->slug($value)->lower()->toString();
        $category = ctype_digit($value) ? $repository->find((int) $value) : $repository->findOneBy(['slug' => $slug]);
        if ($category instanceof CategoriePromotion) {
            return $category;
        }

        $category = (new CategoriePromotion())
            ->setNom($this->truncate($value, 150))
            ->setSlug($this->truncate($slug !== '' ? $slug : 'categorie', 180));
        $this->entityManager->persist($category);

        return $category;
    }

    private function attachImage(Evenement|Promotion $entity, array $payload, string $externalId): void
    {
        $imageUrl = $this->optionalUrl($payload, ['image_url', 'cover_image_url', 'image']);
        if ($imageUrl === null) {
            return;
        }

        $media = $this->imageImporter->import($imageUrl, $externalId);
        $this->entityManager->persist($media);
        $entity->setImageMedia($media);
    }

    private function buildSlug(string $prefix, string $title, string $externalId): string
    {
        $base = $this->slugger->slug($title)->lower()->toString();
        $base = $this->truncate($base !== '' ? $base : $prefix, 190);

        return $this->truncate($prefix.'-'.$base.'-'.substr(hash('sha256', $externalId), 0, 10), 220);
    }

    private function truncate(string $value, int $length): string
    {
        return mb_substr(trim($value), 0, $length);
    }

    private function skippedResponse(Evenement|Promotion $entity, string $externalId, string $path): JsonResponse
    {
        return $this->json([
            'success' => true,
            'action' => 'skipped',
            'message' => 'Publication déjà présente. Aucune modification effectuée.',
            'id' => $entity->getId(),
            'external_id' => $externalId,
            'slug' => $entity->getSlug(),
            'url' => $path.$entity->getSlug(),
        ]);
    }

    private function createdResponse(Evenement|Promotion $entity, string $externalId, string $path): JsonResponse
    {
        return $this->json([
            'success' => true,
            'action' => 'created',
            'id' => $entity->getId(),
            'external_id' => $externalId,
            'slug' => $entity->getSlug(),
            'url' => $path.$entity->getSlug(),
        ], Response::HTTP_CREATED);
    }
}
