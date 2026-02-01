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
            'nis'  => ['required', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($studentId)],
            'nisn' => ['required', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($studentId)],
            'nik'  => ['nullable', 'string', 'max:20'],

            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string'],

            'origin_school' => ['nullable', 'string', 'max:150'],

            'is_kip'     => ['nullable', 'boolean'],
            'kip_number' => ['nullable', 'string', 'max:30', 'required_if:is_kip,1'],

            'father_name' => ['nullable', 'string', 'max:150'],
            'father_job'  => ['nullable', 'string', 'max:120'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'mother_job'  => ['nullable', 'string', 'max:120'],
            'parent_phone' => ['nullable', 'string', 'max:30'],

            'status' => ['nullable', 'in:aktif,lulus,pindah,nonaktif'],
            'entry_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],

            'school_year_id' => ['nullable', 'integer', 'exists:school_years,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'enrollment_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
