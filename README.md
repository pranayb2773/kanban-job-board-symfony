# Kanban Job Board

A modern job application tracking system built with Symfony 8.0 that helps you organize and manage your job search using an intuitive Kanban board interface.

## Overview

Kanban Job Board is a full-featured web application that allows users to track their job applications through different stages of the hiring process. With a drag-and-drop Kanban interface, users can easily visualize and manage their job search journey from wishlist to acceptance.

## Features

### Core Functionality
- **User Authentication & Authorization** - Secure registration and login system
- **Job Board Management** - Create and manage multiple job boards
- **Kanban Interface** - Visual drag-and-drop board with 6 status columns:
  - Wishlist
  - Applied
  - Interview
  - Rejected
  - Accepted
  - Offered

### Job Application Management
- **CRUD Operations** - Create, read, update, and delete job applications
- **Drag-and-Drop** - Move applications between status columns seamlessly
- **Detailed Application Cards** - Track company, position, location, salary, and more
- **Application Timeline** - Automatic timestamp tracking for status changes
- **Modal Interfaces** - User-friendly modals for creating, editing, and viewing applications

### User Experience
- **Responsive Design** - Mobile-friendly Bootstrap 5 interface
- **Real-time Updates** - AJAX-powered interactions without page reloads
- **Toast Notifications** - Instant feedback for all actions
- **User Isolation** - Each user can only access their own job boards and applications

## Tech Stack

### Backend
- **PHP 8.5** - Latest PHP version with modern features
- **Symfony 8.0** - Modern PHP framework
- **Doctrine ORM 3.5** - Database abstraction and entity management
- **Twig 3** - Template engine for views
- **Symfony Security** - Authentication and authorization
- **Doctrine Fixtures** - Sample data for development

### Frontend
- **Bootstrap 5.3.8** - Responsive CSS framework
- **Bootstrap Icons** - Icon library
- **Stimulus.js 3.0** - Lightweight JavaScript framework
- **Turbo (Hotwire)** - SPA-like page transitions
- **SortableJS** - Drag-and-drop functionality
- **Sass** - CSS preprocessor
- **Webpack Encore** - Asset bundling and compilation

### Testing
- **PHPUnit 12.5** - Comprehensive test suite
- **56 Tests** - Entity, Repository, Controller, and Feature tests
- **217 Assertions** - Full code coverage

### Database
- **SQLite** - Lightweight database (development)
- Compatible with **MySQL/PostgreSQL** for production

## Requirements

- PHP >= 8.4
- Composer
- Node.js >= 18.x
- npm or yarn
- SQLite extension (or MySQL/PostgreSQL for production)

## Installation

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

### 6. Load Sample Data (Optional)
```bash
php bin/console doctrine:fixtures:load
```

This creates:
- 2 sample users:
  - Email: `user1@example.com` / Password: `password123`
  - Email: `user2@example.com` / Password: `password123`
- 3 job boards
- 7 sample job applications

### 7. Build Frontend Assets
```bash
npm run build

# For development with watch mode
npm run watch

# For development server
npm run dev-server
```

## Running the Application

### Development Server
```bash
symfony server:start

# Or using PHP built-in server
php -S localhost:8000 -t public/
```

Visit: `http://localhost:8000`

## Testing

