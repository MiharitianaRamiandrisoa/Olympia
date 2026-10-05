<?php

namespace App\Controller;

use App\Repository\BoutiqueRepository;
use App\Repository\EvenementRepository;
use App\Repository\ServiceRepository;
use App\Repository\InformationPratiqueRepository;
use App\Repository\PromotionRepository;
use App\Repository\ActualiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly BoutiqueRepository $boutiqueRepository,
        private readonly PromotionRepository $promotionRepository,
        private readonly ActualiteRepository $actualiteRepository,
        private readonly EvenementRepository $evenementRepository,
        private readonly ServiceRepository $serviceRepository,
        private readonly InformationPratiqueRepository $informationPratiqueRepository,
    )
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $featuredShops = $this->boutiqueRepository->findFeatured();
        if ($featuredShops === []) {
            $featuredShops = array_slice($this->boutiqueRepository->findActive(), 0, 5);
        }

        $shops = array_map(static fn ($boutique) => [
            'name' => $boutique->getEnseigne()?->getNom() ?? 'Enseigne',
            'category' => $boutique->getCategorie()?->getNom() ?? 'Boutique',
            'image' => $boutique->getPhotoMedia()?->getChemin() ?? 'images/shops/default.jpg',
            'href' => '/boutiques/'.($boutique->getEnseigne()?->getSlug() ?? ''),
        ], $featuredShops);

        $promotions = array_map(static fn ($promotion) => [
            'badge' => 'Promotion',
            'title' => $promotion->getTitre(),
            'icon' => 'calendar',
            'date' => $promotion->getDateDebut()?->format('d/m/Y'),
            'text' => $promotion->getDescription() ?? '',
            'image' => $promotion->getImageMedia()?->getChemin() ?? 'images/promo/default.jpg',
            'href' => '/promotions/'.$promotion->getSlug(),
        ], $this->promotionRepository->findFeatured());

        $events = array_map(static fn ($event) => [
            'badge' => 'Evenement',
            'title' => $event->getTitre(),
            'icon' => 'calendar',
            'date' => $event->getDateDebut()?->format('d/m/Y H:i'),
            'text' => $event->getDescription() ?? '',
            'image' => $event->getImageMedia()?->getChemin() ?? 'images/news/default.jpg',
            'href' => '/evenements/'.$event->getSlug(),
        ], $this->evenementRepository->findFeatured());

        $actualites = array_map(static fn ($actualite) => [
            'badge' => $actualite->getCategorie() ?: 'Actualité',
            'title' => $actualite->getTitre(),
            'icon' => 'newspaper',
            'date' => $actualite->getDatePublication()?->format('d/m/Y'),
            'text' => $actualite->getChapeau() ?: $actualite->getContenu() ?: '',
            'image' => $actualite->getImageMedia()?->getChemin() ?? 'images/news/default.jpg',
            'href' => '/actualites/'.$actualite->getSlug(),
        ], array_slice($this->actualiteRepository->findActive(), 0, 3));

        $services = array_map(static fn ($service) => [
            'name' => $service->getNom(),
            'description' => $service->getDescription() ?? '',
            'image' => $service->getPhotoMedia()?->getChemin(),
            'hours' => $service->getHoraires(),
            'featured' => $service->isEstMisEnAvant(),
            'slug' => $service->getSlug(),
            'href' => '/services#'.$service->getSlug(),
        ], $this->serviceRepository->findFeatured() ?: array_slice($this->serviceRepository->findActive(), 0, 4));

        $news = [];
        $max = max(count($actualites), count($promotions), count($events));
        for ($i = 0; $i < $max; ++$i) {
            if (isset($actualites[$i])) $news[] = $actualites[$i];
            if (isset($promotions[$i])) $news[] = $promotions[$i];
            if (isset($events[$i])) $news[] = $events[$i];
        }

        return $this->render('home/index.html.twig', [
            'practical_info' => $this->informationPratiqueRepository->findActiveOrdered(),
            'center_address' => $this->informationPratiqueRepository->findActiveAddress(),
            'quick_access' => [
                ['icon' => 'shopping-bag', 'title' => 'Boutiques', 'text' => 'Pret-a-porter, beaute, tech et accessoires.', 'href' => '/boutiques'],
                ['icon' => 'coffee', 'title' => 'Food court', 'text' => 'Du fast-food aux specialites gastronomiques.', 'href' => '/restaurants'],
                ['icon' => 'calendar', 'title' => 'Evenements', 'text' => 'Decouvrez nos animations et actualites.', 'href' => '/evenements'],
                ['icon' => 'shield', 'title' => 'Services', 'text' => 'Parking, Wi-Fi, espaces bebe et detente.', 'href' => '/services'],
            ],
            'shops' => $shops,
            'news' => $news,
            'services' => $services,
        ]);
    }
}
