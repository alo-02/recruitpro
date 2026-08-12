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
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Applied',
            self::Screening => 'Under Review',
            self::Interview => 'Interview Scheduled',
            self::Offer => 'Offer Received',
            self::Rejected => 'Rejected',
            self::Hired => 'Hired',
            self::Withdrawn => 'Withdrawn',
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
            self::Withdrawn => 'bg-dark',
        };
    }

    public function canWithdraw(): bool
    {
        return in_array($this, [self::Applied, self::Screening], true);
    }
}
