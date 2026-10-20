<?php

namespace App\Repository;

use App\Entity\Billet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

class BilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Billet::class); }

    public function findForControl(string $value, bool $lock = false): ?Billet
    {
        $value = trim($value);
        $query = $this->createQueryBuilder('b')
            ->leftJoin('b.reservation', 'r')->addSelect('r')
            ->leftJoin('r.evenement', 'e')->addSelect('e')
            ->andWhere('b.jeton = :value OR b.reference = :value')
            ->setParameter('value', $value)
            ->getQuery();

        if ($lock) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getOneOrNullResult();
    }

    public function countEmis(): int { return (int) $this->count([]); }

    public function countValidesPourEvenement($evenement): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->join('b.reservation', 'r')
            ->andWhere('r.evenement = :event')->setParameter('event', $evenement)
            ->andWhere('b.statut = :status')->setParameter('status', Billet::STATUT_VALIDE)
            ->getQuery()->getSingleScalarResult();
    }
}
