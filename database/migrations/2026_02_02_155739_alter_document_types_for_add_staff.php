<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sesuaikan daftar enum sesuai yang ada di DB kamu
        DB::statement("ALTER TABLE document_types MODIFY `for` ENUM('teacher','student','staff') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE document_types MODIFY `for` ENUM('teacher','student') NOT NULL");
    }
};
