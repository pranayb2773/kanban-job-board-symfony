<?php

namespace App\Tests\Feature;

use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class KanbanBoardFeatureTest extends WebTestCase
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

    private function createJobBoard(User $user, string $name): JobBoard
    {
        $jobBoard = new JobBoard();
        $jobBoard->setName($name);
        $jobBoard->setDescription('Test board description');
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
        $application->setDescription('Exciting opportunity');
        $application->setStatus($status);
        $application->setJobBoard($board);
        $application->setCreatedAt(new \DateTime());

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    /**
     * Test complete Kanban board workflow: view board, create application, update status, delete
     */
    public function testCompleteKanbanBoardWorkflow(): void
    {
        // Given: A user with a job board
        $user = $this->createUser();
        $this->client->loginUser($user);

        $board = $this->createJobBoard($user, 'Software Engineering Jobs 2024');

        // Step 1: View the empty Kanban board
        $crawler = $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Software Engineering Jobs 2024');
        $this->assertSelectorExists('[data-controller="kanban"]');

        // All 6 status columns should be present
        $this->assertSelectorExists('[data-status="wishlist"]');
        $this->assertSelectorExists('[data-status="applied"]');
        $this->assertSelectorExists('[data-status="interview"]');
        $this->assertSelectorExists('[data-status="rejected"]');
        $this->assertSelectorExists('[data-status="accepted"]');
        $this->assertSelectorExists('[data-status="offered"]');

        // Badge counts should show 0
        $badges = $crawler->filter('.badge');
        foreach ($badges as $badge) {
            $this->assertStringContainsString('0', $badge->textContent);
        }

        // Step 2: Create a new job application
        $application = $this->createJobApplication($board, 'Google', 'wishlist');

        // Reload page and verify application appears
        $crawler = $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertSelectorTextContains('.kanban-card', 'Google');
        $this->assertSelectorTextContains('.kanban-card', 'Software Engineer');

        // Wishlist column badge should show 1
        $badges = $crawler->filter('.badge');
        $this->assertGreaterThan(0, $badges->count(), 'Should have badge elements');
        // The first badge should be for wishlist column and show count 1
        $wishlistBadge = $badges->first();
        $this->assertEquals('1', trim($wishlistBadge->text()));

        // Step 3: Update application status via AJAX
        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$application->getId()}/status",
            ['status' => 'applied']
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);

        // Verify status was updated in database
        $applicationId = $application->getId();
        $this->entityManager->clear();
        $application = $this->entityManager->getRepository(JobApplication::class)->find($applicationId);
        $this->assertEquals('applied', $application->getStatus());
        $this->assertNotNull($application->getAppliedAt());

        // Step 4: Move through the workflow
        // applied -> interview
        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$applicationId}/status",
            ['status' => 'interview']
        );
        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $application = $this->entityManager->getRepository(JobApplication::class)->find($applicationId);
        $this->assertEquals('interview', $application->getStatus());
        $this->assertNotNull($application->getInterviewedAt());

        // interview -> offered
        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$applicationId}/status",
            ['status' => 'offered']
        );
        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $application = $this->entityManager->getRepository(JobApplication::class)->find($applicationId);
        $this->assertEquals('offered', $application->getStatus());
        $this->assertNotNull($application->getOfferedAt());

        // Step 5: Delete the application
        $applicationId = $application->getId();

        $this->client->request('DELETE', "/job-board/application/{$applicationId}/delete");
        $this->assertResponseIsSuccessful();

        // Verify deletion
        $this->entityManager->clear();
        $deletedApp = $this->entityManager
            ->getRepository(JobApplication::class)
            ->find($applicationId);
        $this->assertNull($deletedApp);
    }

    /**
     * Test viewing a board with applications in all statuses
     */
    public function testKanbanBoardDisplaysAllStatuses(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $board = $this->createJobBoard($user, 'My Applications');

        // Create applications in each status
        $this->createJobApplication($board, 'Google', 'wishlist');
        $this->createJobApplication($board, 'Microsoft', 'wishlist');
        $this->createJobApplication($board, 'Amazon', 'applied');
        $this->createJobApplication($board, 'Meta', 'interview');
        $this->createJobApplication($board, 'Apple', 'offered');
        $this->createJobApplication($board, 'Netflix', 'rejected');
        $this->createJobApplication($board, 'Spotify', 'accepted');

        $crawler = $this->client->request('GET', "/job-board/{$board->getId()}/kanban");

        $this->assertResponseIsSuccessful();

        // Verify all companies are visible
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Google', $responseContent);
        $this->assertStringContainsString('Microsoft', $responseContent);
        $this->assertStringContainsString('Amazon', $responseContent);
        $this->assertStringContainsString('Meta', $responseContent);
        $this->assertStringContainsString('Apple', $responseContent);
        $this->assertStringContainsString('Netflix', $responseContent);
        $this->assertStringContainsString('Spotify', $responseContent);

        // Verify we have 7 kanban cards
        $this->assertCount(7, $crawler->filter('.kanban-card'));

        // Verify badge counts
        $wishlistBadge = $crawler->filter('.kanban-column[data-status="wishlist"] .badge')->text();
        $this->assertStringContainsString('2', $wishlistBadge);

        $appliedBadge = $crawler->filter('.kanban-column[data-status="applied"] .badge')->text();
        $this->assertStringContainsString('1', $appliedBadge);

        $interviewBadge = $crawler->filter('.kanban-column[data-status="interview"] .badge')->text();
        $this->assertStringContainsString('1', $interviewBadge);
    }

    /**
     * Test that users can only access their own boards
     */
    public function testUsersCanOnlyAccessOwnBoards(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $user1Board = $this->createJobBoard($user1, 'User 1 Board');
        $user2Board = $this->createJobBoard($user2, 'User 2 Board');

        $this->createJobApplication($user1Board, 'Google', 'wishlist');
        $this->createJobApplication($user2Board, 'Microsoft', 'applied');

        // Login as user1
        $this->client->loginUser($user1);

        // Can access own board
        $this->client->request('GET', "/job-board/{$user1Board->getId()}/kanban");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.kanban-card', 'Google');

        // Cannot access user2's board
        $this->client->request('GET', "/job-board/{$user2Board->getId()}/kanban");
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test that users can only modify their own applications
     */
    public function testUsersCanOnlyModifyOwnApplications(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $user1Board = $this->createJobBoard($user1, 'User 1 Board');
        $user2Board = $this->createJobBoard($user2, 'User 2 Board');

        $user1Application = $this->createJobApplication($user1Board, 'Google');
        $user2Application = $this->createJobApplication($user2Board, 'Microsoft');

        // Login as user1
        $this->client->loginUser($user1);

        // Can update own application
        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$user1Application->getId()}/status",
            ['status' => 'applied']
        );
        $this->assertResponseIsSuccessful();

        // Cannot update user2's application
        $this->client->jsonRequest(
            'PATCH',
            "/job-board/application/{$user2Application->getId()}/status",
            ['status' => 'applied']
        );
        $this->assertResponseStatusCodeSame(404);

        // Cannot delete user2's application
        $user2ApplicationId = $user2Application->getId();
        $this->client->request('DELETE', "/job-board/application/{$user2ApplicationId}/delete");
        $this->assertResponseStatusCodeSame(404);

        // Verify user2's application still exists
        $stillExists = $this->entityManager
            ->getRepository(JobApplication::class)
            ->find($user2ApplicationId);
        $this->assertNotNull($stillExists);
        $this->assertEquals('wishlist', $stillExists->getStatus());
    }

    /**
     * Test application details endpoint returns complete data
     */
    public function testApplicationDetailsReturnsCompleteData(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $board = $this->createJobBoard($user, 'My Board');
        $application = $this->createJobApplication($board, 'Google', 'applied');

        $application->setSalary('$150,000 - $200,000');
        $application->setUrl('https://careers.google.com/job/123');
        $application->setAppliedAt(new \DateTime('2024-01-15'));
        $this->entityManager->flush();

        $this->client->request('GET', "/job-board/application/{$application->getId()}/details");

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Google', $data['company']);
        $this->assertEquals('Software Engineer', $data['jobTitle']);
        $this->assertEquals('Remote', $data['location']);
        $this->assertEquals('applied', $data['status']);
        $this->assertEquals('$150,000 - $200,000', $data['salary']);
        $this->assertEquals('https://careers.google.com/job/123', $data['url']);
        $this->assertEquals('Exciting opportunity', $data['description']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('appliedAt', $data);
    }
}
