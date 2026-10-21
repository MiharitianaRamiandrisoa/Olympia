<?php

namespace App\Controller\Admin;

use App\Entity\Artiste;
use App\Entity\CategorieOeuvre;
use App\Entity\Exposition;
use App\Entity\Oeuvre;

use App\Entity\AbonnementNewsletter;
use App\Entity\Boutique;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieEvenement;
use App\Entity\CategoriePromotion;
use App\Entity\CategorieRestaurant;
use App\Entity\ControleEntree;
use App\Entity\Enseigne;
use App\Entity\Evenement;
use App\Entity\Media;
use App\Entity\MessageContact;
use App\Entity\Promotion;
use App\Entity\Restaurant;
use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BoutiqueRepository;
use App\Repository\RestaurantRepository;
use App\Repository\PromotionRepository;
use App\Repository\EvenementRepository;
use App\Repository\ActualiteRepository;
use App\Repository\MessageContactRepository;
use App\Repository\ReservationRepository;
use App\Repository\BilletRepository;
use App\Repository\ControleEntreeRepository;
use App\Service\EntryControlService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/olympia-admin', routeName: 'app_admin_dashboard')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly RestaurantRepository $restaurants,
        private readonly PromotionRepository $promotions,
        private readonly EvenementRepository $events,
        private readonly ActualiteRepository $actualites,
        private readonly MessageContactRepository $messages,
        private readonly ReservationRepository $reservations,
        private readonly BilletRepository $billets,
        private readonly ControleEntreeRepository $controles,
    ) {
    }

    public function index(): Response
    {
        $ticketEvents = array_map(function (Evenement $event): array {
            $reserved = $this->reservations->countPlacesReservees($event);
            return [
                'event' => $event,
                'reserved' => $reserved,
                'remaining' => $event->getCapaciteMaximale() === null ? null : max(0, $event->getCapaciteMaximale() - $reserved),
            ];
        }, $this->events->findBy([], ['dateDebut' => 'ASC']));

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                ['label' => 'Boutiques', 'value' => $this->boutiques->count([]), 'icon' => 'fa-store', 'color' => 'primary', 'route' => 'app_admin_dashboard_boutique_index'],
                ['label' => 'Restaurants', 'value' => $this->restaurants->count([]), 'icon' => 'fa-utensils', 'color' => 'success', 'route' => 'app_admin_dashboard_restaurant_index'],
                ['label' => 'Promotions', 'value' => $this->promotions->count([]), 'icon' => 'fa-tags', 'color' => 'warning', 'route' => 'app_admin_dashboard_promotion_index'],
                ['label' => 'Événements', 'value' => $this->events->count([]), 'icon' => 'fa-calendar', 'color' => 'info', 'route' => 'app_admin_dashboard_evenement_index'],
                ['label' => 'Actualites', 'value' => $this->actualites->count([]), 'icon' => 'fa-newspaper', 'color' => 'secondary', 'route' => 'app_admin_dashboard_actualite_index'],
                ['label' => 'Messages non lus', 'value' => $this->messages->count(['estLu' => false]), 'icon' => 'fa-envelope', 'color' => 'danger', 'route' => 'app_admin_dashboard_message_contact_index'],
                ['label' => 'Evenements publies', 'value' => $this->events->count(['estActif' => true]), 'icon' => 'fa-bullhorn', 'color' => 'info', 'route' => 'app_admin_dashboard_evenement_index'],
                ['label' => 'Reservations', 'value' => $this->reservations->count([]), 'icon' => 'fa-ticket', 'color' => 'primary', 'route' => 'app_admin_ticket_export'],
                ['label' => 'Billets emis', 'value' => $this->billets->countEmis(), 'icon' => 'fa-qrcode', 'color' => 'success', 'route' => 'app_admin_ticket_export'],
                ['label' => 'Entrees validees', 'value' => $this->controles->countValide(), 'icon' => 'fa-check', 'color' => 'warning', 'route' => 'app_admin_ticket_export'],
            ],
            'ticket_events' => $ticketEvents,
        ]);
    }

    #[Route('/olympia-admin/controle-billets', name: 'app_ticket_control', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TICKET_CONTROL')]
    public function ticketControl(Request $request, EntryControlService $controlService): Response
    {
        $result = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('ticket_control', (string) $request->request->get('_token'))) {
                $result = ['resultat' => 'csrf', 'message' => 'Session expiree. Rechargez la page.'];
            } else {
                $value = trim((string) $request->request->get('ticket'));
                $control = $controlService->control($value, $this->getUser() instanceof Utilisateur ? $this->getUser() : null);
                $result = [
                    'resultat' => $control['resultat'],
                    'message' => $this->ticketControlMessage($control['resultat']),
                    'billet' => $control['billet'],
                ];
            }

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'resultat' => $result['resultat'],
                    'message' => $this->ticketControlMessage($result['resultat']),
                    'billet' => isset($result['billet']) && $result['billet'] ? $result['billet']->getReference() : null,
                ]);
            }
        }

        return $this->render('admin/ticket_control.html.twig', ['result' => $result]);
    }

    #[Route('/olympia-admin/billetterie/export', name: 'app_admin_ticket_export', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function ticketExport(): Response
    {
        return $this->render('admin/ticket_export.html.twig', [
            'events' => $this->events->findBy([], ['dateDebut' => 'ASC', 'titre' => 'ASC']),
        ]);
    }

    private function ticketControlMessage(string $result): string
    {
        return match ($result) {
            ControleEntree::RESULTAT_VALIDE => 'Billet valide : entree enregistree.',
            ControleEntree::RESULTAT_DEJA_UTILISE => 'Ce billet a deja ete utilise.',
            ControleEntree::RESULTAT_ANNULE => 'Ce billet est annule.',
            ControleEntree::RESULTAT_INCONNU => 'Billet inconnu ou invalide.',
            default => 'La requete n a pas pu etre traitee.',
        };
    }

    /*
    public function index(
        BoutiqueRepository $boutiques,
        RestaurantRepository $restaurants,
        PromotionRepository $promotions,
        EvenementRepository $events,
        MessageContactRepository $messages,
    ): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                ['label' => 'Boutiques', 'value' => $boutiques->count([]), 'icon' => 'fa-store', 'color' => 'primary', 'route' => 'app_admin_dashboard_boutique_index'],
                ['label' => 'Restaurants', 'value' => $restaurants->count([]), 'icon' => 'fa-utensils', 'color' => 'success', 'route' => 'app_admin_dashboard_restaurant_index'],
                ['label' => 'Promotions', 'value' => $promotions->count([]), 'icon' => 'fa-tags', 'color' => 'warning', 'route' => 'app_admin_dashboard_promotion_index'],
                ['label' => 'Événements', 'value' => $events->count([]), 'icon' => 'fa-calendar', 'color' => 'info', 'route' => 'app_admin_dashboard_evenement_index'],
                ['label' => 'Messages non lus', 'value' => $messages->count(['estLu' => false]), 'icon' => 'fa-envelope', 'color' => 'danger', 'route' => 'app_admin_dashboard_message_contact_index'],
            ],
        ]);
    }
    */

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle(
            '<img class="admin-olympia-logo" src="/images/logo/OLY_LOGO HOR.png" alt="Olympia Centre Commercial">'
        )->setFaviconPath('images/logo/favico/favicon.ico')
            ->disableDarkMode()
            ->setDefaultColorScheme(ColorScheme::LIGHT);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Billetterie');
        yield MenuItem::linkToRoute('Reservations', 'fa fa-ticket', 'app_admin_dashboard_reservation_index');
        yield MenuItem::linkToRoute('Billets', 'fa fa-qrcode', 'app_admin_dashboard_billet_index');
        yield MenuItem::linkToRoute('Controle des billets', 'fa fa-qrcode', 'app_ticket_control')
            ->setPermission('ROLE_TICKET_CONTROL');
        yield MenuItem::linkToRoute('Export participants CSV', 'fa fa-download', 'app_admin_ticket_export');
        yield MenuItem::section('Galerie');
        yield MenuItem::linkToRoute('Œuvres', 'fa fa-image', 'app_admin_dashboard_oeuvre_index');
        yield MenuItem::linkToRoute('Artistes', 'fa fa-user', 'app_admin_dashboard_artiste_index');
        yield MenuItem::linkToRoute('Expositions', 'fa fa-palette', 'app_admin_dashboard_exposition_index');
        yield MenuItem::linkToRoute('Catégories d’œuvres', 'fa fa-tags', 'app_admin_dashboard_categorie_oeuvre_index');
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Contenu du site');
        yield MenuItem::linkToRoute('Boutiques', 'fa fa-store', 'app_admin_dashboard_boutique_index');
        yield MenuItem::linkToRoute('Restaurants', 'fa fa-utensils', 'app_admin_dashboard_restaurant_index');
        yield MenuItem::linkToRoute('Services', 'fa fa-concierge-bell', 'app_admin_dashboard_service_index');
        yield MenuItem::linkToRoute('Informations pratiques', 'fa fa-info-circle', 'app_admin_dashboard_information_pratique_index');
        yield MenuItem::linkToRoute('Promotions', 'fa fa-tags', 'app_admin_dashboard_promotion_index');
        yield MenuItem::linkToRoute('Événements', 'fa fa-calendar', 'app_admin_dashboard_evenement_index');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'app_admin_dashboard_utilisateur_index');
        yield MenuItem::linkToRoute('Actualites', 'fa fa-newspaper', 'app_admin_dashboard_actualite_index');
        yield MenuItem::section('Catégories');
        yield MenuItem::linkToRoute('Boutiques', 'fa fa-list', 'app_admin_dashboard_categorie_boutique_index');
        yield MenuItem::linkToRoute('Restaurants', 'fa fa-list', 'app_admin_dashboard_categorie_restaurant_index');
        yield MenuItem::linkToRoute('Promotions', 'fa fa-list', 'app_admin_dashboard_categorie_promotion_index');
        yield MenuItem::linkToRoute('Événements', 'fa fa-list', 'app_admin_dashboard_categorie_evenement_index');
        yield MenuItem::section('Communication');
        yield MenuItem::linkToRoute('Messages de contact', 'fa fa-envelope', 'app_admin_dashboard_message_contact_index');
        yield MenuItem::linkToRoute('Abonnés newsletter', 'fa fa-bell', 'app_admin_dashboard_abonnement_newsletter_index');
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToUrl('Se déconnecter', 'fa fa-sign-out', '/olympia-admin/logout');
    }
}
