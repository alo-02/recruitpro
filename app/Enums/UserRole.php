<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Recruiter = 'recruiter';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Candidate',
            self::Recruiter => 'Recruiter',
            self::Admin => 'Administrator',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Student => 'student.dashboard',
            self::Recruiter => 'recruiter.dashboard',
            self::Admin => 'admin.index',
        };
    }
}
