<?php

namespace App\Tests\Repository;

use App\Entity\JobBoard;
use App\Entity\User;
use App\Repository\JobBoardRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JobBoardRepositoryTest extends KernelTestCase
{
    private JobBoardRepository $repository;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(JobBoard::class);

        // Clean up database
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

    private function createJobBoard(User $user, string $name, \DateTime $createdAt = null): JobBoard
    {
        $jobBoard = new JobBoard();
        $jobBoard->setName($name);
        $jobBoard->setUser($user);

        if ($createdAt) {
            // Use reflection to set createdAt for testing
            $reflection = new \ReflectionClass($jobBoard);
            $property = $reflection->getProperty('createdAt');
            $property->setValue($jobBoard, $createdAt);
        }

        $this->entityManager->persist($jobBoard);
        $this->entityManager->flush();

        return $jobBoard;
    }

    public function testFindRecentByUser(): void
    {
        $user = $this->createUser();

        // Create job boards with different dates
        $board1 = $this->createJobBoard($user, 'Board 1', new \DateTime('-10 days'));
        $board2 = $this->createJobBoard($user, 'Board 2', new \DateTime('-5 days'));
        $board3 = $this->createJobBoard($user, 'Board 3', new \DateTime('-1 day'));

        $recentBoards = $this->repository->findRecentByUser($user, 2);

        $this->assertCount(2, $recentBoards);
        $this->assertEquals('Board 3', $recentBoards[0]->getName());
        $this->assertEquals('Board 2', $recentBoards[1]->getName());
    }

    public function testFindRecentByUserDefaultLimit(): void
    {
        $user = $this->createUser();

        // Create 7 job boards
        for ($i = 1; $i <= 7; $i++) {
            $this->createJobBoard($user, "Board $i", new \DateTime("-$i days"));
        }

        $recentBoards = $this->repository->findRecentByUser($user);

        $this->assertCount(5, $recentBoards); // Default limit is 5
    }

    public function testFindRecentByUserOnlyReturnsUserBoards(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $this->createJobBoard($user1, 'User 1 Board');
        $this->createJobBoard($user2, 'User 2 Board');

        $user1Boards = $this->repository->findRecentByUser($user1);

        $this->assertCount(1, $user1Boards);
        $this->assertEquals('User 1 Board', $user1Boards[0]->getName());
    }

    public function testHasJobBoards(): void
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->hasJobBoards($user));

        $this->createJobBoard($user, 'My Board');

        $this->assertTrue($this->repository->hasJobBoards($user));
    }

    public function testCountByUser(): void
    {
        $user = $this->createUser();

        $this->assertEquals(0, $this->repository->countByUser($user));

        $this->createJobBoard($user, 'Board 1');
        $this->createJobBoard($user, 'Board 2');
        $this->createJobBoard($user, 'Board 3');

        $this->assertEquals(3, $this->repository->countByUser($user));
    }

    public function testCountByUserOnlyCountsUserBoards(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $this->createJobBoard($user1, 'User 1 Board 1');
        $this->createJobBoard($user1, 'User 1 Board 2');
        $this->createJobBoard($user2, 'User 2 Board 1');

        $this->assertEquals(2, $this->repository->countByUser($user1));
        $this->assertEquals(1, $this->repository->countByUser($user2));
    }

    public function testFindOneByIdAndUser(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $foundBoard = $this->repository->findOneByIdAndUser($board->getId(), $user);

        $this->assertNotNull($foundBoard);
        $this->assertEquals($board->getId(), $foundBoard->getId());
        $this->assertEquals('My Board', $foundBoard->getName());
    }

    public function testFindOneByIdAndUserReturnsNullForDifferentUser(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $board = $this->createJobBoard($user1, 'User 1 Board');

        $foundBoard = $this->repository->findOneByIdAndUser($board->getId(), $user2);

        $this->assertNull($foundBoard);
    }

    public function testFindOneByIdAndUserReturnsNullForNonExistentBoard(): void
    {
        $user = $this->createUser();

        $foundBoard = $this->repository->findOneByIdAndUser(999999, $user);

        $this->assertNull($foundBoard);
    }
}
