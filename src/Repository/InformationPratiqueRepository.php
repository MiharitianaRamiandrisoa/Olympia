<?php

namespace App\Repository;

use App\Entity\InformationPratique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InformationPratiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, InformationPratique::class); }

    /** @return InformationPratique[] */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.estActif = :active')->setParameter('active', true)
            ->orderBy('i.ordreAffichage', 'ASC')->addOrderBy('i.titre', 'ASC')
            ->getQuery()->getResult();
    }

    public function findActiveAddress(): ?InformationPratique
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :type')->setParameter('type', 'adresse')
            ->andWhere('i.estActif = :active')->setParameter('active', true)
            ->orderBy('i.ordreAffichage', 'ASC')->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
