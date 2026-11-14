<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BoutiqueRepository;
use App\Repository\BoutiquePhotoRepository;
use App\Repository\RestaurantRepository;
use App\Repository\RestaurantPhotoRepository;
use App\Repository\RestaurantPlatRepository;
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
use App\Repository\InformationPratiqueRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Boutique;
use App\Entity\Restaurant;
use App\Entity\MessageContact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    }

    #[Route('/enseignes/{slug}', name: 'app_enseigne_detail', methods: ['GET'])]
    public function boutiqueDetail(string $slug, BoutiqueRepository $boutiqueRepository, BoutiquePhotoRepository $boutiquePhotoRepository): Response
    {
        $boutique = $boutiqueRepository->findActiveByEnseigneSlug($slug);
        if (!$boutique instanceof Boutique) {
            throw $this->createNotFoundException('Boutique introuvable.');
        }

        return $this->render('boutique/detail.html.twig', [
            'boutique' => $boutique,
            'boutique_photos' => $boutiquePhotoRepository->findActiveForBoutique($boutique),
        ]);
    }

    #[Route('/boutiques/{slug}', name: 'app_boutique_detail_legacy', methods: ['GET'])]
    public function boutiqueDetailLegacy(string $slug): Response
    {
        return $this->redirectToRoute('app_enseigne_detail', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/restaurants/{slug}', name: 'app_restaurant_detail', methods: ['GET'])]
    public function restaurantDetail(string $slug, RestaurantRepository $restaurantRepository, RestaurantPhotoRepository $restaurantPhotoRepository, RestaurantPlatRepository $restaurantPlatRepository): Response
    {
        $restaurant = $restaurantRepository->findActiveByEnseigneSlug($slug);
        if (!$restaurant instanceof Restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        return $this->render('restaurant/detail.html.twig', [
            'restaurant' => $restaurant,
            'restaurant_photos' => $restaurantPhotoRepository->findActiveForRestaurant($restaurant),
            'restaurant_plats' => $restaurantPlatRepository->findActiveForRestaurant($restaurant),
        ]);
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

    #[Route('/informations-pratiques', name: 'app_informations_pratiques', methods: ['GET'])]
    public function informationsPratiques(InformationPratiqueRepository $informationPratiqueRepository): Response
    {
        return $this->render('info/pratiques.html.twig', [
            'items' => $informationPratiqueRepository->findActiveOrdered(),
            'address' => $informationPratiqueRepository->findActiveAddress(),
        ]);
    }

    #[Route('/informations', name: 'app_informations_legacy', methods: ['GET'])]
    public function informationsLegacy(): Response
    {
        return $this->redirectToRoute('app_informations_pratiques', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/galerie', name: 'app_galerie_virtuelle', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_USER')]
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
    private function paginate(array $items, Request $request, int $perPage = 10): array
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
