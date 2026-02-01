<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id ?? null;

        return [
            'nip' => ['required', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacherId)],
            'full_name' => ['required', 'string', 'max:150'],

            'gender' => ['nullable', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],

            // NEW
            'religion' => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya'],
            'religion_other' => ['nullable', 'string', 'max:80'],
            'marital_status' => ['nullable', 'in:Kawin,Belum Kawin,Cerai Hidup,Cerai Mati'],

            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string'],

            'employment_status' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $religion = $this->input('religion');
            $other = trim((string) $this->input('religion_other'));

            if ($religion === 'Lainnya' && $other === '') {
                $v->errors()->add('religion_other', 'Jika agama "Lainnya", maka wajib isi keterangan agama.');
            }
        });
    }
}
