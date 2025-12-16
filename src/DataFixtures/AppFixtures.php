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
    private array $companies = [
        'ARM', 'BBC', 'BT', 'Vodafone', 'Sky', 'Ocado Technology', 'Deliveroo',
        'Just Eat', 'Wise', 'Revolut', 'Monzo', 'Starling Bank', 'Checkout.com',
        'Google (London)', 'Amazon (UK)', 'Microsoft (UK)', 'Apple (UK)',
        'Meta (London)', 'Spotify', 'Atlassian', 'Stripe',
        'Barclays', 'HSBC', 'Lloyds Banking Group', 'NatWest Group', 'Santander UK',
        'Jaguar Land Rover', 'BAE Systems', 'Rolls-Royce', 'Shell (UK)',
        'Deloitte', 'Accenture', 'KPMG', 'PwC', 'EY',
        'DeepMind', 'Trainline', 'Rightmove', 'Zoopla', 'ASOS', 'Tesco',
        'National Grid', 'GSK', 'AstraZeneca'
    ];

    private array $jobTitles = [
        'Software Engineer', 'Senior Software Engineer', 'Staff Software Engineer',
        'Principal Engineer', 'Software Developer', 'Full Stack Developer',
        'Frontend Engineer', 'Backend Engineer', 'DevOps Engineer',
        'Site Reliability Engineer', 'Cloud Engineer', 'Data Engineer',
        'Machine Learning Engineer', 'AI Research Scientist', 'Data Scientist',
        'Product Manager', 'Engineering Manager', 'Technical Lead',
        'Solutions Architect', 'Security Engineer', 'QA Engineer',
        'Mobile Developer', 'iOS Developer', 'Android Developer',
        'React Developer', 'Python Developer', 'Java Developer',
        'Go Developer', 'Rust Developer', '.NET Developer'
    ];

    private array $locations = [
        'Remote (UK)', 'Hybrid (London)', 'London', 'Manchester', 'Birmingham',
        'Bristol', 'Leeds', 'Liverpool', 'Newcastle upon Tyne', 'Sheffield',
        'Nottingham', 'Cambridge', 'Oxford', 'Reading', 'Milton Keynes',
        'Edinburgh', 'Glasgow', 'Cardiff', 'Belfast', 'Brighton',
        'Bath', 'Leicester', 'Southampton', 'Portsmouth'
    ];

    private array $statuses = [
        'wishlist', 'applied', 'interview', 'rejected', 'accepted', 'offered'
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create 10 users
        $users = [];
        $userNames = [
            ['Oliver Smith', 'oliver.smith@example.co.uk'],
            ['Amelia Jones', 'amelia.jones@example.co.uk'],
            ['Harry Taylor', 'harry.taylor@example.co.uk'],
            ['Emily Brown', 'emily.brown@example.co.uk'],
            ['George Wilson', 'george.wilson@example.co.uk'],
            ['Isla Johnson', 'isla.johnson@example.co.uk'],
            ['Noah Davies', 'noah.davies@example.co.uk'],
            ['Ava Thomas', 'ava.thomas@example.co.uk'],
            ['Jack Evans', 'jack.evans@example.co.uk'],
            ['Sophia Roberts', 'sophia.roberts@example.co.uk'],
        ];

        foreach ($userNames as [$name, $email]) {
            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }

        // Create job boards for users
        $boardTemplates = [
            ['Software Engineering (UK)', 'Tracking UK software engineering roles'],
            ['London & South East', 'Roles in London and the South East'],
            ['Remote & Hybrid (UK)', 'Remote-first and hybrid opportunities'],
            ['Senior Roles', 'Senior and staff level opportunities'],
            ['FinTech & Banking', 'Banks, FinTechs, and payments companies'],
            ['Data & ML', 'Data engineering, analytics, and ML roles'],
            ['Frontend (React/TypeScript)', 'UI engineering roles'],
            ['Backend & Platform', 'Backend, APIs, and infrastructure roles'],
            ['Full Stack', 'Full stack opportunities across the UK'],
            ['DevOps & SRE', 'Reliability, cloud, and platform roles'],
        ];

        $boards = [];
        foreach ($users as $userIndex => $user) {
            // Each user gets 2-4 job boards
            $boardCount = rand(2, 4);
            for ($i = 0; $i < $boardCount; $i++) {
                $template = $boardTemplates[($userIndex * $boardCount + $i) % count($boardTemplates)];
                $board = new JobBoard();
                $board->setName($template[0]);
                $board->setDescription($template[1]);
                $board->setUser($user);
                $manager->persist($board);
                $boards[] = ['board' => $board, 'user' => $user];
            }
        }

        // Create 200+ job applications
        $applicationCount = 0;
        foreach ($boards as $boardData) {
            $board = $boardData['board'];

            // Each board gets 5-15 applications
            $appsPerBoard = rand(5, 15);

            for ($i = 0; $i < $appsPerBoard; $i++) {
                $company = $this->companies[array_rand($this->companies)];
                $jobTitle = $this->jobTitles[array_rand($this->jobTitles)];
                $location = $this->locations[array_rand($this->locations)];
                $status = $this->statuses[array_rand($this->statuses)];

                $app = new JobApplication();
                $app->setCompany($company);
                $app->setJobTitle($jobTitle);
                $app->setLocation($location);
                $app->setStatus($status);
                $app->setJobBoard($board);

                // Generate realistic salary range
                $baseSalary = rand(35, 130) * 1000;
                $topSalary = $baseSalary + rand(5, 45) * 1000;
                $app->setSalary('£' . number_format($baseSalary) . ' - £' . number_format($topSalary));

                // Add URL
                $app->setUrl($this->generateJobUrl($company));

                // Add description
                $app->setDescription($this->generateJobDescription($jobTitle, $company));

                // Set timestamps based on status
                $createdDaysAgo = rand(1, 90);
                $app->setCreatedAt(new DateTime("-{$createdDaysAgo} days"));

                switch ($status) {
                    case 'applied':
                        $appliedDaysAgo = rand(1, $createdDaysAgo);
                        $app->setAppliedAt(new DateTime("-{$appliedDaysAgo} days"));
                        break;

                    case 'interview':
                        $appliedDaysAgo = rand(5, $createdDaysAgo);
                        $interviewDaysAgo = rand(1, $appliedDaysAgo - 1);
                        $app->setAppliedAt(new DateTime("-{$appliedDaysAgo} days"));
                        $app->setInterviewedAt(new DateTime("-{$interviewDaysAgo} days"));
                        break;

                    case 'offered':
                        $appliedDaysAgo = rand(10, $createdDaysAgo);
                        $interviewDaysAgo = rand(5, $appliedDaysAgo - 2);
                        $offeredDaysAgo = rand(1, $interviewDaysAgo - 1);
                        $app->setAppliedAt(new DateTime("-{$appliedDaysAgo} days"));
                        $app->setInterviewedAt(new DateTime("-{$interviewDaysAgo} days"));
                        $app->setOfferedAt(new DateTime("-{$offeredDaysAgo} days"));
                        break;

                    case 'rejected':
                        $appliedDaysAgo = rand(7, $createdDaysAgo);
                        $rejectedDaysAgo = rand(1, $appliedDaysAgo - 1);
                        $app->setAppliedAt(new DateTime("-{$appliedDaysAgo} days"));
                        if (rand(0, 1)) {
                            $interviewDaysAgo = rand($rejectedDaysAgo + 1, $appliedDaysAgo - 1);
                            $app->setInterviewedAt(new DateTime("-{$interviewDaysAgo} days"));
                        }
                        $app->setRejectedAt(new DateTime("-{$rejectedDaysAgo} days"));
                        break;

                    case 'accepted':
                        $appliedDaysAgo = rand(15, $createdDaysAgo);
                        $interviewDaysAgo = rand(7, $appliedDaysAgo - 2);
                        $offeredDaysAgo = rand(3, $interviewDaysAgo - 1);
                        $app->setAppliedAt(new DateTime("-{$appliedDaysAgo} days"));
                        $app->setInterviewedAt(new DateTime("-{$interviewDaysAgo} days"));
                        $app->setOfferedAt(new DateTime("-{$offeredDaysAgo} days"));
                        break;
                }

                $manager->persist($app);
                $applicationCount++;
            }
        }

        $manager->flush();

        echo "\n========================================\n";
        echo "Fixtures loaded successfully!\n";
        echo "========================================\n";
        echo "Created:\n";
        echo "  - " . count($users) . " users\n";
        echo "  - " . count($boards) . " job boards\n";
        echo "  - {$applicationCount} job applications\n";
        echo "\nSample login credentials:\n";
        echo "  Email: oliver.smith@example.co.uk\n";
        echo "  Password: password123\n";
        echo "\nAll users use the same password: password123\n";
        echo "========================================\n\n";
    }

    private function generateJobUrl(string $company): string
    {
        $companySlug = preg_replace('/[^a-z0-9]+/', '', strtolower($company));
        $domains = [
            'ARM' => 'https://careers.arm.com/job/',
            'BBC' => 'https://www.bbc.co.uk/careers/job/',
            'Deliveroo' => 'https://careers.deliveroo.co.uk/jobs/',
            'Monzo' => 'https://monzo.com/careers/',
            'Revolut' => 'https://www.revolut.com/careers/positions/',
            'Wise' => 'https://www.wise.jobs/role/',
            'Trainline' => 'https://jobs.trainline.com/jobs/',
            'Rightmove' => 'https://careers.rightmove.co.uk/jobs/',
            'AstraZeneca' => 'https://careers.astrazeneca.com/job/',
            'Google (London)' => 'https://careers.google.com/jobs/',
            'Microsoft (UK)' => 'https://careers.microsoft.com/v2/global/en/search?lc=United%20Kingdom&',
            'Amazon (UK)' => 'https://www.amazon.jobs/en/search?base_query=&loc_query=United%20Kingdom&',
            'Apple (UK)' => 'https://jobs.apple.com/en-gb/search?location=united-kingdom-GBR&',
            'Meta (London)' => 'https://www.metacareers.com/jobs/',
            'Stripe' => 'https://stripe.com/jobs/listing/',
            'Atlassian' => 'https://www.atlassian.com/company/careers/detail/',
        ];

        $baseUrl = $domains[$company] ?? "https://www.linkedin.com/jobs/search/?keywords={$companySlug}&location=United%20Kingdom&";
        return $baseUrl . uniqid();
    }

    private function generateJobDescription(string $jobTitle, string $company): string
    {
        $descriptions = [
            "Join our team at {$company} as a {$jobTitle}. We're looking for talented individuals to help build cutting-edge solutions.",
            "Exciting opportunity at {$company} for a {$jobTitle}. Work on challenging problems with a world-class team.",
            "We're hiring a {$jobTitle} to join {$company}. Help us shape the future of technology.",
            "{$company} is seeking a {$jobTitle} to work on innovative projects that impact millions of users.",
            "Be part of something special at {$company}. We're looking for a {$jobTitle} to join our growing team.",
            "Great opportunity for a {$jobTitle} at {$company}. Work with cutting-edge technologies and brilliant engineers.",
            "Join {$company} as a {$jobTitle} and make a real impact. We offer competitive compensation and great benefits.",
            "{$company} is expanding! Looking for a talented {$jobTitle} to help us build the next generation of products.",
            "Seeking a passionate {$jobTitle} to join our team at {$company}. Excellent growth opportunities and work-life balance.",
            "Transform your career at {$company}. We're hiring a {$jobTitle} to work on mission-critical systems.",
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
