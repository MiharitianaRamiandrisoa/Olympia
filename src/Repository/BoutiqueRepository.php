<?php

namespace App\Repository;

use App\Entity\Boutique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoutiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boutique::class);
    }

    /** @return Boutique[] */
    public function findActive(?string $search = null, ?string $category = null, string $sort = 'name'): array
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.enseigne', 'e')
            ->addSelect('e')
            ->leftJoin('b.categorie', 'c')
            ->addSelect('c')
            ->andWhere('b.estActif = :active')
            ->setParameter('active', true)
            ->orderBy('e.nom', 'ASC');

        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(e.nom) LIKE :search OR LOWER(c.nom) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower(trim($search)).'%');
        }

        if ($category !== null && trim($category) !== '') {
            $qb->andWhere('c.slug = :category')->setParameter('category', $category);
        }

        if ($sort === 'category') {
            $qb->orderBy('c.nom', 'ASC')->addOrderBy('e.nom', 'ASC');
        } else {
            $qb->orderBy('e.nom', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Boutique[] */
    public function findFeatured(int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.enseigne', 'e')->addSelect('e')
            ->leftJoin('b.categorie', 'c')->addSelect('c')
            ->andWhere('b.estActif = :active')->setParameter('active', true)
            ->andWhere('b.estMisEnAvant = :featured')->setParameter('featured', true)
            ->orderBy('e.nom', 'ASC')->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function findActiveByEnseigneSlug(string $slug): ?Boutique
    {
        return $this->createQueryBuilder('b')
            ->join('b.enseigne', 'e')->addSelect('e')
            ->leftJoin('b.categorie', 'c')->addSelect('c')
            ->leftJoin('b.photoMedia', 'm')->addSelect('m')
            ->andWhere('e.slug = :slug')->setParameter('slug', $slug)
            ->andWhere('b.estActif = :active')->setParameter('active', true)
            ->getQuery()->getOneOrNullResult();
    }
}
