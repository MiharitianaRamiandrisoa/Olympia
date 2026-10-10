<?php

namespace App\Repository;

use App\Entity\Oeuvre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OeuvreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oeuvre::class);
    }

    /** @return Oeuvre[] */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.imageMedia', 'image')->addSelect('image')
            ->leftJoin('o.artiste', 'artiste')->addSelect('artiste')
            ->leftJoin('o.categorie', 'categorie')->addSelect('categorie')
            ->leftJoin('o.exposition', 'exposition')->addSelect('exposition')
            ->andWhere('o.estActive = :active')
            ->setParameter('active', true)
            ->orderBy('o.ordre', 'ASC')
            ->addOrderBy('o.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
