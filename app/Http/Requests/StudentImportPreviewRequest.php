<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentImportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageSchoolData') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'], // 10MB
            'mode' => ['required', 'in:students_only,students_with_enrollment'],
            'strategy' => ['required', 'in:create_only,upsert_by_nis'],
            'default_school_year_id' => ['nullable', 'integer'],
            'default_classroom_id' => ['nullable', 'integer'],
            'default_enrollment_is_active' => ['nullable', 'boolean'],
        ];
    }

    public function validatedOptions(): array
    {
        $v = $this->validated();

        return [
            'mode' => $v['mode'],
            'strategy' => $v['strategy'],
            'default_school_year_id' => $v['default_school_year_id'] ?? null,
            'default_classroom_id' => $v['default_classroom_id'] ?? null,
            'default_enrollment_is_active' => array_key_exists('default_enrollment_is_active', $v)
                ? (bool) $v['default_enrollment_is_active']
                : true,
        ];
    }
}
