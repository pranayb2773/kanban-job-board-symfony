<?php

namespace App\Tests\Repository;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use App\Enum\JobApplicationStatus;
use App\Repository\JobApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JobApplicationRepositoryTest extends KernelTestCase
{
    private JobApplicationRepository $repository;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(JobApplication::class);

        // Clean up database
        $this->entityManager->createQuery('DELETE FROM App\Entity\JobApplication')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\JobBoard')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function createUser(string $email = 'test@example.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName('Test User');
        $user->setPassword('hashedpassword');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createJobBoard(User $user, string $name): JobBoard
    {
        $jobBoard = new JobBoard();
        $jobBoard->setName($name);
        $jobBoard->setUser($user);

        $this->entityManager->persist($jobBoard);
        $this->entityManager->flush();

        return $jobBoard;
    }

    private function createJobApplication(
        JobBoard $jobBoard,
        string $company,
        string $status = 'wishlist'
    ): JobApplication {
        $application = new JobApplication();
        $application->setCompany($company);
        $application->setJobTitle('Software Engineer');
        $application->setLocation('Remote');
        $application->setDescription('Test description');
        $application->setStatus($status);
        $application->setJobBoard($jobBoard);
        $application->setCreatedAt(new \DateTime());

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    public function testCountByUser(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $this->assertEquals(0, $this->repository->countByUser($user));

        $this->createJobApplication($board, 'Google');
        $this->createJobApplication($board, 'Microsoft');
        $this->createJobApplication($board, 'Amazon');

        $this->assertEquals(3, $this->repository->countByUser($user));
    }

    public function testCountByUserOnlyCountsUserApplications(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $board1 = $this->createJobBoard($user1, 'User 1 Board');
        $board2 = $this->createJobBoard($user2, 'User 2 Board');

        $this->createJobApplication($board1, 'Google');
        $this->createJobApplication($board1, 'Microsoft');
        $this->createJobApplication($board2, 'Amazon');

        $this->assertEquals(2, $this->repository->countByUser($user1));
        $this->assertEquals(1, $this->repository->countByUser($user2));
    }

    public function testCountByUserAndStatus(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $this->createJobApplication($board, 'Google', 'wishlist');
        $this->createJobApplication($board, 'Microsoft', 'applied');
        $this->createJobApplication($board, 'Amazon', 'applied');
        $this->createJobApplication($board, 'Meta', 'interview');

        $this->assertEquals(1, $this->repository->countByUserAndStatus($user, 'wishlist'));
        $this->assertEquals(2, $this->repository->countByUserAndStatus($user, 'applied'));
        $this->assertEquals(1, $this->repository->countByUserAndStatus($user, 'interview'));
        $this->assertEquals(0, $this->repository->countByUserAndStatus($user, 'offered'));
    }

    public function testFindByBoardGroupedByStatus(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $this->createJobApplication($board, 'Google', 'wishlist');
        $this->createJobApplication($board, 'Microsoft', 'wishlist');
        $this->createJobApplication($board, 'Amazon', 'applied');
        $this->createJobApplication($board, 'Meta', 'interview');

        $grouped = $this->repository->findByBoardGroupedByStatus($board);

        // Should have all status keys
        $this->assertArrayHasKey('wishlist', $grouped);
        $this->assertArrayHasKey('applied', $grouped);
        $this->assertArrayHasKey('interview', $grouped);
        $this->assertArrayHasKey('rejected', $grouped);
        $this->assertArrayHasKey('accepted', $grouped);
        $this->assertArrayHasKey('offered', $grouped);

        // Check counts
        $this->assertCount(2, $grouped['wishlist']);
        $this->assertCount(1, $grouped['applied']);
        $this->assertCount(1, $grouped['interview']);
        $this->assertCount(0, $grouped['rejected']);
        $this->assertCount(0, $grouped['accepted']);
        $this->assertCount(0, $grouped['offered']);

        // Check application names
        $this->assertEquals('Google', $grouped['wishlist'][0]->getCompany());
        $this->assertEquals('Microsoft', $grouped['wishlist'][1]->getCompany());
        $this->assertEquals('Amazon', $grouped['applied'][0]->getCompany());
    }

    public function testFindByBoardGroupedByStatusReturnsEmptyArrays(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $grouped = $this->repository->findByBoardGroupedByStatus($board);

        // All status arrays should exist but be empty
        foreach (JobApplicationStatus::cases() as $status) {
            $this->assertArrayHasKey($status->value, $grouped);
            $this->assertIsArray($grouped[$status->value]);
            $this->assertCount(0, $grouped[$status->value]);
        }
    }

    public function testFindByBoardGroupedByStatusOrdersByCreatedAt(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        // Create applications with different dates
        $app1 = $this->createJobApplication($board, 'Google', 'wishlist');
        $app1->setCreatedAt(new \DateTime('-10 days'));
        $this->entityManager->flush();

        $app2 = $this->createJobApplication($board, 'Microsoft', 'wishlist');
        $app2->setCreatedAt(new \DateTime('-5 days'));
        $this->entityManager->flush();

        $app3 = $this->createJobApplication($board, 'Amazon', 'wishlist');
        $app3->setCreatedAt(new \DateTime('-1 day'));
        $this->entityManager->flush();

        $grouped = $this->repository->findByBoardGroupedByStatus($board);

        // Should be ordered by createdAt DESC (newest first)
        $this->assertEquals('Amazon', $grouped['wishlist'][0]->getCompany());
        $this->assertEquals('Microsoft', $grouped['wishlist'][1]->getCompany());
        $this->assertEquals('Google', $grouped['wishlist'][2]->getCompany());
    }

    public function testFindOneByIdAndUser(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google');

        $foundApplication = $this->repository->findOneByIdAndUser($application->getId(), $user);

        $this->assertNotNull($foundApplication);
        $this->assertEquals($application->getId(), $foundApplication->getId());
        $this->assertEquals('Google', $foundApplication->getCompany());
    }

    public function testFindOneByIdAndUserReturnsNullForDifferentUser(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $board = $this->createJobBoard($user1, 'User 1 Board');
        $application = $this->createJobApplication($board, 'Google');

        $foundApplication = $this->repository->findOneByIdAndUser($application->getId(), $user2);

        $this->assertNull($foundApplication);
    }

    public function testFindOneByIdAndUserReturnsNullForNonExistent(): void
    {
        $user = $this->createUser();

        $foundApplication = $this->repository->findOneByIdAndUser(999999, $user);

        $this->assertNull($foundApplication);
    }
}
