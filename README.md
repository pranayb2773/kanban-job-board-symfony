# Kanban Job Board 🎯

A modern job application tracking system built with Symfony 8.0 that helps you organize and manage your job search using an intuitive Kanban board interface with drag-and-drop functionality.

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![Symfony](https://img.shields.io/badge/Symfony-8.0-000000?style=flat-square&logo=symfony)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.8-7952B3?style=flat-square&logo=bootstrap)
![Tests](https://img.shields.io/badge/tests-56%20passed-success?style=flat-square)
![License](https://img.shields.io/badge/license-Proprietary-red?style=flat-square)

## Overview

Kanban Job Board is a full-featured web application that allows users to track their job applications through different stages of the hiring process. With a visual drag-and-drop Kanban interface, users can easily visualize and manage their job search journey from wishlist to acceptance.

## 📸 Screenshots & Demo

### Demo Video
- `public/job_tracker_demo.mp4`

### Screenshots
| Landing Page | Dashboard |
| --- | --- |
| ![Landing Page 1](public/landing_page_1.png) | ![Dashboard](public/dashboard.png) |
| ![Landing Page 2](public/landing_page_2.png) | ![Kanban Board](public/job_board_kanban.png) |
| ![Landing Page 3](public/landing_page_3.png) | ![Application Details](public/job_application_details.png) |
|  | ![Application Edit](public/job_application_edit.png) |

**Perfect for:**
- Job seekers tracking multiple applications
- Career changers managing their transition
- Recruiters organizing candidate pipelines
- Anyone who wants to visualize their job search progress

## ✨ Features

### Core Functionality
- 🔐 **User Authentication & Authorization** - Secure registration and login system with password hashing
- 📋 **Job Board Management** - Create and manage multiple job boards for different job searches
- 🎨 **Kanban Interface** - Visual drag-and-drop board with 6 status columns:
  - 📝 **Wishlist** - Jobs you're interested in
  - 📨 **Applied** - Applications submitted
  - 💬 **Interview** - Interview scheduled or completed
  - ❌ **Rejected** - Unfortunately not selected
  - ✅ **Accepted** - You accepted the offer
  - 🎉 **Offered** - Received a job offer

### Job Application Management
- ✏️ **CRUD Operations** - Create, read, update, and delete job applications
- 🖱️ **Drag-and-Drop** - Move applications between status columns seamlessly
- 📇 **Detailed Application Cards** - Track company, position, location, salary, and more
- ⏱️ **Application Timeline** - Automatic timestamp tracking for status changes
- 🎭 **Modal Interfaces** - User-friendly modals for creating, editing, and viewing applications
- 🔗 **Job Posting Links** - Save URLs to original job postings

### User Experience
- 📱 **Responsive Design** - Mobile-friendly Bootstrap 5 interface
- ⚡ **Real-time Updates** - AJAX-powered interactions without page reloads
- 🔔 **Toast Notifications** - Instant feedback for all actions
- 🔒 **User Isolation** - Each user can only access their own job boards and applications
- 🎯 **Badge Counters** - Visual count of applications in each status
- 📊 **Status Progress** - See your application journey at a glance

## 🛠️ Tech Stack

### Backend
- **PHP 8.5** - Latest PHP version with modern features and enums
- **Symfony 8.0** - Modern PHP framework with PHP-based routing (`config/routes.php`)
- **Doctrine ORM 3.5** - Database abstraction and entity management
- **Twig 3** - Powerful template engine for views
- **Symfony Security** - Authentication and authorization
- **Doctrine Fixtures** - Comprehensive sample data for development
- **Symfony Validator** - Form and data validation

### Frontend
- **Bootstrap 5.3.8** - Responsive CSS framework
- **Bootstrap Icons 1.13** - Comprehensive icon library
- **Stimulus.js 3.0** - Lightweight JavaScript framework
- **Turbo (Hotwire)** - SPA-like page transitions
- **SortableJS 1.15** - Smooth drag-and-drop functionality
- **Sass** - CSS preprocessor for maintainable styles
- **Webpack Encore** - Modern asset bundling and compilation

### Testing
- **PHPUnit 12.5** - Comprehensive test suite
- **56 Tests** - Full test coverage
- **217 Assertions** - Ensuring code quality
- **Test Categories**:
  - Entity tests (behavior and relationships)
  - Repository tests (queries and security)
  - Controller tests (routes and authorization)
  - Feature tests (end-to-end workflows)

### Database
- **SQLite** - Lightweight database (development)
- Compatible with **MySQL/PostgreSQL** for production

## 📋 Requirements

- PHP >= 8.4
- Composer >= 2.0
- Node.js >= 18.x
- npm or yarn
- SQLite extension (or MySQL/PostgreSQL for production)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd kanban-job-board-symfony
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install JavaScript Dependencies
```bash
npm install
```

### 4. Configure Environment
```bash
cp .env .env.local
```

Edit `.env.local` and configure your environment:
```env
APP_ENV=dev
APP_SECRET=your_secret_key_here

# Database (SQLite for development)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# For MySQL
# DATABASE_URL="mysql://username:password@127.0.0.1:3306/kanban_job_board?serverVersion=8.0"

# For PostgreSQL
# DATABASE_URL="postgresql://username:password@127.0.0.1:5432/kanban_job_board?serverVersion=16&charset=utf8"
```

### 5. Create Database Schema
```bash
php bin/console doctrine:migrations:migrate
```

### 6. Load Sample Data (Recommended for Demo)
```bash
php bin/console doctrine:fixtures:load
```

**This creates comprehensive demo data:**

#### 👥 Users (10 total)
All users use the password: `password123`

| Name | Email |
|------|-------|
| Oliver Smith | oliver.smith@example.co.uk |
| Amelia Jones | amelia.jones@example.co.uk |
| Harry Taylor | harry.taylor@example.co.uk |
| Emily Brown | emily.brown@example.co.uk |
| George Wilson | george.wilson@example.co.uk |
| Isla Johnson | isla.johnson@example.co.uk |
| Noah Davies | noah.davies@example.co.uk |
| Ava Thomas | ava.thomas@example.co.uk |
| Jack Evans | jack.evans@example.co.uk |
| Sophia Roberts | sophia.roberts@example.co.uk |

#### 📊 Data Volume
- **~30 Job Boards** (each user gets 2-4 boards)
- **~300-400 Job Applications** distributed across all statuses

#### 🏢 Companies (50+)
Fixtures are UK-focused (companies, locations, and GBP salary ranges).

**UK tech, fintech & enterprise (examples):**
- ARM, BBC, BT, Vodafone, Sky
- Monzo, Starling Bank, Revolut, Wise, Checkout.com
- Ocado Technology, Deliveroo, Just Eat, Trainline, Rightmove
- Barclays, HSBC, NatWest Group, Lloyds Banking Group
- DeepMind, Google (London), Microsoft (UK), Amazon (UK), Apple (UK)

**Data & Cloud Companies:**
- MongoDB, Snowflake, DataDog, Elastic, Confluent
- HashiCorp, Cloudflare, Twilio

**Gaming & Creative:**
- Unity, Epic Games, Roblox, Discord, Canva

**And many more...**

#### 💼 Job Titles (30+)
- Software Engineer (various levels: Senior, Staff, Principal)
- Full Stack / Frontend / Backend Developer
- DevOps Engineer, SRE, Cloud Engineer
- Data Engineer, Data Scientist, ML Engineer
- Product Manager, Engineering Manager, Technical Lead
- Mobile Developer (iOS, Android)
- Language-specific roles (Python, Java, Go, Rust, .NET)

#### 📍 Locations (29+)
- Remote
- **Silicon Valley:** Mountain View, Palo Alto, Menlo Park, Sunnyvale
- **Major Tech Hubs:** San Francisco, Seattle, Austin, Boston, NYC
- **Other Cities:** Denver, Portland, LA, Chicago, Miami, Atlanta
- And 15+ more US cities

#### 💰 Salary Information
- Realistic ranges from **$80,000 to $330,000**
- Based on role seniority and company size

#### ⏰ Realistic Timelines
- Applications created over past 1-90 days
- Status-appropriate timestamps:
  - **Wishlist:** Just created date
  - **Applied:** Applied 1-X days after creation
  - **Interview:** Applied → Interviewed progression
  - **Offered:** Full timeline (Applied → Interviewed → Offered)
  - **Rejected:** Applied (with optional interview) → Rejected
  - **Accepted:** Complete timeline through offer acceptance

#### 🔗 Features per Application
- Company-specific career page URLs
- Unique job descriptions
- Location and salary information
- Proper status workflow timestamps

### 7. Build Frontend Assets
```bash
npm run build

# For development with watch mode
npm run watch

# For development server with live reload
npm run dev-server
```

## 🎮 Running the Application

### Development Server
```bash
symfony server:start

# Or using PHP built-in server
php -S localhost:8000 -t public/
```

Visit: **http://localhost:8000**

### Login Credentials (After Loading Fixtures)
```
Email: oliver.smith@example.co.uk
Password: password123
```

## 🧪 Testing

### Run All Tests
```bash
php bin/phpunit
```

### Run Tests with Detailed Output
```bash
php bin/phpunit --testdox
```

### Run Specific Test Suites
```bash
# Entity tests
php bin/phpunit tests/Entity

# Repository tests
php bin/phpunit tests/Repository

# Controller tests
php bin/phpunit tests/Controller

# Feature tests
php bin/phpunit tests/Feature
```

### Test Coverage Summary
```
✅ 56 tests
✅ 217 assertions
✅ All passing
```

**Test Categories:**
- **Entity Tests (18 tests)**: User, JobBoard, JobApplication behavior and relationships
- **Repository Tests (15 tests)**: Query methods, user isolation, security checks
- **Controller Tests (13 tests)**: Authentication, authorization, AJAX endpoints
- **Feature Tests (10 tests)**: Complete workflows, multi-user scenarios, security boundaries

### Setup Test Environment
The test environment uses a separate SQLite database:
```bash
php bin/console doctrine:schema:create --env=test
```

## 📁 Project Structure

```
kanban-job-board-symfony/
├── assets/
│   ├── controllers/               # Stimulus controllers
│   │   └── kanban_controller.js   # Drag-and-drop logic
│   ├── styles/                    # SCSS files
│   │   └── app.scss
│   └── app.js                     # Main JavaScript entry
├── config/
│   ├── packages/                  # Bundle configurations
│   ├── routes/                    # Framework/dev routes (profiler, errors, etc.)
│   ├── routes.php                 # Application routes (single source of truth)
│   └── services.yaml              # Service container
├── migrations/                    # Database migrations
├── public/
│   ├── build/                     # Compiled assets (auto-generated)
│   └── index.php                  # Application entry point
├── src/
│   ├── Controller/
│   │   ├── JobBoardController.php # Kanban + job board endpoints
│   │   ├── SecurityController.php # Auth endpoints
│   │   └── DashboardController.php
│   ├── Entity/
│   │   ├── User.php               # User entity
│   │   ├── JobBoard.php           # Job board entity
│   │   └── JobApplication.php     # Application entity
│   ├── Enum/
│   │   └── JobApplicationStatus.php # Status enum (6 values)
│   ├── Form/
│   │   ├── JobBoardType.php       # Job board form
│   │   ├── JobApplicationType.php # Application form
│   │   └── RegistrationType.php   # Registration form
│   ├── Repository/
│   │   ├── JobBoardRepository.php
│   │   └── JobApplicationRepository.php
│   ├── DataFixtures/
│   │   └── AppFixtures.php        # 300+ demo applications
│   └── Twig/
│       └── Components/            # Twig components
├── templates/
│   ├── job_board/
│   │   ├── kanban.html.twig       # Main Kanban view
│   │   ├── index.html.twig        # Board list
│   │   └── _application_card.html.twig
│   ├── partials/
│   │   ├── _header.html.twig      # Navigation header
│   │   ├── _job_application_create_modal.html.twig
│   │   ├── _job_application_edit_modal.html.twig
│   │   └── _delete_confirmation_modal.html.twig
│   ├── security/
│   │   ├── login.html.twig
│   │   └── register.html.twig
│   ├── dashboard/
│   │   └── index.html.twig
│   ├── form/
│   │   └── bootstrap_form_theme.html.twig
│   └── base.html.twig             # Base layout
├── tests/
│   ├── Entity/                    # Entity unit tests
│   ├── Repository/                # Repository tests
│   ├── Controller/                # Controller tests
│   └── Feature/                   # Integration tests
├── var/
│   ├── cache/                     # Application cache
│   ├── log/                       # Log files
│   └── data.db                    # SQLite database (dev)
├── vendor/                        # PHP dependencies (Composer)
├── node_modules/                  # JS dependencies (npm)
├── composer.json                  # PHP dependencies
├── package.json                   # JS dependencies
├── webpack.config.js              # Webpack Encore config
├── phpunit.dist.xml               # PHPUnit configuration
└── README.md                      # This file
```

## 📖 Usage Guide

### 1. Register an Account
- Navigate to `/register`
- Enter your name, email, and password
- Submit to create your account

### 2. Create a Job Board
- After login, click **"New Job Board"** from the dashboard
- Enter board name (e.g., "FinTech & Banking")
- Add optional description
- Submit the form

### 3. View Kanban Board
- Click on any job board from the header dropdown menu
- You'll see 6 status columns with application counts

### 4. Add Job Application
- Click the **"+ Add Application"** button on the Kanban board
- Fill in the application details:
  - **Company name** (required) - e.g., "Monzo"
  - **Job title** (required) - e.g., "Senior Software Engineer"
  - **Location** (required) - e.g., "London" or "Remote (UK)"
  - **URL** (optional) - Link to job posting
  - **Salary** (optional) - e.g., "£65,000 - £95,000"
  - **Description** (required) - Job description or notes
  - **Initial status** - Select from dropdown
- Click **Submit** to create

### 5. Manage Applications

#### View Details
- Click on any application card
- Modal displays all information including timestamps

#### Edit Application
- Click **"Edit"** button in the details modal
- Update any fields
- Save changes

#### Change Status (Drag & Drop)
- Grab any application card
- Drag it to a different status column
- Release to update status
- Toast notification confirms the change
- Timestamps are automatically updated

#### Delete Application
- Click **"Delete"** button in the details modal
- Confirm deletion in the confirmation modal
- Application is permanently removed

### 6. Track Your Progress
- **Badge Counters**: Each column header shows application count
- **Sorting**: Applications ordered by creation date (newest first)
- **Timestamps**: Track when you:
  - Created the application (`createdAt`)
  - Applied (`appliedAt`)
  - Got an interview (`interviewedAt`)
  - Received an offer (`offeredAt`)
  - Were rejected (`rejectedAt`)

### 7. Multiple Job Boards
- Create different boards for different job searches
- Example boards:
  - "London & South East"
  - "Remote & Hybrid (UK)"
  - "Senior Positions"
  - "Data & ML"

## 🔧 Development

### Coding Standards
- **PHP**: PSR-12 coding style
- **JavaScript**: ES6+ with Stimulus conventions
- **CSS**: BEM-like naming for custom styles
- **Twig**: Consistent indentation and organization

### Database Migrations

#### Create a New Migration
```bash
php bin/console make:migration
```

#### Run Migrations
```bash
php bin/console doctrine:migrations:migrate
```

#### Check Migration Status
```bash
php bin/console doctrine:migrations:status
```

### Creating New Entities
```bash
php bin/console make:entity
```

### Asset Compilation

```bash
# Development build (unminified, with source maps)
npm run dev

# Production build (minified, optimized)
npm run build

# Watch for changes (auto-rebuild)
npm run watch

# Dev server with HMR (hot module replacement)
npm run dev-server
```

### Clearing Cache
```bash
# Clear application cache
php bin/console cache:clear

# Clear cache for production environment
php bin/console cache:clear --env=prod
```

### Debug Commands

```bash
# List all routes
php bin/console debug:router

# Show specific route details
php bin/console debug:router app_job_board_kanban

# List all services
php bin/console debug:container

# Check environment variables
php bin/console debug:dotenv
```

### Code Quality

```bash
# PHP CS Fixer (if installed)
vendor/bin/php-cs-fixer fix src

# PHPStan (if installed)
vendor/bin/phpstan analyse src
```

## 🌐 API Endpoints

### Kanban Board Operations
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/job-board/{id}/kanban` | Display Kanban board with all applications |
| GET | `/job-board/application/{id}/details` | Get application details (JSON) |
| PATCH | `/job-board/application/{id}/status` | Update application status (AJAX) |

### Application Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/job-board/{boardId}/_fragment/application-modal` | Load create form modal (AJAX) |
| POST | `/job-board/{boardId}/application/create` | Create new application (AJAX) |
| GET | `/job-board/application/{id}/_fragment/edit-modal` | Load edit form modal (AJAX) |
| POST | `/job-board/application/{id}/update` | Update application (AJAX) |
| DELETE | `/job-board/application/{id}/delete` | Delete application (AJAX) |

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/login` | Login page |
| POST | `/login` | Login submission |
| GET | `/logout` | Logout user |
| GET | `/register` | Registration page |
| POST | `/register` | Registration submission |

### Job Boards
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | User dashboard with board list |
| GET | `/job-board` | List all user's job boards |
| GET | `/job-board/new` | Create job board form |
| POST | `/job-board/new` | Create job board |

## 🔒 Security Features

- **Password Hashing**: Bcrypt algorithm with automatic salt
- **CSRF Protection**: Built-in Symfony CSRF tokens on all forms
- **XSS Prevention**: Twig auto-escaping for all output
- **SQL Injection Prevention**: Doctrine parameterized queries
- **User Isolation**: Repository-level security checks ensure users only access their own data
- **Authorization**: Role-based access control (`ROLE_USER`)
- **Session Security**: Secure session handling and cookie configuration
- **Form Validation**: Server-side validation on all inputs
- **Route Protection**: Authentication required for all application routes

## ⚡ Performance Optimizations

- **Asset Compilation**: Minified and bundled JS/CSS in production
- **Doctrine Query Optimization**: Efficient joins and eager loading
- **Lazy Loading**: Turbo for SPA-like navigation without full page reloads
- **Database Indexes**: Optimized queries with proper indexing on foreign keys
- **Cache**: Symfony cache for configuration, routes, and templates
- **Asset Versioning**: Cache-busting for CSS/JS files
- **AJAX Operations**: Minimize page reloads with async updates

## 🌐 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

**Minimum Requirements:**
- ES6 JavaScript support
- CSS Grid and Flexbox support
- Fetch API support

## 🐛 Troubleshooting

### Database Connection Errors
```bash
# Verify database URL in .env.local
cat .env.local | grep DATABASE_URL

# For SQLite, ensure var/ directory is writable
chmod -R 775 var/
chown -R www-data:www-data var/  # On Linux servers
```

### Asset Build Errors
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Permission Issues
```bash
# Set proper permissions on cache and log directories
chmod -R 775 var/cache var/log

# On Linux servers
chown -R www-data:www-data var/
```

### Test Database Issues
```bash
# Recreate test database schema
php bin/console doctrine:schema:drop --force --env=test
php bin/console doctrine:schema:create --env=test
```

### Composer Dependency Issues
```bash
# Update all dependencies
composer update

# Clear Composer cache
composer clear-cache

# Reinstall dependencies
rm -rf vendor/
composer install
```

### Symfony Cache Issues
```bash
# Clear all caches
php bin/console cache:clear
php bin/console cache:warmup

# Clear specific cache pools
php bin/console cache:pool:clear cache.app
```

### JavaScript Errors
1. Check browser console for errors
2. Verify Stimulus controllers are loaded: Check for `[data-controller]` attributes
3. Ensure Webpack build completed successfully
4. Clear browser cache and hard reload (Ctrl+Shift+R / Cmd+Shift+R)
