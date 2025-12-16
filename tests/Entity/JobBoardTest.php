<?php

namespace App\Tests\Entity;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class JobBoardTest extends TestCase
{
    public function testCanCreateJobBoard(): void
    {
        $jobBoard = new JobBoard();
        $jobBoard->setName('Software Engineering Jobs');
        $jobBoard->setDescription('Tracking my applications');

        $this->assertEquals('Software Engineering Jobs', $jobBoard->getName());
        $this->assertEquals('Tracking my applications', $jobBoard->getDescription());
    }

    public function testCreatedAtIsSetAutomatically(): void
    {
        $jobBoard = new JobBoard();

        $this->assertInstanceOf(\DateTimeInterface::class, $jobBoard->getCreatedAt());
        $this->assertEqualsWithDelta(new \DateTime(), $jobBoard->getCreatedAt(), 2);
    }

    public function testCanSetUser(): void
    {
        $jobBoard = new JobBoard();
        $user = new User();
        $user->setEmail('user@example.com');

        $jobBoard->setUser($user);

        $this->assertEquals($user, $jobBoard->getUser());
    }

    public function testCanAddJobApplication(): void
    {
        $jobBoard = new JobBoard();
        $application = new JobApplication();
        $application->setCompany('Google');
        $application->setJobTitle('Software Engineer');

        $jobBoard->addJobApplication($application);

        $this->assertCount(1, $jobBoard->getJobApplications());
        $this->assertTrue($jobBoard->getJobApplications()->contains($application));
        $this->assertEquals($jobBoard, $application->getJobBoard());
    }

    public function testCanRemoveJobApplication(): void
    {
        $jobBoard = new JobBoard();
        $application = new JobApplication();
        $application->setCompany('Google');

        $jobBoard->addJobApplication($application);
        $this->assertCount(1, $jobBoard->getJobApplications());

        $jobBoard->removeJobApplication($application);
        $this->assertCount(0, $jobBoard->getJobApplications());
        $this->assertNull($application->getJobBoard());
    }

    public function testDoesNotAddDuplicateApplications(): void
    {
        $jobBoard = new JobBoard();
        $application = new JobApplication();
        $application->setCompany('Google');

        $jobBoard->addJobApplication($application);
        $jobBoard->addJobApplication($application); // Add same application twice

        $this->assertCount(1, $jobBoard->getJobApplications());
    }

    public function testJobBoardStartsWithEmptyApplicationsCollection(): void
    {
        $jobBoard = new JobBoard();

        $this->assertCount(0, $jobBoard->getJobApplications());
    }
}
