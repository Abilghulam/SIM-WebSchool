<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentBulkPlacementStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageSchoolData') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],

            // classroom boleh kosong
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],

            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
