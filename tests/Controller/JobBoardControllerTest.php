<?php

namespace App\Tests\Controller;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class JobBoardControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

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

        $hasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $hasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function loginUser(User $user): void
    {
        $this->client->loginUser($user);
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
        JobBoard $board,
        string $company,
        string $status = 'wishlist'
    ): JobApplication {
        $application = new JobApplication();
        $application->setCompany($company);
        $application->setJobTitle('Software Engineer');
        $application->setLocation('Remote');
        $application->setDescription('Test description');
        $application->setStatus($status);
        $application->setJobBoard($board);
        $application->setCreatedAt(new \DateTime());

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    public function testKanbanRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertResponseRedirects('/login');
    }

    public function testKanbanDisplaysJobBoard(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $board = $this->createJobBoard($user, 'Software Engineering Jobs');
        $this->createJobApplication($board, 'Google', 'wishlist');
        $this->createJobApplication($board, 'Microsoft', 'applied');

        $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Software Engineering Jobs');
        $this->assertSelectorExists('[data-controller="kanban"]');
        $this->assertSelectorExists('[data-status="wishlist"]');
        $this->assertSelectorExists('[data-status="applied"]');
    }

    public function testKanbanReturns404ForNonExistentBoard(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $this->client->request('GET', '/job-board/999999/kanban');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testKanbanReturns404ForOtherUserBoard(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $board = $this->createJobBoard($user1, 'User 1 Board');

        $this->loginUser($user2);
        $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertResponseStatusCodeSame(404);
    }

    public function testApplicationDetailsRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google');

        $this->client->request('GET', "/job-board/application/{$application->getId()}/details");

        $this->assertResponseRedirects('/login');
    }

    public function testApplicationDetailsReturnsJson(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google', 'applied');
        $application->setSalary('$150,000');
        $application->setUrl('https://careers.google.com');
        $this->entityManager->flush();

        $this->client->request('GET', "/job-board/application/{$application->getId()}/details");

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($application->getId(), $data['id']);
        $this->assertEquals('Google', $data['company']);
        $this->assertEquals('Software Engineer', $data['jobTitle']);
        $this->assertEquals('applied', $data['status']);
        $this->assertEquals('$150,000', $data['salary']);
        $this->assertEquals('https://careers.google.com', $data['url']);
    }

    public function testUpdateStatusRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google', 'wishlist');

        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$application->getId()}/status",
            ['status' => 'applied']
        );

        $this->assertResponseRedirects('/login');
    }

    public function testUpdateStatusChangesApplicationStatus(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google', 'wishlist');

        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$application->getId()}/status",
            ['status' => 'applied']
        );

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);

        // Refresh entity from database
        $this->entityManager->refresh($application);
        $this->assertEquals('applied', $application->getStatus());
        $this->assertNotNull($application->getAppliedAt());
    }

    public function testUpdateStatusRejectsInvalidStatus(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google', 'wishlist');

        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$application->getId()}/status",
            ['status' => 'invalid_status']
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCreateApplicationRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');

        $this->client->request('POST', "/job-board/{$board->getId()}/application/create");

        $this->assertResponseRedirects('/login');
    }

    public function testDeleteApplicationRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google');

        $this->client->request('DELETE', "/job-board/application/{$application->getId()}/delete");

        $this->assertResponseRedirects('/login');
    }

    public function testDeleteApplicationRemovesFromDatabase(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google');
        $applicationId = $application->getId();

        $this->client->request('DELETE', "/job-board/application/{$applicationId}/delete");

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);

        // Verify application was deleted
        $deletedApplication = $this->entityManager
            ->getRepository(JobApplication::class)
            ->find($applicationId);

        $this->assertNull($deletedApplication);
    }

    public function testDeleteApplicationReturns404ForOtherUserApplication(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $board = $this->createJobBoard($user1, 'User 1 Board');
        $application = $this->createJobApplication($board, 'Google');

        $this->loginUser($user2);
        $this->client->request('DELETE', "/job-board/application/{$application->getId()}/delete");

        $this->assertResponseStatusCodeSame(404);

        // Verify application was NOT deleted
        $this->entityManager->refresh($application);
        $this->assertNotNull($application);
    }
}
