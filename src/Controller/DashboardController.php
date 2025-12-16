<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Repository\JobBoardRepository;
use App\Enum\JobApplicationStatus;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly JobBoardRepository $jobBoardRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function index(): Response
    {
        $user = $this->getUser();

        $statusCounts = [];
        foreach (JobApplicationStatus::cases() as $status) {
            $statusCounts[$status->value] = 0;
        }

        $statusCountRows = $this->entityManager->createQueryBuilder()
            ->select('a.status AS status, COUNT(a.id) AS count')
            ->from(JobApplication::class, 'a')
            ->join('a.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.status')
            ->getQuery()
            ->getArrayResult();

        foreach ($statusCountRows as $row) {
            $status = (string) $row['status'];
            if (!array_key_exists($status, $statusCounts)) {
                continue;
            }

            $statusCounts[$status] = (int) $row['count'];
        }

        $totalApplications = array_sum($statusCounts);

        $activeApplications = 0;
        foreach ([
            JobApplicationStatus::WISHLIST->value,
            JobApplicationStatus::APPLIED->value,
            JobApplicationStatus::INTERVIEW->value,
            JobApplicationStatus::OFFERED->value,
        ] as $status) {
            $activeApplications += $statusCounts[$status];
        }

        // Get recent applications (last 10)
        $recentApplications = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(JobApplication::class, 'a')
            ->join('a.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Get recent job boards (last 5)
        $recentBoards = $this->jobBoardRepository->findRecentByUser($user, 5);

        $recentBoardsWithStats = [];
        foreach ($recentBoards as $board) {
            $boardStatusCounts = [];
            foreach (JobApplicationStatus::cases() as $status) {
                $boardStatusCounts[$status->value] = 0;
            }

            $recentBoardsWithStats[$board->getId()] = [
                'board' => $board,
                'total' => 0,
                'active' => 0,
                'statusCounts' => $boardStatusCounts,
            ];
        }

        if (count($recentBoardsWithStats) > 0) {
            $boardStatusRows = $this->entityManager->createQueryBuilder()
                ->select('jb.id AS boardId, a.status AS status, COUNT(a.id) AS count')
                ->from(JobApplication::class, 'a')
                ->join('a.jobBoard', 'jb')
                ->where('jb IN (:boards)')
                ->setParameter('boards', $recentBoards)
                ->groupBy('jb.id, a.status')
                ->getQuery()
                ->getArrayResult();

            foreach ($boardStatusRows as $row) {
                $boardId = (int) $row['boardId'];
                $status = (string) $row['status'];
                $count = (int) $row['count'];

                if (!isset($recentBoardsWithStats[$boardId])) {
                    continue;
                }

                if (!array_key_exists($status, $recentBoardsWithStats[$boardId]['statusCounts'])) {
                    continue;
                }

                $recentBoardsWithStats[$boardId]['statusCounts'][$status] = $count;
                $recentBoardsWithStats[$boardId]['total'] += $count;

                if (in_array($status, [
                    JobApplicationStatus::WISHLIST->value,
                    JobApplicationStatus::APPLIED->value,
                    JobApplicationStatus::INTERVIEW->value,
                    JobApplicationStatus::OFFERED->value,
                ], true)) {
                    $recentBoardsWithStats[$boardId]['active'] += $count;
                }
            }
        }

        $since7Days = new DateTimeImmutable('-7 days');

        $applicationsLast7Days = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(JobApplication::class, 'a')
            ->join('a.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since7Days)
            ->getQuery()
            ->getSingleScalarResult();

        $staleThreshold = new DateTimeImmutable('-14 days');
        $followUpStatuses = [
            JobApplicationStatus::APPLIED->value,
            JobApplicationStatus::INTERVIEW->value,
            JobApplicationStatus::OFFERED->value,
        ];

        $lastTouchExpr = '(CASE
            WHEN a.offeredAt IS NOT NULL THEN a.offeredAt
            WHEN a.interviewedAt IS NOT NULL THEN a.interviewedAt
            WHEN a.appliedAt IS NOT NULL THEN a.appliedAt
            ELSE a.createdAt
        END)';

        $staleActiveCount = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(JobApplication::class, 'a')
            ->join('a.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere($lastTouchExpr.' < :threshold')
            ->setParameter('user', $user)
            ->setParameter('statuses', $followUpStatuses)
            ->setParameter('threshold', $staleThreshold)
            ->getQuery()
            ->getSingleScalarResult();

        $latestActivityExpr = '(CASE
            WHEN a.rejectedAt IS NOT NULL THEN a.rejectedAt
            WHEN a.offeredAt IS NOT NULL THEN a.offeredAt
            WHEN a.interviewedAt IS NOT NULL THEN a.interviewedAt
            WHEN a.appliedAt IS NOT NULL THEN a.appliedAt
            ELSE a.createdAt
        END)';

        $latestActivityRaw = $this->entityManager->createQueryBuilder()
            ->select('MAX('.$latestActivityExpr.') AS latestActivityAt')
            ->from(JobApplication::class, 'a')
            ->join('a.jobBoard', 'jb')
            ->where('jb.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $latestActivityAt = null;
        if ($latestActivityRaw instanceof DateTimeInterface) {
            $latestActivityAt = $latestActivityRaw;
        } elseif (is_string($latestActivityRaw) && $latestActivityRaw !== '') {
            $latestActivityAt = new DateTimeImmutable($latestActivityRaw);
        }

        return $this->render('dashboard/index.html.twig', [
            'has_job_boards' => $this->jobBoardRepository->hasJobBoards($user),
            'job_boards_count' => $this->jobBoardRepository->countByUser($user),
            'total_applications' => $totalApplications,

            // Recent data
            'recent_applications' => $recentApplications,
            'recent_boards_with_stats' => array_values($recentBoardsWithStats),
            'applications_last_7_days' => $applicationsLast7Days,
            'stale_active_count' => $staleActiveCount,
            'latest_activity_at' => $latestActivityAt,

            // Active applications (not rejected or accepted)
            'active_applications' => $activeApplications,
        ]);
    }
}
