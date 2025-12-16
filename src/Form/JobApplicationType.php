<?php

namespace App\Form;

use App\Entity\JobApplication;
use App\Enum\JobApplicationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', TextType::class, [
                'label' => 'Company Name',
                'attr' => ['placeholder' => 'e.g., Google, Microsoft, etc.'],
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Job Title',
                'attr' => ['placeholder' => 'e.g., Senior Software Engineer'],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'attr' => ['placeholder' => 'e.g., San Francisco, Remote, etc.'],
            ])
            ->add('url', UrlType::class, [
                'label' => 'Job Posting URL',
                'required' => false,
                'attr' => ['placeholder' => 'https://...'],
            ])
            ->add('salary', TextType::class, [
                'label' => 'Salary Range',
                'required' => false,
                'attr' => ['placeholder' => 'e.g., $120k - $150k'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description / Notes',
                'attr' => [
                    'placeholder' => 'Key responsibilities, requirements, notes, etc.',
                    'rows' => 4,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => array_combine(
                    array_map(fn($case) => ucfirst($case->value), JobApplicationStatus::cases()),
                    array_map(fn($case) => $case->value, JobApplicationStatus::cases())
                ),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobApplication::class,
        ]);
    }
}
