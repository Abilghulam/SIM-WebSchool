<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentBulkPlacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageSchoolData') ?? false;
    }

    public function rules(): array
    {
        return [
            // filter
            'search' => ['nullable', 'string', 'max:150'],
            'entry_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],

            // target
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'note' => ['nullable', 'string', 'max:255'],

            // mode eksekusi
            'apply_mode' => ['required', 'in:selected,all_filtered'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ];
    }

    public function validatedFilters(): array
    {
        $v = $this->validated();
        return [
            'search' => $v['search'] ?? null,
            'entry_year' => $v['entry_year'] ?? null,
        ];
    }
}
