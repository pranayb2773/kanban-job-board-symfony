<?php

namespace App\Repository;

use App\Entity\JobApplication;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobApplication>
 */
class JobApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobApplication::class);
    }

    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('ja')
            ->select('COUNT(ja.id)')
            ->join('ja.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByUserAndStatus(User $user, string $status): int
    {
        return $this->createQueryBuilder('ja')
            ->select('COUNT(ja.id)')
            ->join('ja.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->andWhere('ja.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
