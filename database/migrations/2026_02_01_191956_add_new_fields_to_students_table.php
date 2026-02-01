<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // NEW
            $table->string('nisn', 20)->nullable()->after('nis');
            $table->string('nik', 20)->nullable()->after('nisn');

            $table->boolean('is_kip')->nullable()->after('nik'); // null untuk data lama
            $table->string('kip_number', 30)->nullable()->after('is_kip');

            $table->string('origin_school', 150)->nullable()->after('entry_year');

            // ganti "Nama Wali" => pekerjaan orang tua
            $table->string('father_job', 120)->nullable()->after('father_name');
            $table->string('mother_job', 120)->nullable()->after('mother_name');

            // guardian_name jangan langsung drop dulu biar aman (nanti migration terpisah)
            // $table->dropColumn('guardian_name');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nisn',
                'nik',
                'is_kip',
                'kip_number',
                'origin_school',
                'father_job',
                'mother_job',
            ]);
        });
    }
};
