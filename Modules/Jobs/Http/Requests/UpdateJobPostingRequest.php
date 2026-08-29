<?php

namespace Modules\Jobs\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:internship,full-time,contract'],
            'category' => ['required', 'in:'.implode(',', array_column(\App\Enums\JobCategory::cases(), 'value'))],
            'location_type' => ['required', 'in:remote,onsite,hybrid'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'string', 'max:50'],
            'salary_max' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:50'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'ats_keywords' => ['required', 'string', 'max:1000'],
            'ats_min_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,active,closed'],
        ];
    }
}
