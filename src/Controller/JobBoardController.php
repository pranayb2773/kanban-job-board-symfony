<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Enum\JobApplicationStatus;
use App\Form\JobApplicationType;
use App\Form\JobBoardType;
use App\Repository\JobApplicationRepository;
use App\Repository\JobBoardRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class JobBoardController extends AbstractController
{
    public function modal(): Response
    {
        // Build the Job Board creation form for the modal (used from header)
        $jobBoard = new JobBoard();
        $form = $this->createForm(JobBoardType::class, $jobBoard, [
            'action' => $this->generateUrl('app_job_board_create'),
            'method' => 'POST',
        ]);

        return $this->render('partials/_job_board_create_modal.html.twig', [
            'job_board_form' => $form->createView(),
        ]);
    }

    public function editModal(int $id, JobBoardRepository $jobBoardRepository): Response
    {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$jobBoard) {
            throw $this->createNotFoundException('Job board not found');
        }

        $form = $this->createForm(JobBoardType::class, $jobBoard, [
            'action' => $this->generateUrl('app_job_board_update', ['id' => $id]),
            'method' => 'POST',
        ]);

        return $this->render('partials/_job_board_edit_modal.html.twig', [
            'job_board_form' => $form->createView(),
            'job_board' => $jobBoard,
        ]);
    }

    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $jobBoard = new JobBoard();
        $form = $this->createForm(JobBoardType::class, $jobBoard, [
            'action' => $this->generateUrl('app_job_board_create'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobBoard->setUser($this->getUser());

            $entityManager->persist($jobBoard);
            $entityManager->flush();

            $freshForm = $this->createForm(JobBoardType::class, new JobBoard(), [
                'action' => $this->generateUrl('app_job_board_create'),
                'method' => 'POST',
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Job board created successfully!',
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Please fix the form errors and try again.',
            'modal' => $this->renderView('partials/_job_board_create_modal.html.twig', [
                'job_board_form' => $form->createView(),
            ]),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function update(
        int $id,
        Request $request,
        JobBoardRepository $jobBoardRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$jobBoard) {
            return $this->json(['error' => 'Job board not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(JobBoardType::class, $jobBoard, [
            'action' => $this->generateUrl('app_job_board_update', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Job board updated successfully!',
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Please fix the form errors and try again.',
            'modal' => $this->renderView('partials/_job_board_edit_modal.html.twig', [
                'job_board_form' => $form->createView(),
                'job_board' => $jobBoard,
            ]),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function delete(
        int $id,
        JobBoardRepository $jobBoardRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$jobBoard) {
            return $this->json(['error' => 'Job board not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($jobBoard);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Job board deleted successfully',
        ]);
    }

    public function kanban(
        int $id,
        JobBoardRepository $jobBoardRepository,
        JobApplicationRepository $jobApplicationRepository
    ): Response {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$jobBoard) {
            throw $this->createNotFoundException('Job board not found');
        }

        $applicationsByStatus = $jobApplicationRepository->findByBoardGroupedByStatus($jobBoard);

        return $this->render('job_board/kanban.html.twig', [
            'job_board' => $jobBoard,
            'applications_by_status' => $applicationsByStatus,
            'statuses' => JobApplicationStatus::cases(),
        ]);
    }

    public function applicationDetails(
        int $id,
        JobApplicationRepository $jobApplicationRepository
    ): JsonResponse {
        $application = $jobApplicationRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$application) {
            return $this->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $application->getId(),
            'company' => $application->getCompany(),
            'jobTitle' => $application->getJobTitle(),
            'location' => $application->getLocation(),
            'url' => $application->getUrl(),
            'salary' => $application->getSalary(),
            'description' => $application->getDescription(),
            'status' => $application->getStatus(),
            'createdAt' => $application->getCreatedAt()?->format('Y-m-d H:i:s'),
            'appliedAt' => $application->getAppliedAt()?->format('Y-m-d H:i:s'),
            'interviewedAt' => $application->getInterviewedAt()?->format('Y-m-d H:i:s'),
            'offeredAt' => $application->getOfferedAt()?->format('Y-m-d H:i:s'),
            'rejectedAt' => $application->getRejectedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function updateStatus(
        int $id,
        Request $request,
        JobApplicationRepository $jobApplicationRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $application = $jobApplicationRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$application) {
            return $this->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        // Validate status
        $validStatuses = array_map(fn($case) => $case->value, JobApplicationStatus::cases());
        if (!in_array($newStatus, $validStatuses)) {
            return $this->json(['error' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $application->setStatus($newStatus);

        // Update timestamp fields based on status
        $now = new DateTime();
        match ($newStatus) {
            JobApplicationStatus::APPLIED->value => $application->setAppliedAt($now),
            JobApplicationStatus::INTERVIEW->value => $application->setInterviewedAt($now),
            JobApplicationStatus::OFFERED->value => $application->setOfferedAt($now),
            JobApplicationStatus::REJECTED->value => $application->setRejectedAt($now),
            default => null,
        };

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'application' => [
                'id' => $application->getId(),
                'status' => $application->getStatus(),
            ],
        ]);
    }

    public function applicationModal(
        int $boardId,
        JobBoardRepository $jobBoardRepository
    ): Response {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($boardId, $this->getUser());

        if (!$jobBoard) {
            throw $this->createNotFoundException('Job board not found');
        }

        $application = new JobApplication();
        $application->setJobBoard($jobBoard);
        $application->setCreatedAt(new DateTime());

        $form = $this->createForm(JobApplicationType::class, $application, [
            'action' => $this->generateUrl('app_job_application_create', ['boardId' => $boardId]),
            'method' => 'POST',
        ]);

        return $this->render('partials/_job_application_create_modal.html.twig', [
            'job_application_form' => $form->createView(),
            'job_board' => $jobBoard,
        ]);
    }

    public function createApplication(
        int $boardId,
        Request $request,
        JobBoardRepository $jobBoardRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $jobBoard = $jobBoardRepository->findOneByIdAndUser($boardId, $this->getUser());

        if (!$jobBoard) {
            return $this->json(['error' => 'Job board not found'], Response::HTTP_NOT_FOUND);
        }

        $application = new JobApplication();
        $application->setJobBoard($jobBoard);
        $application->setCreatedAt(new DateTime());

        $form = $this->createForm(JobApplicationType::class, $application, [
            'action' => $this->generateUrl('app_job_application_create', ['boardId' => $boardId]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($application);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Job application added successfully!',
                'application' => [
                    'id' => $application->getId(),
                    'status' => $application->getStatus(),
                ],
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Please fix the form errors and try again.',
            'modal' => $this->renderView('partials/_job_application_create_modal.html.twig', [
                'job_application_form' => $form->createView(),
                'job_board' => $jobBoard,
            ]),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function editApplicationModal(
        int $id,
        JobApplicationRepository $jobApplicationRepository
    ): Response {
        $application = $jobApplicationRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$application) {
            throw $this->createNotFoundException('Application not found');
        }

        $form = $this->createForm(JobApplicationType::class, $application, [
            'action' => $this->generateUrl('app_job_application_update', ['id' => $id]),
            'method' => 'POST',
        ]);

        return $this->render('partials/_job_application_edit_modal.html.twig', [
            'job_application_form' => $form->createView(),
            'application' => $application,
        ]);
    }

    public function updateApplication(
        int $id,
        Request $request,
        JobApplicationRepository $jobApplicationRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $application = $jobApplicationRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$application) {
            return $this->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(JobApplicationType::class, $application, [
            'action' => $this->generateUrl('app_job_application_update', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Application updated successfully!',
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Please fix the form errors and try again.',
            'modal' => $this->renderView('partials/_job_application_edit_modal.html.twig', [
                'job_application_form' => $form->createView(),
                'application' => $application,
            ]),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function deleteApplication(
        int $id,
        JobApplicationRepository $jobApplicationRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $application = $jobApplicationRepository->findOneByIdAndUser($id, $this->getUser());

        if (!$application) {
            return $this->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($application);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Application deleted successfully',
        ]);
    }
}
