<?php

namespace App\Repository;

use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tenant::class);
    }

    public function findBySlugOrDomain(string $value): ?Tenant
    {
        return $this->createQueryBuilder('t')
            ->where('t.slug = :val OR t.customDomain = :val')
            ->andWhere('t.isActive = true')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
