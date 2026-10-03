<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /** @return Service[] */
    public function findActive(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.estActif = :active')->setParameter('active', true)
            ->orderBy('s.estMisEnAvant', 'DESC')->addOrderBy('s.nom', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return Service[] */
    public function findFeatured(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.estActif = :active')->setParameter('active', true)
            ->andWhere('s.estMisEnAvant = :featured')->setParameter('featured', true)
            ->orderBy('s.nom', 'ASC')->getQuery()->getResult();
    }
}
