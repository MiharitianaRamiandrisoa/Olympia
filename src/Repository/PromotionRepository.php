<?php
namespace App\Repository;
use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Promotion::class); }
    /** @return Promotion[] */
    public function findActive(?string $category = null): array
    {
        $qb = $this->createQueryBuilder('p')->join('p.enseigne', 'e')->addSelect('e')
            ->leftJoin('p.categorie', 'c')->addSelect('c')
            ->andWhere('p.estActif = :active')->setParameter('active', true)
            ->andWhere('p.dateDebut <= :now')->setParameter('now', new \DateTimeImmutable())
            ->andWhere('p.dateFin IS NULL OR p.dateFin >= :today')->setParameter('today', new \DateTimeImmutable('today'));
        if ($category !== null && trim($category) !== '') {
            $qb->andWhere('c.slug = :category')->setParameter('category', $category);
        }
        return $qb->orderBy('p.dateDebut', 'DESC')->getQuery()->getResult();
    }

    /** @return Promotion[] */
    public function findFeatured(): array
    {
        return $this->createQueryBuilder('p')->join('p.enseigne', 'e')->addSelect('e')
            ->leftJoin('p.categorie', 'c')->addSelect('c')
            ->andWhere('p.estActif = :active')->setParameter('active', true)
            ->andWhere('p.estMisEnAvant = :featured')->setParameter('featured', true)
            ->andWhere('p.dateDebut <= :now')->setParameter('now', new \DateTimeImmutable())
            ->andWhere('p.dateFin IS NULL OR p.dateFin >= :today')->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('p.dateDebut', 'DESC')->getQuery()->getResult();
    }
}
