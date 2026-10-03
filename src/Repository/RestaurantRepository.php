<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Restaurant::class); }
    /** @return Restaurant[] */
    public function findActive(?string $search = null, ?string $category = null): array
    {
        $qb = $this->createQueryBuilder('r')->join('r.enseigne', 'e')->addSelect('e')
            ->leftJoin('r.categorie', 'c')->addSelect('c')
            ->andWhere('r.estActif = :active')->setParameter('active', true)->orderBy('e.nom', 'ASC');
        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(e.nom) LIKE :search OR LOWER(c.nom) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower(trim($search)).'%');
        }
        if ($category !== null && trim($category) !== '') {
            $qb->andWhere('c.slug = :category')->setParameter('category', $category);
        }
        return $qb->getQuery()->getResult();
    }

    public function findActiveByEnseigneSlug(string $slug): ?Restaurant
    {
        return $this->createQueryBuilder('r')
            ->join('r.enseigne', 'e')->addSelect('e')
            ->leftJoin('r.categorie', 'c')->addSelect('c')
            ->leftJoin('r.photoMedia', 'm')->addSelect('m')
            ->andWhere('e.slug = :slug')->setParameter('slug', $slug)
            ->andWhere('r.estActif = :active')->setParameter('active', true)
            ->getQuery()->getOneOrNullResult();
    }
}
