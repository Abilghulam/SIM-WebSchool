<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Kalau kamu mau bersih total (hapus SEMUA document_types), aktifkan ini:
        Schema::disableForeignKeyConstraints();
        DB::table('document_types')->truncate();
        Schema::enableForeignKeyConstraints();

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

        foreach (['teacher', 'student'] as $for) {
            foreach ($formats as $name) {
                DocumentType::updateOrCreate(
                    ['name' => $name, 'for' => $for],
                    ['is_required' => false]
                );
            }
        }
    }
}
