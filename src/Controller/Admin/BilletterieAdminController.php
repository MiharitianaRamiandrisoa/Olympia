<?php

namespace App\Controller\Admin;

use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class BilletterieAdminController extends AbstractController
{
    #[Route('/olympia-admin/billetterie/participants.csv', name: 'app_admin_ticket_participants_csv', methods: ['GET'])]
    public function participants(Request $request, ReservationRepository $reservations, EvenementRepository $events): Response
    {
        $event = null;
        $eventId = $request->query->getInt('event');
        if ($eventId > 0) {
            $event = $events->find($eventId);
            if (!$event) {
                throw $this->createNotFoundException('Evenement introuvable.');
            }
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Reference', 'Evenement', 'Nom', 'Email', 'Telephone', 'Places', 'Statut', 'Paiement', 'Date']);
        $items = $event
            ? $reservations->findByEvent($event)
            : $reservations->findBy([], ['dateCreation' => 'DESC']);

        foreach ($items as $reservation) {
            fputcsv($handle, [
                $reservation->getReference(),
                $reservation->getEvenement()?->getTitre(),
                $reservation->getNom(),
                $reservation->getEmail(),
                $reservation->getTelephone(),
                $reservation->getNombrePlaces(),
                $reservation->getStatut(),
                $reservation->getStatutPaiement(),
                $reservation->getDateCreation()->format(DATE_ATOM),
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = 'olympia-participants';
        if ($event) {
            $filename .= '-'.trim((string) preg_replace('/[^a-z0-9]+/i', '-', $event->getSlug()), '-');
        }

        return new Response("\xEF\xBB\xBF".$csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }
}
