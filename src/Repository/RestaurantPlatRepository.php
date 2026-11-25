<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\RestaurantPlat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RestaurantPlatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantPlat::class);
    }

    /** @return RestaurantPlat[] */
    public function findActiveForRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('plat')
            ->leftJoin('plat.imageMedia', 'media')->addSelect('media')
            ->andWhere('plat.restaurant = :restaurant')
            ->andWhere('plat.estActif = :active')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('active', true)
            ->orderBy('plat.ordre', 'ASC')
            ->addOrderBy('plat.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
