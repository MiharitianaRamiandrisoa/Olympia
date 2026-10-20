<?php

namespace App\Repository;

use App\Entity\ControleEntree;
use App\Entity\Billet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ControleEntreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ControleEntree::class); }

    public function countValide(): int
    {
        return (int) $this->count(['resultat' => ControleEntree::RESULTAT_VALIDE]);
    }

    public function existsSuccessfulFor(Billet $billet): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('1')
            ->andWhere('c.billet = :billet')->setParameter('billet', $billet)
            ->andWhere('c.resultat = :result')->setParameter('result', ControleEntree::RESULTAT_VALIDE)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
