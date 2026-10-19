<?php

namespace App\Service;

use App\Dto\ReservationRequest;
use App\Entity\Billet;
use App\Entity\Evenement;
use App\Entity\Reservation;
use App\Exception\ReservationException;
use App\Repository\EvenementRepository;
use App\Repository\BilletRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ReservationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EvenementRepository $evenements,
        private readonly ReservationRepository $reservations,
        private readonly BilletRepository $billets,
        private readonly int $paymentHoldMinutes,
    ) {
    }

    /** @return array{reservation: Reservation, accessToken: string, ticketsCreated: bool} */
    public function create(Evenement $event, ReservationRequest $request): array
    {
        if (!$event->getId()) {
            throw new ReservationException('Cet evenement ne peut pas recevoir de reservation.');
        }

        $accessToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $result = null;

        $this->reservations->deleteExpiredPaymentReservations();
        $this->entityManager->beginTransaction();
        try {
            $lockedEvent = $this->evenements->findForUpdate($event->getId());
            if (!$lockedEvent instanceof Evenement || !$lockedEvent->isReservationOuverte()) {
                throw new ReservationException('Les reservations ne sont pas ouvertes pour cet evenement.');
            }

            $placesReservees = $this->reservations->countPlacesReservees($lockedEvent);
            if ($lockedEvent->getCapaciteMaximale() !== null
                && $placesReservees + $request->nombrePlaces > $lockedEvent->getCapaciteMaximale()) {
                throw new ReservationException('Cet evenement est complet ou ne dispose plus d assez de places.');
            }

            $reservation = (new Reservation())
                ->setEvenement($lockedEvent)
                ->setReference($this->newReference('OLY'))
                ->setNom($request->nom)
                ->setEmail($request->email)
                ->setTelephone($request->telephone)
                ->setNombrePlaces($request->nombrePlaces)
                ->setJetonAccesHash(hash('sha256', $accessToken));

            $ticketsCreated = $lockedEvent->isGratuit();
            if ($ticketsCreated) {
                $reservation->setStatut(Reservation::STATUT_CONFIRMEE)
                    ->setStatutPaiement(Reservation::PAIEMENT_NON_APPLICABLE)
                    ->setDateConfirmation(new \DateTimeImmutable());

                for ($index = 0; $index < $request->nombrePlaces; ++$index) {
                    $reservation->addBillet(
                        (new Billet())
                            ->setReference($this->newReference('TKT'))
                            ->setJeton(bin2hex(random_bytes(32)))
                    );
                }
            } else {
                $reservation->setStatut(Reservation::STATUT_EN_ATTENTE_PAIEMENT)
                    ->setStatutPaiement(Reservation::PAIEMENT_EN_ATTENTE)
                    ->setDateExpirationPaiement(
                        (new \DateTimeImmutable())->modify(sprintf('+%d minutes', max(1, $this->paymentHoldMinutes)))
                    );
            }

            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            $this->entityManager->commit();
            $result = ['reservation' => $reservation, 'accessToken' => $accessToken, 'ticketsCreated' => $ticketsCreated];
        } catch (\Throwable $exception) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $exception;
        }

        return $result;
    }

    public function confirmPayment(Reservation $reservation): void
    {
        if ($reservation->getStatut() === Reservation::STATUT_CONFIRMEE) {
            return;
        }

        $this->entityManager->beginTransaction();
        try {
            $lockedReservation = $this->reservations->findForUpdate($reservation->getId());
            if (!$lockedReservation instanceof Reservation) {
                throw new ReservationException('Reservation introuvable.');
            }

            if ($lockedReservation->isPaiementExpire()) {
                $this->entityManager->remove($lockedReservation);
                $this->entityManager->flush();
                $this->entityManager->commit();
                throw new ReservationException('Le delai de paiement est expire. La reservation a ete supprimee.');
            }

            if ($lockedReservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_PAIEMENT) {
                throw new ReservationException('Cette reservation ne peut plus etre payee.');
            }

            $lockedReservation
                ->setStatut(Reservation::STATUT_CONFIRMEE)
                ->setStatutPaiement(Reservation::PAIEMENT_PAYE)
                ->setDateConfirmation(new \DateTimeImmutable())
                ->setDateExpirationPaiement(null);

            for ($index = $lockedReservation->getBillets()->count(); $index < $lockedReservation->getNombrePlaces(); ++$index) {
                $lockedReservation->addBillet(
                    (new Billet())
                        ->setReference($this->newReference('TKT'))
                        ->setJeton(bin2hex(random_bytes(32)))
                );
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $exception;
        }
    }

    public function placesRestantes(Evenement $event): ?int
    {
        if ($event->getCapaciteMaximale() === null) {
            return null;
        }

        $this->reservations->deleteExpiredPaymentReservations();
        return max(0, $event->getCapaciteMaximale() - $this->reservations->countPlacesReservees($event));
    }

    private function newReference(string $prefix): string
    {
        do {
            $reference = $prefix.'-'.strtoupper(bin2hex(random_bytes(6)));
        } while ($this->reservations->findOneBy(['reference' => $reference]) !== null
            || $this->billets->findOneBy(['reference' => $reference]) !== null);

        return $reference;
    }
}
