<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentImportTemplateSheet implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'students';
    }

    public function array(): array
    {
        // Header yang dipakai import service kita (snake_case)
        $header = [
            'nis',
            'full_name',
            'gender',            // L / P (boleh kosong)
            'birth_place',
            'birth_date',        // YYYY-MM-DD atau DD/MM/YYYY
            'religion',
            'phone',
            'email',
            'address',
            'father_name',
            'mother_name',
            'guardian_name',
            'parent_phone',
            'status',            // aktif/lulus/pindah/nonaktif
            'entry_year',
            // Enrollment opsional (bisa kosong kalau mode students_only)
            'school_year_id',
            'classroom_id',
            'is_active',         // 1/0 (optional)
            'note',
        ];

        // Contoh baris (biar user kebayang)
        $example1 = [
            '10001',
            'Budi Santoso',
            'L',
            'Bandung',
            '2008-01-20',
            'Islam',
            '08123456789',
            'budi@example.com',
            'Jl. Mawar No. 1',
            'Ayah Budi',
            'Ibu Budi',
            'Paman Budi',
            '08129876543',
            'aktif',
            '2024',
            '', // school_year_id
            '', // classroom_id
            '1',
            'Catatan opsional',
        ];

        $example2 = [
            '10002',
            'Siti Aminah',
            'P',
            'Jakarta',
            '20/02/2008',
            'Islam',
            '08120000000',
            '',
            '',
            '',
            '',
            '',
            '',
            'aktif',
            '2024',
            '',
            '',
            '1',
            '',
        ];

        return [
            $header,
            $example1,
            $example2,
        ];
    }
}
