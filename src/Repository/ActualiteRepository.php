<?php

namespace App\Repository;

use App\Entity\Actualite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ActualiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actualite::class);
    }

    /** @return Actualite[] */
    public function findActive(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.imageMedia', 'image')->addSelect('image')
            ->andWhere('a.estActif = :active')->setParameter('active', true)
            ->andWhere('a.datePublication <= :now')->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.datePublication', 'DESC');

        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(a.titre) LIKE :search OR LOWER(a.chapeau) LIKE :search OR LOWER(a.contenu) LIKE :search OR LOWER(a.categorie) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower(trim($search)).'%');
        }

        return $qb->getQuery()->getResult();
    }
}
