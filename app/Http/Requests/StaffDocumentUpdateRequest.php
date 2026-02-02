<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffDocumentUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'document_type_id' => [
                'nullable',
                'integer',
                Rule::exists('document_types', 'id')->where('for', 'staff'),
            ],
            'title' => ['nullable', 'string', 'max:120'],
            'file' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}
