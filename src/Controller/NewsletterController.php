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

        $subscriber = $repository->findOneBy(['email' => $email]);
        $isNewSubscriber = $subscriber === null;
        if ($isNewSubscriber) {
            $subscriber = (new AbonnementNewsletter())->setEmail($email);
            $entityManager->persist($subscriber);
            $entityManager->flush();
        }

        $emailSent = false;
        if ($subscriber->getWelcomeEmailSentAt() === null) {
            $emailSent = $newsletterMailer->sendWelcome($email);
            if ($emailSent) {
                $subscriber->markWelcomeEmailSent();
                $entityManager->flush();
            }
        }

        $alreadyWelcomed = $subscriber->getWelcomeEmailSentAt() !== null && !$emailSent;

        return $this->json([
            'success' => $emailSent || $alreadyWelcomed,
            'message' => $emailSent
                ? 'Votre inscription est confirmée. Un email de bienvenue vient de vous être envoyé.'
                : ($alreadyWelcomed
                    ? 'Cette adresse est déjà inscrite et son email de bienvenue a déjà été envoyé.'
                    : 'Votre inscription est enregistrée, mais l’email n’a pas pu être envoyé. Réessayez dans quelques instants.'),
            'emailSent' => $emailSent,
            'isNewSubscriber' => $isNewSubscriber,
        ]);
    }
}
