<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BoutiqueRepository;
use App\Repository\RestaurantRepository;
use App\Repository\PromotionRepository;
use App\Repository\ActualiteRepository;
use App\Repository\EvenementRepository;
use App\Repository\CategorieBoutiqueRepository;
use App\Repository\CategorieRestaurantRepository;
use App\Repository\CategoriePromotionRepository;
use App\Repository\CategorieEvenementRepository;
use App\Repository\ServiceRepository;
use App\Repository\OeuvreRepository;
use App\Repository\ArtisteRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Boutique;
use App\Entity\Restaurant;
use App\Entity\MessageContact;
use Doctrine\ORM\EntityManagerInterface;

class SiteController extends AbstractController
{
    // #[Route('/', name: 'app_home')]
    // public function home(): Response
    // {
    //     return $this->redirectToRoute('app_enseignes'); // page d'accueil à créer
    // }

    #[Route('/enseignes', name: 'app_enseignes', methods: ['GET'])]
    public function boutiques(Request $request, BoutiqueRepository $boutiqueRepository, CategorieBoutiqueRepository $categorieRepository): Response
    {
        $search = $request->query->get('q');
        $category = $request->query->get('category');
        $sort = $request->query->get('sort', 'all');
        $categories = $categorieRepository->findBy(['estActif' => true], ['nom' => 'ASC']);
        $shops = array_map(static fn ($boutique) => [
            'name' => $boutique->getEnseigne()?->getNom() ?? 'Enseigne',
            'category' => $boutique->getCategorie()?->getNom() ?? 'Boutique',
            'floor' => 'Olympia',
            'image' => $boutique->getPhotoMedia()?->getChemin() ?? 'images/Minimal Storefront Badge Illustration.png',
            'url' => '/enseignes/'.($boutique->getEnseigne()?->getSlug() ?? ''),
        ], $boutiqueRepository->findActive($search, $category, $sort));
        $pagination = $this->paginate($shops, $request);
        /*
        $shops = [
            ['name' => 'ZARA', 'category' => 'Mode', 'floor' => 'R+1', 'image' => 'images/shops/zara.jpg'],
            ['name' => 'H&M', 'category' => 'Mode', 'floor' => 'R+1', 'image' => 'images/shops/hm.jpg'],
            ['name' => 'LC Waikiki', 'category' => 'Mode', 'floor' => 'R+1', 'image' => 'images/shops/lcw.jpg'],
            ['name' => 'Bata', 'category' => 'Chaussures', 'floor' => 'RDC', 'image' => 'images/shops/bata.jpg'],
            ['name' => 'Jumbo', 'category' => 'Alimentation', 'floor' => 'RDC', 'image' => 'images/shops/jumbo.jpg'],
            ['name' => 'C&A', 'category' => 'Mode', 'floor' => 'R+1', 'image' => 'images/shops/ca.jpg'],
            ['name' => 'Watsons', 'category' => 'Beauté & Santé', 'floor' => 'R+1', 'image' => 'images/shops/watsons.jpg'],
            ['name' => 'Orange', 'category' => 'High-Tech', 'floor' => 'RDC', 'image' => 'images/shops/orange.jpg'],
        ]; */

        return $this->render('boutique/index.html.twig', [
            'shops' => $pagination['items'],
            'pagination' => $pagination,
            'categories' => $categories,
            'active_category' => $category,
            'active_sort' => $sort,
            'search' => $search,
        ]);
    }

