<?php

namespace App\Service;

use App\Entity\Actualite;
use App\Repository\AbonnementNewsletterRepository;
use App\Repository\ActualiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PromotionRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

final class NewsletterMailer
{
    public function __construct(
        private readonly TransportInterface $transport,
        private readonly PromotionRepository $promotions,
        private readonly ActualiteRepository $actualites,
        private readonly AbonnementNewsletterRepository $abonnements,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $mailerDsn,
        private readonly string $fromAddress,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendWelcome(string $recipient): bool
    {
        if ($this->mailerDsn === '' || str_starts_with($this->mailerDsn, 'null://')) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from($this->fromAddress)
                ->to($recipient)
                ->subject('Bienvenue dans la newsletter Olympia')
                ->htmlTemplate('email/newsletter_welcome.html.twig')
                ->textTemplate('email/newsletter_welcome.txt.twig')
                ->context([
                    'promotions' => array_slice($this->promotions->findActive(), 0, 6),
                    'actualites' => array_slice($this->actualites->findActive(), 0, 4),
                ]);

            // Use the transport directly so the welcome message is delivered
            // during the subscription request, without requiring a Messenger worker.
            $this->transport->send($email);
            return true;
        } catch (\Throwable $exception) {
            $this->logger->error('Newsletter welcome email could not be sent.', [
                'exception' => $exception,
            ]);

            return false;
        }
    }

    public function sendActualite(Actualite $actualite): bool
    {
        if ($actualite->getNewsletterEnvoyeeLe() !== null) {
            return true;
        }

        if (!$actualite->isPubliee() || $this->mailerDsn === '' || str_starts_with($this->mailerDsn, 'null://')) {
            return false;
        }

        $recipients = array_values(array_unique(array_filter(array_map(
            static fn ($abonnement): ?string => $abonnement->getEmail(),
            $this->abonnements->findAll(),
        ))));

        if ($recipients === []) {
            $actualite->markNewsletterEnvoyee();
            $this->entityManager->flush();

            return true;
        }

        $failed = false;
        foreach ($recipients as $recipient) {
            try {
                $email = (new TemplatedEmail())
                    ->from($this->fromAddress)
                    ->to($recipient)
                    ->subject('Nouvelle actualité Olympia : '.$actualite->getTitre())
                    ->htmlTemplate('email/newsletter_actualite.html.twig')
                    ->textTemplate('email/newsletter_actualite.txt.twig')
                    ->context(['actualite' => $actualite]);

                $this->transport->send($email);
            } catch (\Throwable $exception) {
                $failed = true;
                $this->logger->error('Newsletter actualité could not be sent.', [
                    'recipient' => $recipient,
                    'actualite_id' => $actualite->getId(),
                    'exception' => $exception,
                ]);
            }
        }

        if ($failed) {
            return false;
        }

        $actualite->markNewsletterEnvoyee();
        $this->entityManager->flush();

        return true;
    }
}
