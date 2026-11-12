<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\BoutiquePhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoutiquePhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoutiquePhoto::class);
    }

    /** @return BoutiquePhoto[] */
    public function findActiveForBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('photo')
            ->leftJoin('photo.imageMedia', 'media')->addSelect('media')
            ->andWhere('photo.boutique = :boutique')
            ->andWhere('photo.estActif = :active')
            ->setParameter('boutique', $boutique)
            ->setParameter('active', true)
            ->orderBy('photo.ordre', 'ASC')
            ->addOrderBy('photo.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