### Run All Tests
```bash
php bin/phpunit
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

### Test Coverage
- **Entity Tests**: User, JobBoard, JobApplication behavior
- **Repository Tests**: Query methods, user isolation, security
- **Controller Tests**: Authentication, authorization, AJAX endpoints
- **Feature Tests**: Complete workflows, multi-user scenarios

### Setup Test Environment
The test environment uses a separate SQLite database:
```bash
php bin/console doctrine:schema:create --env=test
```

## Project Structure

```
kanban-job-board-symfony/
├── assets/
│   ├── controllers/          # Stimulus controllers
│   │   └── kanban_controller.js
│   ├── styles/              # SCSS files
│   └── app.js               # Main JavaScript entry
├── config/
│   ├── packages/            # Bundle configurations
│   └── routes/              # Route definitions
├── migrations/              # Database migrations
├── public/
│   ├── build/              # Compiled assets
│   └── index.php           # Entry point
├── src/
│   ├── Controller/         # Controllers
│   │   ├── JobBoardController.php
│   │   └── SecurityController.php
│   ├── Entity/             # Doctrine entities
│   │   ├── User.php
│   │   ├── JobBoard.php
│   │   └── JobApplication.php
│   ├── Enum/               # PHP enums
│   │   └── JobApplicationStatus.php
│   ├── Form/               # Symfony forms
│   │   ├── JobBoardType.php
│   │   └── JobApplicationType.php
│   ├── Repository/         # Doctrine repositories
│   │   ├── JobBoardRepository.php
│   │   └── JobApplicationRepository.php
│   └── DataFixtures/       # Fixtures
│       └── AppFixtures.php
├── templates/
│   ├── job_board/          # Job board templates
│   │   ├── kanban.html.twig
│   │   └── _application_card.html.twig
│   ├── partials/           # Reusable components
│   │   ├── _header.html.twig
│   │   ├── _job_application_create_modal.html.twig
│   │   └── _job_application_edit_modal.html.twig
│   ├── security/           # Authentication templates
│   └── base.html.twig      # Base template
├── tests/
│   ├── Entity/             # Entity tests
│   ├── Repository/         # Repository tests
│   ├── Controller/         # Controller tests
│   └── Feature/            # Feature/integration tests
├── var/
│   ├── cache/              # Application cache
│   ├── log/                # Log files
│   └── data.db             # SQLite database (dev)
├── vendor/                 # PHP dependencies
├── composer.json
├── package.json
└── webpack.config.js       # Webpack Encore config
```

## Usage Guide

### 1. Register an Account
- Navigate to `/register`
- Create your account with email and password

### 2. Create a Job Board
- Click "New Job Board" from the dashboard
- Enter board name and description
- Submit the form

### 3. View Kanban Board
- Click on any job board from the header dropdown
- You'll see 6 status columns

### 4. Add Job Application
- Click the "+ Add Application" button
- Fill in the application details:
  - Company name (required)
  - Job title (required)
  - Location (required)
  - URL (optional)
  - Salary (optional)
  - Description (required)
  - Initial status
- Submit to create

### 5. Manage Applications
- **View Details**: Click on any application card to view full details
- **Edit**: Click "Edit" in the details modal
- **Delete**: Click "Delete" with confirmation
- **Change Status**: Drag and drop cards between columns

### 6. Track Your Progress
- Each column badge shows the count of applications
- Applications are ordered by creation date (newest first)
- Automatic timestamps track when you:
  - Applied (`appliedAt`)
  - Got an interview (`interviewedAt`)
  - Received an offer (`offeredAt`)
  - Were rejected (`rejectedAt`)

## Development

### Coding Standards
- PSR-12 coding style for PHP
- ES6+ JavaScript with Stimulus conventions
- BEM-like CSS naming for custom styles

### Database Migrations
Create a new migration:
```bash
php bin/console make:migration
```

Run migrations:
```bash
php bin/console doctrine:migrations:migrate
```

### Creating New Entities
```bash
php bin/console make:entity
```

### Asset Compilation
```bash
# Development build
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

### Clearing Cache
```bash
php bin/console cache:clear
```

### Debug Routes
```bash
php bin/console debug:router
```

## API Endpoints

### Kanban Board
- `GET /job-board/{id}/kanban` - Display Kanban board
- `GET /job-board/application/{id}/details` - Get application details (JSON)
- `PATCH /job-board/application/{id}/status` - Update application status (AJAX)

### Application Management
- `POST /job-board/{boardId}/application/create` - Create application (AJAX)
- `POST /job-board/application/{id}/update` - Update application (AJAX)
- `DELETE /job-board/application/{id}/delete` - Delete application (AJAX)

### Authentication
- `GET /login` - Login page
- `POST /login` - Login submission
- `GET /logout` - Logout
- `GET /register` - Registration page
- `POST /register` - Registration submission

## Security Features

- **Password Hashing**: Bcrypt password hashing
- **CSRF Protection**: Built-in Symfony CSRF tokens on all forms
- **XSS Prevention**: Twig auto-escaping
- **SQL Injection Prevention**: Doctrine parameterized queries
- **User Isolation**: Repository-level security checks
- **Authorization**: Role-based access control
- **Session Security**: Secure session handling

## Performance Optimizations

- **Asset Compilation**: Minified JS/CSS in production
- **Doctrine Query Optimization**: Efficient joins and indexing
- **Lazy Loading**: Turbo for SPA-like navigation
- **Cache**: Symfony cache for configuration and routes
- **Database Indexes**: Optimized queries with proper indexing

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Database Connection Errors
```bash
# Verify database URL in .env.local
# For SQLite, ensure var/ directory is writable
chmod -R 775 var/
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
```

### Test Database Issues
```bash
# Recreate test database schema
php bin/console doctrine:schema:drop --force --env=test
php bin/console doctrine:schema:create --env=test
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Contribution Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting PR

## License

This project is proprietary software. All rights reserved.

## Acknowledgments

- Built with [Symfony](https://symfony.com/)
- UI powered by [Bootstrap](https://getbootstrap.com/)
- Drag-and-drop by [SortableJS](https://sortablejs.github.io/Sortable/)
- Icons by [Bootstrap Icons](https://icons.getbootstrap.com/)

## Support

For issues, questions, or contributions, please open an issue on the repository.

---

**Happy Job Hunting!** 🎯
