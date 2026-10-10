<?php

namespace App\Controller;

use App\Repository\BoutiqueRepository;
use App\Repository\EvenementRepository;
use App\Repository\PromotionRepository;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SeoController extends AbstractController
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly RestaurantRepository $restaurants,
        private readonly PromotionRepository $promotions,
        private readonly EvenementRepository $events,
    ) {
    }

    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $sitemap = $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return new Response(
            "User-agent: *\nAllow: /\n\nDisallow: /olympia-admin\nDisallow: /login\n\nSitemap: {$sitemap}\n",
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $urls = [];
        foreach (['app_home', 'app_enseignes', 'app_restaurants', 'app_actualites', 'app_promotions', 'app_events', 'app_services', 'app_galerie_virtuelle', 'app_contact', 'app_legal_mentions', 'app_legal_privacy', 'app_legal_terms'] as $route) {
            $urls[$this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL)] = null;
        }

        foreach ($this->boutiques->findActive() as $boutique) {
            $slug = $boutique->getEnseigne()?->getSlug();
            if ($slug) {
                $urls[$this->generateUrl('app_enseigne_detail', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL)] = null;
            }
        }

        foreach ($this->restaurants->findActive() as $restaurant) {
            $slug = $restaurant->getEnseigne()?->getSlug();
            if ($slug) {
                $urls[$this->generateUrl('app_restaurant_detail', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL)] = null;
            }
        }

        foreach ($this->promotions->findActive() as $promotion) {
            if ($promotion->getSlug()) {
                $urls[$this->generateUrl('app_promotion_detail', ['slug' => $promotion->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)] = null;
            }
        }

        foreach ($this->events->findActive() as $event) {
            if ($event->getSlug()) {
                $urls[$this->generateUrl('app_event_detail', ['slug' => $event->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)] = null;
            }
        }

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $loc => $lastModified) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.htmlspecialchars($loc, ENT_XML1, 'UTF-8').'</loc>';
            if ($lastModified instanceof \DateTimeInterface) {
                $xml[] = '    <lastmod>'.$lastModified->format('c').'</lastmod>';
            }
            $xml[] = '  </url>';
        }
        $xml[] = '</urlset>';

        return new Response(
            implode("\n", $xml),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }
}