    #[Route('/boutiques', name: 'app_boutiques_legacy', methods: ['GET'])]
    public function boutiquesLegacy(Request $request): Response
    {
        return $this->redirectToRoute('app_enseignes', $request->query->all(), Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/restaurants', name: 'app_restaurants')]
    public function restaurants(Request $request, RestaurantRepository $restaurantRepository, CategorieRestaurantRepository $categorieRepository): Response
    {
        $search = $request->query->get('q');
        $category = $request->query->get('category');
        $categories = $categorieRepository->findBy(['estActif' => true], ['nom' => 'ASC']);
        $restaurants = array_map(static fn ($restaurant) => [
            'name' => $restaurant->getEnseigne()?->getNom() ?? 'Food court',
            'cuisine' => $restaurant->getCategorie()?->getNom() ?? 'Cuisine',
            'floor' => 'Olympia',
            'image' => $restaurant->getPhotoMedia()?->getChemin() ?? 'images/Minimal Storefront Badge Illustration.png',
            'url' => '/restaurants/'.($restaurant->getEnseigne()?->getSlug() ?? ''),
        ], $restaurantRepository->findActive($search, $category));
        $pagination = $this->paginate($restaurants, $request);
        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $pagination['items'],
            'pagination' => $pagination,
            'categories' => $categories,
            'active_category' => $category,
        ]);

        /*
        $restaurants = [
            ['name' => 'Le Jardin', 'cuisine' => 'Cuisine locale', 'floor' => 'RDC', 'image' => 'images/resto/jardin.jpg'],
            ['name' => 'Pizza & Co', 'cuisine' => 'Italienne', 'floor' => 'R+1', 'image' => 'images/resto/pizza.jpg'],
            ['name' => 'Sushi Time', 'cuisine' => 'Japonaise', 'floor' => 'R+1', 'image' => 'images/resto/sushi.jpg'],
            ['name' => 'La Table', 'cuisine' => 'Française', 'floor' => 'RDC', 'image' => 'images/resto/table.jpg'],
            ['name' => 'Burger House', 'cuisine' => 'Fast Food', 'floor' => 'R+1', 'image' => 'images/resto/burger.jpg'],
            ['name' => 'Café des Amis', 'cuisine' => 'Café & Pâtisserie', 'floor' => 'RDC', 'image' => 'images/resto/cafe.jpg'],
            ['name' => 'Spice & Grill', 'cuisine' => 'Internationale', 'floor' => 'R+1', 'image' => 'images/resto/spice.jpg'],
            ['name' => 'Fresh Bowl', 'cuisine' => 'Healthy Food', 'floor' => 'RDC', 'image' => 'images/resto/bowl.jpg'],
        ];

        return $this->render('restaurant/index.html.twig', ['restaurants' => $restaurants]);
        */
    }

    #[Route('/enseignes/{slug}', name: 'app_enseigne_detail', methods: ['GET'])]
    public function boutiqueDetail(string $slug, BoutiqueRepository $boutiqueRepository): Response
    {
        $boutique = $boutiqueRepository->findActiveByEnseigneSlug($slug);
        if (!$boutique instanceof Boutique) {
            throw $this->createNotFoundException('Boutique introuvable.');
        }

        return $this->render('boutique/detail.html.twig', ['boutique' => $boutique]);
    }

