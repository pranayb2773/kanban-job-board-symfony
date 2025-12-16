<?php

namespace App\Tests\Entity;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use PHPUnit\Framework\TestCase;

class JobApplicationTest extends TestCase
{
    public function testCanCreateJobApplication(): void
    {
        $application = new JobApplication();
        $application->setCompany('Google');
        $application->setJobTitle('Software Engineer');
        $application->setLocation('Mountain View, CA');
        $application->setStatus('wishlist');
        $application->setDescription('Exciting opportunity');

        $this->assertEquals('Google', $application->getCompany());
        $this->assertEquals('Software Engineer', $application->getJobTitle());
        $this->assertEquals('Mountain View, CA', $application->getLocation());
        $this->assertEquals('wishlist', $application->getStatus());
        $this->assertEquals('Exciting opportunity', $application->getDescription());
    }

    public function testCanSetOptionalFields(): void
    {
        $application = new JobApplication();
        $application->setUrl('https://careers.google.com/job');
        $application->setSalary('$150,000 - $200,000');

        $this->assertEquals('https://careers.google.com/job', $application->getUrl());
        $this->assertEquals('$150,000 - $200,000', $application->getSalary());
    }

    public function testCanSetJobBoard(): void
    {
        $application = new JobApplication();
        $jobBoard = new JobBoard();
        $jobBoard->setName('My Board');

        $application->setJobBoard($jobBoard);

        $this->assertEquals($jobBoard, $application->getJobBoard());
    }

    public function testCanSetStatusTimestamps(): void
    {
        $application = new JobApplication();
        $createdAt = new \DateTime('2024-01-01');
        $appliedAt = new \DateTime('2024-01-02');
        $interviewedAt = new \DateTime('2024-01-10');
        $offeredAt = new \DateTime('2024-01-15');
        $rejectedAt = new \DateTime('2024-01-20');

        $application->setCreatedAt($createdAt);
        $application->setAppliedAt($appliedAt);
        $application->setInterviewedAt($interviewedAt);
        $application->setOfferedAt($offeredAt);
        $application->setRejectedAt($rejectedAt);

        $this->assertEquals($createdAt, $application->getCreatedAt());
        $this->assertEquals($appliedAt, $application->getAppliedAt());
        $this->assertEquals($interviewedAt, $application->getInterviewedAt());
        $this->assertEquals($offeredAt, $application->getOfferedAt());
        $this->assertEquals($rejectedAt, $application->getRejectedAt());
    }

    public function testTimestampsCanBeNull(): void
    {
        $application = new JobApplication();

        $this->assertNull($application->getCreatedAt());
        $this->assertNull($application->getAppliedAt());
        $this->assertNull($application->getInterviewedAt());
        $this->assertNull($application->getOfferedAt());
        $this->assertNull($application->getRejectedAt());
    }

    public function testCanChangeStatus(): void
    {
        $application = new JobApplication();
        $application->setStatus('wishlist');

        $this->assertEquals('wishlist', $application->getStatus());

        $application->setStatus('applied');
        $this->assertEquals('applied', $application->getStatus());

        $application->setStatus('interview');
        $this->assertEquals('interview', $application->getStatus());
    }
}
