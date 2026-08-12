<?php

namespace App\Models;

use App\Enums\InterviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'recruiter_id',
        'candidate_id',
        'scheduled_at',
        'mode',
        'location_or_link',
        'meeting_url',
        'calendar_uid',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => InterviewStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Interview $interview) {
            $interview->calendar_uid ??= (string) Str::uuid();
            if (! $interview->meeting_url && in_array($interview->mode, ['online', 'phone'], true)) {
                $interview->meeting_url = 'https://meet.jit.si/recruitpro-'.Str::lower(Str::random(10));
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(InterviewScorecard::class);
    }

    public function googleCalendarUrl(): string
    {
        $start = $this->scheduled_at->copy()->utc()->format('Ymd\THis\Z');
        $end = $this->scheduled_at->copy()->addHour()->utc()->format('Ymd\THis\Z');
        $title = rawurlencode('Interview: '.$this->application?->jobPosting?->title);
        $details = rawurlencode(($this->meeting_url ?: $this->location_or_link) ?? '');
        $location = rawurlencode($this->meeting_url ?: ($this->location_or_link ?? ''));

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}";
    }

    public function toIcs(): string
    {
        $uid = $this->calendar_uid ?: (string) $this->id;
        $dtStart = $this->scheduled_at->copy()->utc()->format('Ymd\THis\Z');
        $dtEnd = $this->scheduled_at->copy()->addHour()->utc()->format('Ymd\THis\Z');
        $summary = 'Interview: '.($this->application?->jobPosting?->title ?? 'RecruitPro');
        $desc = trim(($this->notes ?? '').' '.($this->meeting_url ?? $this->location_or_link ?? ''));
        $location = $this->meeting_url ?: ($this->location_or_link ?? '');

        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//RecruitPro//EN\r\nBEGIN:VEVENT\r\nUID:{$uid}@recruitpro\r\nDTSTAMP:".now()->utc()->format('Ymd\THis\Z')."\r\nDTSTART:{$dtStart}\r\nDTEND:{$dtEnd}\r\nSUMMARY:{$summary}\r\nDESCRIPTION:{$desc}\r\nLOCATION:{$location}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
    }
}
