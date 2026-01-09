<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? null;

        return [
            'nis' => ['required', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($studentId)],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string'],

            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:30'],

            'status' => ['nullable', 'in:aktif,lulus,pindah,nonaktif'],
            'entry_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],

            // enrollment update (opsional: hanya jika diubah)
            'school_year_id' => ['nullable', 'integer', 'exists:school_years,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'enrollment_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
