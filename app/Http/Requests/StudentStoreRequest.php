<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa diganti policy/permission
    }

    public function rules(): array
    {
        return [
            // biodata inti
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string'],

            // keluarga
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:30'],

            // status
            'status' => ['nullable', 'in:aktif,lulus,pindah,nonaktif'],
            'entry_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],

            // enrollment (riwayat kelas untuk tahun ajaran)
            'school_year_id' => ['required', 'integer', 'exists:school_years,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'enrollment_note' => ['nullable', 'string', 'max:255'],

            // dokumen awal (opsional kalau mau upload saat create)
            'documents' => ['nullable', 'array'],
            'documents.*.document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'documents.*.title' => ['nullable', 'string', 'max:120'],
            'documents.*.file' => ['required_with:documents', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'], // 5MB
        ];
    }
}
