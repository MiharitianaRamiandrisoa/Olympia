<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ReservationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailerDsn,
    ) {
    }

    public function sendConfirmation(Reservation $reservation, string $accessToken): bool
    {
        if ($this->mailerDsn === '' || str_starts_with($this->mailerDsn, 'null://')) {
            return false;
        }

        $url = $this->urlGenerator->generate('app_ticket_wallet', ['token' => $accessToken], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new TemplatedEmail())
            ->from('billetterie@olympia.mg')
            ->to($reservation->getEmail())
            ->subject('Confirmation de votre reservation Olympia '.$reservation->getReference())
            ->htmlTemplate('email/reservation_confirmation.html.twig')
            ->context(['reservation' => $reservation, 'ticketsUrl' => $url]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendPaymentLink(Reservation $reservation, string $accessToken): bool
    {
        if ($this->mailerDsn === '' || str_starts_with($this->mailerDsn, 'null://')) {
            return false;
        }

        $paymentUrl = $this->urlGenerator->generate(
            'app_reservation_payment_simulation',
            ['token' => $accessToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $email = (new TemplatedEmail())
            ->from('billetterie@olympia.mg')
            ->to($reservation->getEmail())
            ->subject('Finalisez votre reservation Olympia '.$reservation->getReference())
            ->htmlTemplate('email/reservation_payment.html.twig')
            ->context(['reservation' => $reservation, 'paymentUrl' => $paymentUrl]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
