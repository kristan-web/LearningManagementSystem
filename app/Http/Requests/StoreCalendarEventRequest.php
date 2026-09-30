<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['Student', 'Teacher', 'Admin'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after_or_equal:start_datetime'],
            'section_id' => ['nullable', 'exists:class_sections,section_id'],
            'subject_id' => ['nullable', 'exists:subjects,subject_id'],
        ];
    }
}
