<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentImportCommitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageSchoolData') ?? false;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'uuid'],
        ];
    }
}
