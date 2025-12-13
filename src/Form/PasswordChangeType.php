<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Enter your current password',
                    'autocomplete' => 'current-password',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your current password'),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'New Password',
                    'attr' => [
                        'placeholder' => 'Enter new password',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm New Password',
                    'attr' => [
                        'placeholder' => 'Confirm new password',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter a new password'),
                    new Assert\Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        maxMessage: 'Your password should not exceed {{ limit }} characters'
                    ),
                    new Assert\NotCompromisedPassword(
                        message: 'This password has been leaked in a data breach. Please choose a different password.'
                    ),
                    new Assert\PasswordStrength(
                        minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
                        message: 'Your password is too weak. Please use a stronger password with a mix of letters, numbers, and special characters.'
                    ),
                ],
            ])
        ;
    }
}
