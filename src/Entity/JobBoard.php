<?php

namespace App\Entity;

use App\Repository\JobBoardRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobBoardRepository::class)]
class JobBoard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Board name is required.')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'jobBoards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, JobApplication>
     */
    #[ORM\OneToMany(targetEntity: JobApplication::class, mappedBy: 'jobBoard', orphanRemoval: true)]
    private Collection $JobApplications;

    public function __construct()
    {
        $this->JobApplications = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, JobApplication>
     */
    public function getJobApplications(): Collection
    {
        return $this->JobApplications;
    }

    public function addJobApplication(JobApplication $jobApplication): static
    {
        if (!$this->JobApplications->contains($jobApplication)) {
            $this->JobApplications->add($jobApplication);
            $jobApplication->setJobBoard($this);
        }

        return $this;
    }

    public function removeJobApplication(JobApplication $jobApplication): static
    {
        if ($this->JobApplications->removeElement($jobApplication)) {
            // set the owning side to null (unless already changed)
            if ($jobApplication->getJobBoard() === $this) {
                $jobApplication->setJobBoard(null);
            }
        }

        return $this;
    }
}
