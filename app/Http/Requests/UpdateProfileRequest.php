<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string', 'max:500'],
            'school' => ['nullable', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'min:2000', 'max:2040'],
            'gpa' => ['nullable', 'string', 'max:10'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