    #[Route('/boutiques/{slug}', name: 'app_boutique_detail_legacy', methods: ['GET'])]
    public function boutiqueDetailLegacy(string $slug): Response
    {
        return $this->redirectToRoute('app_enseigne_detail', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/restaurants/{slug}', name: 'app_restaurant_detail', methods: ['GET'])]
    public function restaurantDetail(string $slug, RestaurantRepository $restaurantRepository): Response
    {
        $restaurant = $restaurantRepository->findActiveByEnseigneSlug($slug);
        if (!$restaurant instanceof Restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        return $this->render('restaurant/detail.html.twig', ['restaurant' => $restaurant]);
    }

    #[Route('/promotions', name: 'app_promotions')]
    public function promotions(Request $request, PromotionRepository $promotionRepository, CategoriePromotionRepository $categorieRepository): Response
    {
        $category = $request->query->get('category');
        $categories = $categorieRepository->findBy(['estActif' => true], ['nom' => 'ASC']);
        $promotions = array_map(static fn ($promotion) => [
            'tag' => 'Promotion',
            'title' => $promotion->getTitre(),
            'date' => $promotion->getDateDebut()?->format('d/m/Y'),
            'shop' => $promotion->getEnseigne()?->getNom() ?? 'Olympia',
            'description' => $promotion->getDescription() ?? '',
            'image' => $promotion->getImageMedia()?->getChemin() ?? 'images/Minimal Storefront Badge Illustration.png',
            'url' => '/promotions/'.$promotion->getSlug(),
        ], $promotionRepository->findActive($category));
        $pagination = $this->paginate($promotions, $request);
        return $this->render('promotion/index.html.twig', ['promotions' => $pagination['items'], 'pagination' => $pagination, 'categories' => $categories, 'active_category' => $category]);

        /*
        $promotions = [
            ['tag' => 'Restauration', 'title' => 'MENU SPÉCIAL', 'date' => 'Du 5 au 18 Mai 2025', 'shop' => 'Gastro',
             'description' => "Découvrez une formule spéciale pour profiter d'un repas complet à prix avantageux.", 'image' => 'images/promo/menu.jpg'],
            ['tag' => 'Mode', 'title' => "Jusqu'à -50%", 'date' => 'Du 5 au 18 Mai 2025', 'shop' => 'Zara',
             'description' => 'Profitez de réductions exceptionnelles dans toutes vos boutiques préférées.', 'image' => 'images/promo/mode.jpg'],
            ['tag' => 'Nouveauté', 'title' => 'Atelier créatif enfants', 'date' => '18 Mai 2025', 'shop' => 'Garderie',
             'description' => 'Des activités créatives encadrées pour vos enfants pendant votre séance shopping.', 'image' => 'images/promo/atelier.jpg'],
        ];

        return $this->render('promotion/index.html.twig', ['promotions' => $promotions]); */
    }

    #[Route('/evenements', name: 'app_events')]
    public function events(Request $request, EvenementRepository $evenementRepository, CategorieEvenementRepository $categorieRepository): Response
    {
        $category = $request->query->get('category');
        $categories = $categorieRepository->findBy([], ['nom' => 'ASC']);
        $events = array_map(static fn ($event) => [
            'badge' => 'Événement',
            'title' => $event->getTitre(),
            'icon' => 'calendar',
            'date' => $event->getDateDebut()?->format('d/m/Y H:i'),
            'start' => $event->getDateDebut()?->format(DATE_ATOM),
            'end' => $event->getDateFin()?->format(DATE_ATOM),
            'location' => $event->getLieu(),
            'text' => $event->getDescription() ?? '',
            'image' => $event->getImageMedia()?->getChemin() ?? 'images/Minimal Storefront Badge Illustration.png',
            'href' => '/evenements/'.$event->getSlug(),
        ], $evenementRepository->findActive($category));
        $pagination = $this->paginate($events, $request);
        return $this->render('evenement/index.html.twig', ['events' => $pagination['items'], 'pagination' => $pagination, 'categories' => $categories, 'active_category' => $category]);

    }

    #[Route('/actualites', name: 'app_actualites', methods: ['GET'])]
    public function actualites(Request $request, ActualiteRepository $actualiteRepository): Response
    {
        $search = trim((string) $request->query->get('q', ''));

        $articles = array_map(static fn ($article) => [
            'badge' => $article->getCategorie() ?: 'Actualité',
            'title' => $article->getTitre(),
            'date' => $article->getDatePublication()?->format('d/m/Y'),
            'text' => $article->getChapeau() ?: $article->getContenu() ?: '',
            'image' => $article->getImageMedia()?->getChemin() ?? 'images/Minimal Storefront Badge Illustration.png',
            'href' => '/actualites/'.$article->getSlug(),
        ], $actualiteRepository->findActive($search));
        $featured = $articles[0] ?? null;
        $pagination = $this->paginate(array_slice($articles, 1), $request);

        /*

        $promotions = array_map(static fn ($promotion) => [
            'type' => 'promotion',
            'badge' => $promotion->getCategorie()?->getNom() ?? 'Nouveautés',
            'title' => $promotion->getTitre(),
            'date' => $promotion->getDateDebut()?->format('d/m/Y'),
            'timestamp' => $promotion->getDateDebut()?->getTimestamp() ?? 0,
            'text' => $promotion->getDescription() ?? '',
            'image' => $promotion->getImageMedia()?->getChemin() ?? 'images/promo/default.jpg',
            'href' => '/promotions/'.$promotion->getSlug(),
        ], $promotionRepository->findActive());

        $events = array_map(static fn ($event) => [
            'type' => 'event',
            'badge' => 'Événement',
            'title' => $event->getTitre(),
            'date' => $event->getDateDebut()?->format('d/m/Y'),
            'timestamp' => $event->getDateDebut()?->getTimestamp() ?? 0,
            'text' => $event->getDescription() ?? '',
            'image' => $event->getImageMedia()?->getChemin() ?? 'images/news/default.jpg',
            'href' => '/evenements/'.$event->getSlug(),
        ], $evenementRepository->findActive());

        $articles = array_merge($promotions, $events);
        usort($articles, static fn (array $left, array $right) => $right['timestamp'] <=> $left['timestamp']);
        $articles = array_values(array_filter($articles, static function (array $article) use ($type, $search): bool {
            if ($type !== 'all' && $article['type'] !== $type) {
                return false;
            }
            if ($search === '') {
                return true;
            }
            return str_contains(mb_strtolower($article['title'].' '.$article['text'].' '.$article['badge']), mb_strtolower($search));
        }));

        */
        return $this->render('actualite/index.html.twig', [
            'articles' => $pagination['items'],
            'pagination' => $pagination,
            'featured' => $featured,
            'search' => $search,
        ]);
    }

    #[Route('/actualites/{slug}', name: 'app_actualite_detail', methods: ['GET'])]
    public function actualiteDetail(string $slug, ActualiteRepository $actualiteRepository): Response
    {
        $actualite = $actualiteRepository->findOneBy(['slug' => $slug, 'estActif' => true]);
        if (!$actualite instanceof \App\Entity\Actualite) {
            throw $this->createNotFoundException('Actualite introuvable.');
        }

        return $this->render('actualite/detail.html.twig', ['actualite' => $actualite]);
    }

    #[Route('/services', name: 'app_services', methods: ['GET'])]
    public function services(Request $request, ServiceRepository $serviceRepository): Response
    {
        $pagination = $this->paginate($serviceRepository->findActive(), $request);
        return $this->render('service/index.html.twig', [
            'services' => $pagination['items'],
            'pagination' => $pagination,
        ]);
    }

    #[Route('/galerie', name: 'app_galerie_virtuelle', methods: ['GET'])]
    public function galerieVirtuelle(OeuvreRepository $oeuvreRepository): Response
    {
        $works = array_map(static function (\App\Entity\Oeuvre $oeuvre): array {
            return [
                'title' => $oeuvre->getTitre() ?? 'Œuvre Olympia',
                'artist' => $oeuvre->getArtiste()?->getNom() ?? 'Artiste Olympia',
                'artistId' => $oeuvre->getArtiste()?->getId(),
                'category' => $oeuvre->getCategorie()?->getNom() ?? 'Art',
                'year' => $oeuvre->getAnnee() ?? (int) date('Y'),
                'medium' => $oeuvre->getTechnique() ?? 'Œuvre originale',
                'size' => $oeuvre->getDimensions() ?? '—',
                'ratio' => ($oeuvre->getImageMedia()?->getLargeur() && $oeuvre->getImageMedia()?->getHauteur())
                    ? sprintf('%d/%d', $oeuvre->getImageMedia()->getLargeur(), $oeuvre->getImageMedia()->getHauteur())
                    : '4/5',
                'image' => $oeuvre->getImageMedia()?->getChemin(),
            ];
        }, $oeuvreRepository->findPublished());

        return $this->render('galerie/index.html.twig', ['works' => $works]);
    }

    #[Route('/galerie-virtuelle/artistes/{id}', name: 'app_galerie_artiste_detail', methods: ['GET'])]
    public function galerieArtisteDetail(int $id, ArtisteRepository $artisteRepository, OeuvreRepository $oeuvreRepository): Response
    {
        $artiste = $artisteRepository->findOneBy(['id' => $id, 'estActif' => true]);
        if (!$artiste) {
            throw $this->createNotFoundException('Artiste introuvable.');
        }

        $works = array_map(static fn (\App\Entity\Oeuvre $oeuvre): array => [
            'title' => $oeuvre->getTitre(),
            'category' => $oeuvre->getCategorie()?->getNom() ?? 'Art',
            'year' => $oeuvre->getAnnee(),
            'image' => $oeuvre->getImageMedia()?->getChemin(),
        ], $oeuvreRepository->findBy(['artiste' => $artiste, 'estActive' => true], ['ordre' => 'ASC']));

        return $this->render('galerie/artiste.html.twig', [
            'artiste' => $artiste,
            'works' => $works,
        ]);
    }

    #[Route('/promotions/{slug}', name: 'app_promotion_detail', methods: ['GET'])]
    public function promotionDetail(string $slug, PromotionRepository $promotionRepository): Response
    {
        $promotion = $promotionRepository->findOneBy(['slug' => $slug, 'estActif' => true]);
        if (!$promotion instanceof \App\Entity\Promotion) {
            throw $this->createNotFoundException('Promotion introuvable.');
        }
        return $this->render('promotion/detail.html.twig', ['promotion' => $promotion]);
    }

    #[Route('/evenements/{slug}', name: 'app_event_detail', methods: ['GET'])]
    public function eventDetail(string $slug, EvenementRepository $evenementRepository): Response
    {
        $event = $evenementRepository->findOneBy(['slug' => $slug, 'estActif' => true]);
        if (!$event instanceof \App\Entity\Evenement) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        return $this->render('evenement/detail.html.twig', ['event' => $event]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('contact', (string) $request->request->get('_token'))) {
                return $this->json(['success' => false, 'message' => 'Votre session a expiré. Rechargez la page puis réessayez.'], 419);
            }

            $name = trim((string) $request->request->get('name'));
            $email = trim((string) $request->request->get('email'));
            $message = trim((string) $request->request->get('message'));
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
                return $this->json(['success' => false, 'message' => 'Veuillez renseigner votre nom, un email valide et votre message.'], 422);
            }

            $contact = (new MessageContact())
                ->setNom($name)
                ->setEmail($email)
                ->setTelephone($request->request->get('telephone') ?: null)
                ->setSujet($request->request->get('subject') ?: null)
                ->setMessage($message);
            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Votre message a bien été envoyé. Nous vous répondrons rapidement.']);
        }

        return $this->render('info/contact.html.twig');
    }

    /** @return array{items: array, current_page: int, total_pages: int, total_items: int, per_page: int} */
    private function paginate(array $items, Request $request, int $perPage = 9): array
    {
        $totalItems = count($items);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, min($totalPages, $request->query->getInt('page', 1)));

        return [
            'items' => array_slice($items, ($currentPage - 1) * $perPage, $perPage),
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'per_page' => $perPage,
        ];
    }
}
