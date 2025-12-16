<?php

namespace App\Tests\Entity;

use App\Entity\JobBoard;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('John Doe');
        $user->setPassword('hashedpassword');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('hashedpassword', $user->getPassword());
    }

    public function testUserIdentifierIsEmail(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->assertEquals('user@example.com', $user->getUserIdentifier());
    }

    public function testUserHasRoleUserByDefault(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testCanSetCustomRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles); // Always includes ROLE_USER
    }

    public function testCanAddJobBoard(): void
    {
        $user = new User();
        $jobBoard = new JobBoard();
        $jobBoard->setName('My Board');

        $user->addJobBoard($jobBoard);

        $this->assertCount(1, $user->getJobBoards());
        $this->assertTrue($user->getJobBoards()->contains($jobBoard));
        $this->assertEquals($user, $jobBoard->getUser());
    }

    public function testCanRemoveJobBoard(): void
    {
        $user = new User();
        $jobBoard = new JobBoard();
        $jobBoard->setName('My Board');

        $user->addJobBoard($jobBoard);
        $this->assertCount(1, $user->getJobBoards());

        $user->removeJobBoard($jobBoard);
        $this->assertCount(0, $user->getJobBoards());
        $this->assertNull($jobBoard->getUser());
    }

    public function testDoesNotAddDuplicateJobBoards(): void
    {
        $user = new User();
        $jobBoard = new JobBoard();
        $jobBoard->setName('My Board');

        $user->addJobBoard($jobBoard);
        $user->addJobBoard($jobBoard); // Add same board twice

        $this->assertCount(1, $user->getJobBoards());
    }
}
