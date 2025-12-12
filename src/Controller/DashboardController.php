<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Enum\JobApplicationStatus;
use App\Repository\JobApplicationRepository;
use App\Repository\JobBoardRepository;
use App\Entity\JobBoard;
use App\Form\JobBoardType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly JobBoardRepository $jobBoardRepository,
        private readonly JobApplicationRepository $jobApplicationRepository
    ) {
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'has_job_boards' => $this->jobBoardRepository->hasJobBoards($user),
            'job_boards' => $this->jobBoardRepository->countByUser($user),
            'total_jobs' => $this->jobApplicationRepository->countByUser($user),
            'applied_jobs' => $this->jobApplicationRepository->countByUserAndStatus($user, JobApplicationStatus::APPLIED->value),
            'interview_jobs' => $this->jobApplicationRepository->countByUserAndStatus($user, JobApplicationStatus::INTERVIEW->value),
        ]);
    }
}
