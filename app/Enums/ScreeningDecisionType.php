<?php

namespace App\Enums;

enum ScreeningDecisionType: string
{
    case AcceptRecommendation = 'accept_recommendation';
    case RejectRecommendation = 'reject_recommendation';
    case Shortlist = 'shortlist';
    case Remove = 'remove';
    case Interview = 'interview';

    public function label(): string
    {
        return match ($this) {
            self::AcceptRecommendation => 'Accepted AI recommendation',
            self::RejectRecommendation => 'Rejected AI recommendation',
            self::Shortlist => 'Manually shortlisted',
            self::Remove => 'Removed from shortlist',
            self::Interview => 'Moved to interview',
        };
    }
}
