<?php

namespace App\Repository;

use App\Entity\JobBoard;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobBoard>
 */
class JobBoardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobBoard::class);
    }

    /**
     * Find the most recent job boards for a given user.
     *
     * @param User $user  The owner of the job boards
     * @param int  $limit Max number of results to return (default 5)
     * @return JobBoard[]
     */
    public function findRecentByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.user = :user')
            ->setParameter('user', $user)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function hasJobBoards(User $user): bool
    {
        return $this->countByUser($user) > 0;
    }

    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
