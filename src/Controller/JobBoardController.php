<?php

namespace App\Controller;

use App\Entity\JobBoard;
use App\Form\JobBoardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/job-board')]
#[IsGranted('ROLE_USER')]
class JobBoardController extends AbstractController
{
    #[Route('/_fragment/job-board-modal', name: 'fragment_job_board_modal', methods: ['GET'])]
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

    #[Route('/create', name: 'app_job_board_create', methods: ['POST'])]
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
}
