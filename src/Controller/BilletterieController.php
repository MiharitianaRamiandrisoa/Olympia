<?php

namespace App\Controller;

use App\Dto\ReservationRequest;
use App\Entity\Evenement;
use App\Exception\ReservationException;
use App\Form\ReservationType;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationMailer;
use App\Service\ReservationService;
use App\Service\TicketQrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BilletterieController extends AbstractController
{
    #[Route('/evenements/{slug}/reservation', name: 'app_event_reservation', methods: ['GET', 'POST'])]
    public function reserve(
        string $slug,
        Request $request,
        EvenementRepository $events,
        ReservationService $reservations,
        ReservationMailer $mailer,
    ): Response {
        $event = $events->findOneBy(['slug' => $slug, 'estActif' => true]);
        if (!$event instanceof Evenement) {
            throw $this->createNotFoundException('Evenement introuvable.');
        }

        $data = new ReservationRequest();
        $form = $this->createForm(ReservationType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $reservations->create($event, $data);

                if (!$result['ticketsCreated']) {
                    $paymentEmailSent = $mailer->sendPaymentLink($result['reservation'], $result['accessToken']);

                    return $this->redirectToRoute('app_reservation_payment_simulation', [
                        'token' => $result['accessToken'],
                        'payment_email_sent' => $paymentEmailSent ? '1' : '0',
                    ]);
                }

                $emailSent = $mailer->sendConfirmation($result['reservation'], $result['accessToken']);

                return $this->redirectToRoute('app_reservation_confirmation', [
                    'token' => $result['accessToken'],
                    'email_sent' => $emailSent ? '1' : '0',
                ]);
            } catch (ReservationException $exception) {
                $form->addError(new \Symfony\Component\Form\FormError($exception->getMessage()));
            }
        }

        return $this->render('billetterie/reservation.html.twig', [
            'event' => $event,
            'form' => $form,
            'places_restantes' => $reservations->placesRestantes($event),
        ]);
    }

    #[Route('/billetterie/reservation/{token}/paiement', name: 'app_reservation_payment_simulation', methods: ['GET', 'POST'])]
    public function paymentSimulation(
        string $token,
        Request $request,
        ReservationRepository $reservations,
        ReservationService $reservationService,
        ReservationMailer $mailer,
    ): Response {
        $reservations->deleteExpiredPaymentReservations();
        $reservation = $reservations->findByAccessToken($token);
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation introuvable ou delai de paiement expire.');
        }

        if ($reservation->getStatut() === \App\Entity\Reservation::STATUT_CONFIRMEE) {
            return $this->redirectToRoute('app_reservation_confirmation', ['token' => $token]);
        }

        $paymentError = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('simulate_payment_'.$reservation->getId(), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            try {
                $reservationService->confirmPayment($reservation);
                $emailSent = $mailer->sendConfirmation($reservation, $token);

                return $this->redirectToRoute('app_reservation_confirmation', [
                    'token' => $token,
                    'email_sent' => $emailSent ? '1' : '0',
                ]);
            } catch (ReservationException $exception) {
                $paymentError = $exception->getMessage();
            }
        }

        return $this->render('billetterie/paiement_simulation.html.twig', [
            'reservation' => $reservation,
            'payment_error' => $paymentError,
            'payment_email_sent' => $request->query->getBoolean('payment_email_sent'),
        ]);
    }

    #[Route('/billetterie/reservation/{token}', name: 'app_reservation_confirmation', methods: ['GET'])]
    public function confirmation(string $token, Request $request, ReservationRepository $reservations): Response
    {
        $reservations->deleteExpiredPaymentReservations();
        $reservation = $reservations->findByAccessToken($token);
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation introuvable ou lien expire.');
        }

        return $this->render('billetterie/confirmation.html.twig', [
            'reservation' => $reservation,
            'access_token' => $token,
            'email_sent' => $request->query->getBoolean('email_sent'),
        ]);
    }

    #[Route('/billetterie/tickets/{token}', name: 'app_ticket_wallet', methods: ['GET'])]
    public function wallet(string $token, ReservationRepository $reservations, TicketQrCodeGenerator $qrCodes): Response
    {
        $reservations->deleteExpiredPaymentReservations();
        $reservation = $reservations->findByAccessToken($token);
        if (!$reservation || $reservation->getStatut() !== \App\Entity\Reservation::STATUT_CONFIRMEE) {
            throw $this->createNotFoundException('Billets introuvables ou lien expire.');
        }

        $ticketQrs = [];
        foreach ($reservation->getBillets() as $ticket) {
            $ticketQrs[$ticket->getReference()] = $qrCodes->generateDataUri($ticket);
        }

        return $this->render('billetterie/tickets.html.twig', [
            'reservation' => $reservation,
            'ticket_qrs' => $ticketQrs,
        ]);
    }

    #[Route('/billetterie/ticket/{token}/{reference}/qr', name: 'app_ticket_qr', methods: ['GET'])]
    public function qr(string $token, string $reference, ReservationRepository $reservations, TicketQrCodeGenerator $qrCodes): Response
    {
        $reservations->deleteExpiredPaymentReservations();
        $reservation = $reservations->findByAccessToken($token);
        $ticket = $reservation?->getBillets()->filter(static fn ($item) => $item->getReference() === $reference)->first();
        if (!$reservation || $reservation->getStatut() !== \App\Entity\Reservation::STATUT_CONFIRMEE || !$ticket) {
            throw $this->createNotFoundException('Billet introuvable.');
        }

        $dataUri = $qrCodes->generateDataUri($ticket);
        [, $encoded] = explode(',', $dataUri, 2);

        return new Response(base64_decode($encoded, true), Response::HTTP_OK, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, no-store']);
    }
}
