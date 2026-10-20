<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Reservation::class); }

    public function countPlacesConfirmees(Evenement $evenement): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.nombrePlaces), 0)')
            ->andWhere('r.evenement = :event')->setParameter('event', $evenement)
            ->andWhere('r.statut = :status')->setParameter('status', Reservation::STATUT_CONFIRMEE)
            ->getQuery()->getSingleScalarResult();
    }

    public function countPlacesReservees(Evenement $evenement): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.nombrePlaces), 0)')
            ->andWhere('r.evenement = :event')->setParameter('event', $evenement)
            ->andWhere('(r.statut = :confirmed OR (r.statut = :pending AND r.dateExpirationPaiement > :now))')
            ->setParameter('confirmed', Reservation::STATUT_CONFIRMEE)
            ->setParameter('pending', Reservation::STATUT_EN_ATTENTE_PAIEMENT)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()->getSingleScalarResult();
    }

    public function deleteExpiredPaymentReservations(?\DateTimeImmutable $now = null): int
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->andWhere('r.statut = :status')
            ->andWhere('r.dateExpirationPaiement IS NOT NULL')
            ->andWhere('r.dateExpirationPaiement <= :now')
            ->setParameter('status', Reservation::STATUT_EN_ATTENTE_PAIEMENT)
            ->setParameter('now', $now ?? new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function findForUpdate(int $id): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')->setParameter('id', $id)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }

    public function countConfirmations(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')->andWhere('r.statut = :status')
            ->setParameter('status', Reservation::STATUT_CONFIRMEE)
            ->getQuery()->getSingleScalarResult();
    }

    public function findByAccessToken(string $rawToken): ?Reservation
    {
        return $this->findOneBy(['jetonAccesHash' => hash('sha256', $rawToken)]);
    }

    /** @return Reservation[] */
    public function findForEvent(Evenement $evenement): array
    {
        return $this->findBy(['evenement' => $evenement], ['dateCreation' => 'DESC']);
    }
}
