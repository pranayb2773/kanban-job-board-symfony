<?php

namespace App\Repository;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use App\Enum\JobApplicationStatus;
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

    /**
     * Find job applications for a specific board, grouped by status
     *
     * @return array<string, JobApplication[]>
     */
    public function findByBoardGroupedByStatus(JobBoard $jobBoard): array
    {
        $applications = $this->createQueryBuilder('ja')
            ->where('ja.jobBoard = :board')
            ->setParameter('board', $jobBoard)
            ->orderBy('ja.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Initialize array with all status values
        $grouped = [];
        foreach (JobApplicationStatus::cases() as $status) {
            $grouped[$status->value] = [];
        }

        // Group applications by status
        foreach ($applications as $application) {
            $status = $application->getStatus();
            if (isset($grouped[$status])) {
                $grouped[$status][] = $application;
            }
        }

        return $grouped;
    }

    /**
     * Find a single application with board ownership verification
     */
    public function findOneByIdAndUser(int $id, User $user): ?JobApplication
    {
        return $this->createQueryBuilder('ja')
            ->join('ja.jobBoard', 'jb')
            ->where('ja.id = :id')
            ->andWhere('jb.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
