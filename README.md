# RecruitPro — Internship & Job Portal

Enterprise-grade recruitment platform built with **Laravel 12**, **Bootstrap 5**, and **JavaScript**.

## Features

- **Role-Based Access Control** — Student, Recruiter, Admin roles with middleware protection
- **Authentication** — Login, registration, logout with rate limiting & CSRF protection
- **Job Postings** — Search, filter, post, manage, and apply to jobs
- **Application Tracking** — Students track applications; recruiters manage pipeline
- **Profile Builder** — Skills, education, resume upload with completeness score
- **Talent Pool** — Recruiters browse candidate profiles
- **Admin Dashboard** — Platform overview and user management

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL

## Setup

```bash
cd recruitpro
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Visit: **http://127.0.0.1:8000**

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Student | student@recruitpro.com | password |
| Recruiter | recruiter@recruitpro.com | password |
| Admin | admin@recruitpro.com | password |

## Architecture

```
app/
├── Enums/          # UserRole, ApplicationStatus, JobStatus
├── Http/
│   ├── Controllers/  # Auth, Dashboard, JobPosting, Application, Profile
│   ├── Middleware/   # EnsureUserHasRole (RBAC)
│   └── Requests/     # Form validation (Login, Register, Job, Profile)
├── Models/         # User, Profile, Company, JobPosting, Application
└── Policies/       # JobPostingPolicy, ApplicationPolicy
```

## Routes

| Area | Middleware | Examples |
|------|-----------|----------|
| Public | — | `/`, `/jobs`, `/jobs/{slug}` |
| Guest | guest | `/login`, `/register` |
| Student | auth + role:student | `/student/dashboard`, `/applications` |
| Recruiter | auth + role:recruiter | `/recruiter/dashboard`, `/jobs/post` |
| Admin | auth + role:admin | `/admin` |

## Testing

```bash
php artisan test
```

## Tech Stack

- Laravel 12 (PHP)
- Bootstrap 5.3 (CDN)
- Vanilla JavaScript
- SQLite database
- Blade templating
