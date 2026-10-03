<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_mentions', methods: ['GET'])]
    public function mentions(): Response
    {
        return $this->renderPage(
            'Mentions légales',
            'Informations légales du centre commercial Olympia Madagascar à Tanjombato.',
            'Retrouvez les informations légales relatives au site et au centre commercial Olympia Madagascar.'
        );
    }

    #[Route('/confidentialite', name: 'app_legal_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->renderPage(
            'Politique de confidentialité',
            'Politique de confidentialité du site Olympia Madagascar.',
            'Olympia Madagascar respecte votre vie privée et utilise vos données uniquement pour assurer le fonctionnement du site et répondre à vos demandes.'
        );
    }

    #[Route('/cgu', name: 'app_legal_terms', methods: ['GET'])]
    public function terms(): Response
    {
        return $this->renderPage(
            'Conditions générales d’utilisation',
            'Conditions générales d’utilisation du site Olympia Madagascar.',
            'L’utilisation du site Olympia Madagascar implique l’acceptation des présentes conditions générales d’utilisation.'
        );
    }

    private function renderPage(string $title, string $description, string $intro): Response
    {
        return $this->render('legal/page.html.twig', [
            'page_title' => $title,
            'meta_description' => $description,
            'intro' => $intro,
        ]);
    }
}
