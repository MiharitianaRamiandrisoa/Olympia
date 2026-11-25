<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\RestaurantPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RestaurantPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantPhoto::class);
    }

    /** @return RestaurantPhoto[] */
    public function findActiveForRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('photo')
            ->leftJoin('photo.imageMedia', 'media')->addSelect('media')
            ->andWhere('photo.restaurant = :restaurant')
            ->andWhere('photo.estActif = :active')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('active', true)
            ->orderBy('photo.ordre', 'ASC')
            ->addOrderBy('photo.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
