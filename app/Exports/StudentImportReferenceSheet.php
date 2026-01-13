<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\SchoolYear;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentImportReferenceSheet implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'referensi';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['REFERENSI SCHOOL YEARS'];
        $rows[] = ['school_year_id', 'name', 'is_active'];

        foreach (SchoolYear::query()->orderByDesc('is_active')->orderByDesc('id')->get(['id','name','is_active']) as $sy) {
            $rows[] = [$sy->id, $sy->name, $sy->is_active ? 1 : 0];
        }

        $rows[] = [];
        $rows[] = ['REFERENSI CLASSROOMS'];
        $rows[] = ['classroom_id', 'name', 'grade_level', 'major_id'];

        foreach (Classroom::query()->orderBy('grade_level')->orderBy('name')->get(['id','name','grade_level','major_id']) as $c) {
            $rows[] = [$c->id, $c->name, $c->grade_level, $c->major_id];
        }

        $rows[] = [];
        $rows[] = ['CATATAN'];
        $rows[] = ['- Isi students.school_year_id & students.classroom_id pakai referensi di atas.'];
        $rows[] = ['- Kalau tidak ingin import enrollment, kosongkan kolom school_year_id & classroom_id.'];
        $rows[] = ['- gender: L atau P (boleh kosong). status: aktif/lulus/pindah/nonaktif.'];

        return $rows;
    }
}
