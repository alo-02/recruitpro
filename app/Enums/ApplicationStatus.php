<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Applied = 'applied';
    case Screening = 'screening';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Applied',
            self::Screening => 'Under Review',
            self::Interview => 'Interview Scheduled',
            self::Offer => 'Offer Received',
            self::Rejected => 'Rejected',
            self::Hired => 'Hired',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Applied => 'bg-secondary',
            self::Screening => 'bg-warning text-dark',
            self::Interview => 'bg-success',
            self::Offer => 'bg-primary',
            self::Rejected => 'bg-danger',
            self::Hired => 'bg-info',
        };
    }
}
