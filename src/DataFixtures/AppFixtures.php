<?php

namespace App\DataFixtures;


use App\Entity\JobApplication;
use App\Entity\JobBoard;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create users
        $user1 = new User();
        $user1->setEmail('john.doe@example.com');
        $user1->setName('John Doe');
        $hashedPassword = $this->passwordHasher->hashPassword($user1, 'password123');
        $user1->setPassword($hashedPassword);
        $user1->setRoles(['ROLE_USER']);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('jane.smith@example.com');
        $user2->setName('Jane Smith');
        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'password123');
        $user2->setPassword($hashedPassword);
        $user2->setRoles(['ROLE_USER']);
        $manager->persist($user2);

        // Create job boards for user1
        $board1 = new JobBoard();
        $board1->setName('Software Engineering Jobs');
        $board1->setDescription('Tracking my software engineering job applications');
        $board1->setUser($user1);
        $manager->persist($board1);

        $board2 = new JobBoard();
        $board2->setName('Remote Opportunities');
        $board2->setDescription('Remote job opportunities I am interested in');
        $board2->setUser($user1);
        $manager->persist($board2);

        // Create job boards for user2
        $board3 = new JobBoard();
        $board3->setName('Data Science Positions');
        $board3->setDescription('Data science and analytics roles');
        $board3->setUser($user2);
        $manager->persist($board3);

        // Create job applications for board1
        $app1 = new JobApplication();
        $app1->setCompany('Google');
        $app1->setJobTitle('Senior Software Engineer');
        $app1->setUrl('https://careers.google.com/jobs/example');
        $app1->setSalary('$150,000 - $200,000');
        $app1->setLocation('Mountain View, CA');
        $app1->setDescription('Working on cutting-edge technologies in cloud infrastructure.');
        $app1->setStatus('applied');
        $app1->setJobBoard($board1);
        $app1->setCreatedAt(new DateTime('-10 days'));
        $app1->setAppliedAt(new DateTime('-8 days'));
        $manager->persist($app1);

        $app2 = new JobApplication();
        $app2->setCompany('Microsoft');
        $app2->setJobTitle('Software Engineer II');
        $app2->setUrl('https://careers.microsoft.com/jobs/example');
        $app2->setSalary('$140,000 - $180,000');
        $app2->setLocation('Seattle, WA');
        $app2->setDescription('Develop innovative solutions for Azure platform.');
        $app2->setStatus('interviewed');
        $app2->setJobBoard($board1);
        $app2->setCreatedAt(new DateTime('-15 days'));
        $app2->setAppliedAt(new DateTime('-12 days'));
        $app2->setInterviewedAt(new DateTime('-3 days'));
        $manager->persist($app2);

        $app3 = new JobApplication();
        $app3->setCompany('Amazon');
        $app3->setJobTitle('Software Development Engineer');
        $app3->setUrl('https://www.amazon.jobs/example');
        $app3->setSalary('$145,000 - $195,000');
        $app3->setLocation('Austin, TX');
        $app3->setDescription('Build scalable distributed systems for AWS services.');
        $app3->setStatus('wishlist');
        $app3->setJobBoard($board1);
        $app3->setCreatedAt(new DateTime('-2 days'));
        $manager->persist($app3);

        // Create job applications for board2
        $app4 = new JobApplication();
        $app4->setCompany('GitLab');
        $app4->setJobTitle('Backend Engineer');
        $app4->setUrl('https://about.gitlab.com/jobs/example');
        $app4->setSalary('$130,000 - $170,000');
        $app4->setLocation('Remote');
        $app4->setDescription('Work on GitLab core product features.');
        $app4->setStatus('offered');
        $app4->setJobBoard($board2);
        $app4->setCreatedAt(new DateTime('-20 days'));
        $app4->setAppliedAt(new DateTime('-18 days'));
        $app4->setInterviewedAt(new DateTime('-10 days'));
        $app4->setOfferedAt(new DateTime('-2 days'));
        $manager->persist($app4);

        $app5 = new JobApplication();
        $app5->setCompany('Stripe');
        $app5->setJobTitle('Software Engineer');
        $app5->setUrl('https://stripe.com/jobs/example');
        $app5->setSalary('$160,000 - $210,000');
        $app5->setLocation('Remote');
        $app5->setDescription('Build payment processing systems.');
        $app5->setStatus('rejected');
        $app5->setJobBoard($board2);
        $app5->setCreatedAt(new DateTime('-30 days'));
        $app5->setAppliedAt(new DateTime('-28 days'));
        $app5->setInterviewedAt(new DateTime('-15 days'));
        $app5->setRejectedAt(new DateTime('-7 days'));
        $manager->persist($app5);

        // Create job applications for board3
        $app6 = new JobApplication();
        $app6->setCompany('Netflix');
        $app6->setJobTitle('Data Scientist');
        $app6->setUrl('https://jobs.netflix.com/example');
        $app6->setSalary('$155,000 - $205,000');
        $app6->setLocation('Los Gatos, CA');
        $app6->setDescription('Analyze user behavior and build recommendation systems.');
        $app6->setStatus('applied');
        $app6->setJobBoard($board3);
        $app6->setCreatedAt(new DateTime('-5 days'));
        $app6->setAppliedAt(new DateTime('-3 days'));
        $manager->persist($app6);

        $app7 = new JobApplication();
        $app7->setCompany('Meta');
        $app7->setJobTitle('Machine Learning Engineer');
        $app7->setUrl('https://www.metacareers.com/jobs/example');
        $app7->setSalary('$170,000 - $220,000');
        $app7->setLocation('Menlo Park, CA');
        $app7->setDescription('Develop ML models for content ranking and recommendations.');
        $app7->setStatus('wishlist');
        $app7->setJobBoard($board3);
        $app7->setCreatedAt(new DateTime('-1 day'));
        $manager->persist($app7);

        $manager->flush();
    }
}
