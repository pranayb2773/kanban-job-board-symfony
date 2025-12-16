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
        'Google', 'Microsoft', 'Amazon', 'Meta', 'Apple', 'Netflix', 'Stripe',
        'GitLab', 'GitHub', 'Atlassian', 'Salesforce', 'Adobe', 'Oracle',
        'IBM', 'Intel', 'NVIDIA', 'AMD', 'Cisco', 'VMware', 'Red Hat',
        'Uber', 'Lyft', 'Airbnb', 'DoorDash', 'Instacart', 'Dropbox',
        'Slack', 'Zoom', 'Shopify', 'Square', 'PayPal', 'Twitter', 'LinkedIn',
        'Snap', 'Pinterest', 'Spotify', 'Twilio', 'Cloudflare', 'DataDog',
        'MongoDB', 'Snowflake', 'HashiCorp', 'Elastic', 'Confluent', 'Unity',
        'Epic Games', 'Roblox', 'Discord', 'Figma', 'Notion', 'Canva'
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
        'Remote', 'San Francisco, CA', 'New York, NY', 'Seattle, WA',
        'Austin, TX', 'Boston, MA', 'Denver, CO', 'Portland, OR',
        'Los Angeles, CA', 'Chicago, IL', 'Miami, FL', 'Atlanta, GA',
        'Mountain View, CA', 'Palo Alto, CA', 'Menlo Park, CA',
        'Sunnyvale, CA', 'Redmond, WA', 'Santa Monica, CA',
        'Boulder, CO', 'Cambridge, MA', 'Arlington, VA', 'Raleigh, NC',
        'Phoenix, AZ', 'Salt Lake City, UT', 'Dallas, TX', 'Houston, TX',
        'Philadelphia, PA', 'San Diego, CA', 'Washington, DC'
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
            ['John Doe', 'john.doe@example.com'],
            ['Jane Smith', 'jane.smith@example.com'],
            ['Michael Johnson', 'michael.johnson@example.com'],
            ['Emily Davis', 'emily.davis@example.com'],
            ['David Wilson', 'david.wilson@example.com'],
            ['Sarah Brown', 'sarah.brown@example.com'],
            ['Robert Garcia', 'robert.garcia@example.com'],
            ['Lisa Martinez', 'lisa.martinez@example.com'],
            ['James Anderson', 'james.anderson@example.com'],
            ['Jennifer Taylor', 'jennifer.taylor@example.com'],
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
            ['Software Engineering Jobs', 'Tracking software engineering positions'],
            ['Remote Opportunities', 'Work from anywhere positions'],
            ['Senior Level Roles', 'Senior and staff level opportunities'],
            ['Startup Jobs', 'Early stage startup opportunities'],
            ['FAANG Applications', 'Applications to big tech companies'],
            ['Data Science Positions', 'Data science and ML roles'],
            ['Frontend Development', 'Frontend and UI engineering roles'],
            ['Backend Engineering', 'Backend and infrastructure positions'],
            ['Full Stack Roles', 'Full stack development opportunities'],
            ['DevOps Positions', 'DevOps and SRE roles'],
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
                $baseSalary = rand(80, 250) * 1000;
                $topSalary = $baseSalary + rand(30, 80) * 1000;
                $app->setSalary('$' . number_format($baseSalary) . ' - $' . number_format($topSalary));

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
        echo "  Email: john.doe@example.com\n";
        echo "  Password: password123\n";
        echo "\nAll users use the same password: password123\n";
        echo "========================================\n\n";
    }

    private function generateJobUrl(string $company): string
    {
        $companySlug = strtolower(str_replace(' ', '', $company));
        $domains = [
            'Google' => 'https://careers.google.com/jobs/',
            'Microsoft' => 'https://careers.microsoft.com/jobs/',
            'Amazon' => 'https://www.amazon.jobs/en/jobs/',
            'Meta' => 'https://www.metacareers.com/jobs/',
            'Apple' => 'https://jobs.apple.com/en-us/details/',
            'Netflix' => 'https://jobs.netflix.com/jobs/',
            'Stripe' => 'https://stripe.com/jobs/listing/',
            'GitLab' => 'https://about.gitlab.com/jobs/apply/',
            'GitHub' => 'https://github.com/careers/',
            'Atlassian' => 'https://www.atlassian.com/company/careers/detail/',
        ];

        $baseUrl = $domains[$company] ?? "https://{$companySlug}.com/careers/";
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
