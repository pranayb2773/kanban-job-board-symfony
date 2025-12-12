<?php

namespace App\Twig;

use App\Repository\JobBoardRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security  $security,
        private readonly JobBoardRepository $jobBoardRepository
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_recent_job_boards', [$this, 'getRecentJobBoards']),
        ];
    }

    public function getRecentJobBoards(): array
    {
        $user = $this->security->getUser();

        if (!$user) {
            error_log('NavigationExtension: No user found');
            return [];
        }

        // Fetch latest 5 job boards using repository (DB-side ordering + limit)
        $result = $this->jobBoardRepository->findRecentByUser($user, 5);
        error_log('NavigationExtension: Returning ' . count($result) . ' job boards from repository');

        return $result;
    }
}
