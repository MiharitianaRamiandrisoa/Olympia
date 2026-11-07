<?php

namespace App\Controller;

use App\Entity\AbonnementNewsletter;
use App\Repository\AbonnementNewsletterRepository;
use App\Service\NewsletterMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        AbonnementNewsletterRepository $repository,
        EntityManagerInterface $entityManager,
        NewsletterMailer $newsletterMailer,
    ): JsonResponse {
        $email = mb_strtolower(trim((string) $request->request->get('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'message' => 'Veuillez saisir une adresse email valide.'], 422);
        }

        $isNewSubscriber = $repository->findOneBy(['email' => $email]) === null;
        if ($isNewSubscriber) {
            $entityManager->persist((new AbonnementNewsletter())->setEmail($email));
            $entityManager->flush();
        }

        $emailSent = $isNewSubscriber && $newsletterMailer->sendWelcome($email);
        return $this->json([
            'success' => true,
            'message' => $isNewSubscriber
                ? ($emailSent
                    ? 'Votre inscription est confirmée. Un email de bienvenue vient de vous être envoyé.'
                    : 'Votre inscription est enregistrée. L’envoi de l’email sera disponible après configuration du serveur mail.')
                : 'Cette adresse est déjà inscrite à la newsletter.',
            'emailSent' => $emailSent,
        ]);
    }
}
