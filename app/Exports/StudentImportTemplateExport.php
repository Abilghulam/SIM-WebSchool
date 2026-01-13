<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\SchoolYear;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new StudentImportTemplateSheet(),
            new StudentImportReferenceSheet(),
        ];
    }
}
