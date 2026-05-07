<?php

namespace App\Repository;

use App\Entity\Lead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->setParameter('status', $status)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAssignedTo(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.assignedTo = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.priority', 'DESC')
            ->addOrderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $term): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.fullName LIKE :term OR l.phone LIKE :term OR l.email LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
