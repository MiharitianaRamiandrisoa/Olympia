<?php

namespace App\Service;

use App\Repository\ActualiteRepository;
use App\Repository\PromotionRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class NewsletterMailer
{
    public function __construct(
        private readonly TransportInterface $transport,
        private readonly PromotionRepository $promotions,
        private readonly ActualiteRepository $actualites,
        private readonly string $mailerDsn,
        private readonly string $fromAddress,
    ) {
    }

    public function sendWelcome(string $recipient): bool
    {
        if ($this->mailerDsn === '' || str_starts_with($this->mailerDsn, 'null://')) {
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->fromAddress)
            ->to($recipient)
            ->subject('Bienvenue dans la newsletter Olympia')
            ->htmlTemplate('email/newsletter_welcome.html.twig')
            ->context([
                'promotions' => array_slice($this->promotions->findActive(), 0, 6),
                'actualites' => array_slice($this->actualites->findActive(), 0, 4),
            ]);

        try {
            // Use the transport directly so the welcome message is delivered
            // during the subscription request, without requiring a Messenger worker.
            $this->transport->send($email);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
