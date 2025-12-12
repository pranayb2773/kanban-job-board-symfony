<?php

namespace App\Controller;

use App\Entity\JobBoard;
use App\Form\JobBoardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jobBoard = new JobBoard();
        $form = $this->createForm(JobBoardType::class, $jobBoard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobBoard->setUser($this->getUser());

            $entityManager->persist($jobBoard);
            $entityManager->flush();

            $this->addFlash('success', 'Job board created successfully!');

            return $this->redirectToRoute('app_dashboard');
        }

        // If form has errors, redirect back with errors
        return $this->redirectToRoute('app_dashboard');
    }
}
