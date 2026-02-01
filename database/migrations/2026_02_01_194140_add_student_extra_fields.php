<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // NEW identity
            $table->string('nisn', 20)->nullable()->after('nis');
            $table->string('nik', 20)->nullable()->after('nisn');

            // NEW KIP
            $table->boolean('is_kip')->nullable()->after('nik');      // null utk data lama
            $table->string('kip_number', 30)->nullable()->after('is_kip');

            // NEW origin school
            $table->string('origin_school', 150)->nullable()->after('entry_year');

            // replace guardian => parent jobs
            $table->string('father_job', 120)->nullable()->after('father_name');
            $table->string('mother_job', 120)->nullable()->after('mother_name');

            // indexes
            $table->unique('nisn'); // ✅ unique DB (multiple NULL allowed)
            $table->index('is_kip');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['nisn']);
            $table->dropIndex(['is_kip']);

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
