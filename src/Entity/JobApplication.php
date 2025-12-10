<?php

namespace App\Entity;

use App\Repository\JobApplicationRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobApplicationRepository::class)]
class JobApplication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Company name is required.')]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Job title is required.')]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'Please enter a valid URL.')]
    private ?string $url = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $salary = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Location is required.')]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['wishlist', 'applied', 'interviewed', 'offered', 'rejected'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'JobApplications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobBoard $jobBoard = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $appliedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $interviewedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $offeredAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $rejectedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(?string $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getJobBoard(): ?JobBoard
    {
        return $this->jobBoard;
    }

    public function setJobBoard(?JobBoard $jobBoard): static
    {
        $this->jobBoard = $jobBoard;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAppliedAt(): ?DateTimeInterface
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(?DateTimeInterface $appliedAt): static
    {
        $this->appliedAt = $appliedAt;

        return $this;
    }

    public function getInterviewedAt(): ?DateTimeInterface
    {
        return $this->interviewedAt;
    }

    public function setInterviewedAt(?DateTimeInterface $interviewedAt): static
    {
        $this->interviewedAt = $interviewedAt;

        return $this;
    }

    public function getOfferedAt(): ?DateTimeInterface
    {
        return $this->offeredAt;
    }

    public function setOfferedAt(?DateTimeInterface $offeredAt): static
    {
        $this->offeredAt = $offeredAt;

        return $this;
    }

    public function getRejectedAt(): ?DateTimeInterface
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?DateTimeInterface $rejectedAt): static
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }
}
