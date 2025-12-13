<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordChangeType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    #[Route('/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $profileForm = $this->createForm(ProfileType::class, $user);
        $passwordForm = $this->createForm(PasswordChangeType::class);

        return $this->render('user/profile.html.twig', [
            'profile_form' => $profileForm,
            'password_form' => $passwordForm,
        ]);
    }

    #[Route('/profile/update', name: 'app_user_profile_update', methods: ['POST'])]
    public function updateProfile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->entityManager->flush();
            $this->security->login($user);

            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_user_profile');
        }

        // Refresh entity to revert changes on validation failure
        $this->entityManager->refresh($user);

        $passwordForm = $this->createForm(PasswordChangeType::class);

        return $this->render('user/profile.html.twig', [
            'profile_form' => $form,
            'password_form' => $passwordForm,
        ]);
    }

    #[Route('/profile/change-password', name: 'app_user_change_password', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(PasswordChangeType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(
                    new FormError('Current password is incorrect.')
                );
            } else {
                $this->changeUserPassword($user, $newPassword, $passwordHasher);
                $this->addFlash('success', 'Password changed successfully!');

                return $this->redirectToRoute('app_user_profile');
            }
        }

        $profileForm = $this->createForm(ProfileType::class, $user);

        return $this->render('user/profile.html.twig', [
            'profile_form' => $profileForm,
            'password_form' => $form,
        ]);
    }

    private function changeUserPassword(User $user, string $newPassword, UserPasswordHasherInterface $passwordHasher): void
    {
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();
    }
}
