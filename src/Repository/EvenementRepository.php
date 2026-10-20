<?php
namespace App\Repository;
use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Evenement::class); }
    /** @return Evenement[] */
    public function findActive(?string $category = null): array
    {
        $qb = $this->createQueryBuilder('e')->leftJoin('e.enseigne', 'brand')->addSelect('brand')
            ->leftJoin('e.categorie', 'c')->addSelect('c')
            ->andWhere('e.estActif = :active')->setParameter('active', true)
            ->andWhere('e.dateDebut >= :today')->setParameter('today', new \DateTimeImmutable('today'));
        if ($category !== null && trim($category) !== '') {
            $qb->andWhere('c.slug = :category')->setParameter('category', $category);
        }
        return $qb->orderBy('e.dateDebut', 'ASC')->getQuery()->getResult();
    }

    /** @return Evenement[] */
    public function findFeatured(): array
    {
        return $this->createQueryBuilder('e')->leftJoin('e.enseigne', 'brand')->addSelect('brand')
            ->leftJoin('e.categorie', 'c')->addSelect('c')
            ->andWhere('e.estActif = :active')->setParameter('active', true)
            ->andWhere('e.estMisEnAvant = :featured')->setParameter('featured', true)
            ->andWhere('e.dateDebut >= :today')->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('e.dateDebut', 'ASC')->getQuery()->getResult();
    }

    /** @return Evenement[] */
    public function findPublicUpcoming(?string $category = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.enseigne', 'brand')->addSelect('brand')
            ->leftJoin('e.categorie', 'c')->addSelect('c')
            ->andWhere('e.estActif = :active')->setParameter('active', true)
            ->andWhere('e.dateDebut >= :now')->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.dateDebut', 'ASC');

        if ($category !== null && trim($category) !== '') {
            $qb->andWhere('c.slug = :category')->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function findForUpdate(int $id): ?Evenement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :id')->setParameter('id', $id)
            ->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getOneOrNullResult();
    }
}
