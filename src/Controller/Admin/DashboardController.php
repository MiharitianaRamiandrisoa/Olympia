<?php

namespace App\Controller\Admin;

use App\Entity\AbonnementNewsletter;
use App\Entity\Boutique;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieEvenement;
use App\Entity\CategoriePromotion;
use App\Entity\CategorieRestaurant;
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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\BoutiqueRepository;
use App\Repository\RestaurantRepository;
use App\Repository\PromotionRepository;
use App\Repository\EvenementRepository;
use App\Repository\MessageContactRepository;

#[AdminDashboard(routePath: '/olympia-admin', routeName: 'app_admin_dashboard')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly RestaurantRepository $restaurants,
        private readonly PromotionRepository $promotions,
        private readonly EvenementRepository $events,
        private readonly MessageContactRepository $messages,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                ['label' => 'Boutiques', 'value' => $this->boutiques->count([]), 'icon' => 'fa-store', 'color' => 'primary', 'route' => 'app_admin_dashboard_boutique_index'],
                ['label' => 'Restaurants', 'value' => $this->restaurants->count([]), 'icon' => 'fa-utensils', 'color' => 'success', 'route' => 'app_admin_dashboard_restaurant_index'],
                ['label' => 'Promotions', 'value' => $this->promotions->count([]), 'icon' => 'fa-tags', 'color' => 'warning', 'route' => 'app_admin_dashboard_promotion_index'],
                ['label' => 'Événements', 'value' => $this->events->count([]), 'icon' => 'fa-calendar', 'color' => 'info', 'route' => 'app_admin_dashboard_evenement_index'],
                ['label' => 'Messages non lus', 'value' => $this->messages->count(['estLu' => false]), 'icon' => 'fa-envelope', 'color' => 'danger', 'route' => 'app_admin_dashboard_message_contact_index'],
            ],
        ]);
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
            '<img src="/images/logo/logo-header-teal.png" alt="Olympia" style="max-width: 150px; max-height: 60px; object-fit: contain;">'
        )->setFaviconPath('images/logo/favico/favicon.ico');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Contenu du site');
        yield MenuItem::linkToRoute('Boutiques', 'fa fa-store', 'app_admin_dashboard_boutique_index');
        yield MenuItem::linkToRoute('Restaurants', 'fa fa-utensils', 'app_admin_dashboard_restaurant_index');
        yield MenuItem::linkToRoute('Services', 'fa fa-concierge-bell', 'app_admin_dashboard_service_index');
        yield MenuItem::linkToRoute('Informations pratiques', 'fa fa-info-circle', 'app_admin_dashboard_information_pratique_index');
        yield MenuItem::linkToRoute('Promotions', 'fa fa-tags', 'app_admin_dashboard_promotion_index');
        yield MenuItem::linkToRoute('Événements', 'fa fa-calendar', 'app_admin_dashboard_evenement_index');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'app_admin_dashboard_utilisateur_index');
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
