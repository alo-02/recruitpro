<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostingRequest extends FormRequest
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
            'location_type' => ['required', 'in:remote,onsite,hybrid'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'string', 'max:50'],
            'salary_max' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:50'],
            'skills' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:draft,active'],
        ];
    }
}
