<?php
namespace App\Repository;
use App\Entity\AbonnementNewsletter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class AbonnementNewsletterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, AbonnementNewsletter::class); }
}
