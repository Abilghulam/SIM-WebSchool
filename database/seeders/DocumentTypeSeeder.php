<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $formats = [
            // Dokumen
            'PDF',
            'DOC',
            'DOCX',
            'XLS',
            'XLSX',
            'PPT',
            'PPTX',

            // Gambar
            'JPG',
            'JPEG',
            'PNG',
        ];

        $for = 'staff';

        foreach ($formats as $name) {
            DocumentType::updateOrCreate(
                ['name' => $name, 'for' => $for],
                ['is_required' => false]
            );
        }
    }
}
