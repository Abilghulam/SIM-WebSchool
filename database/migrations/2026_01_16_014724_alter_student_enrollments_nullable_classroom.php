<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            // Drop FK dulu sebelum ubah kolom
            $table->dropForeign(['classroom_id']);

            // Drop unique lama
            $table->dropUnique(['student_id', 'school_year_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            // classroom_id jadi nullable
            $table->foreignId('classroom_id')
                ->nullable()
                ->change();

            // Re-create FK dengan nullOnDelete (biar aman kalau kelas dihapus)
            $table->foreign('classroom_id')
                ->references('id')
                ->on('classrooms')
                ->nullOnDelete();

            // Unique soft-delete friendly
            $table->unique(['student_id', 'school_year_id', 'deleted_at'], 'se_student_year_deleted_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropUnique('se_student_year_deleted_unique');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('classroom_id')->nullable(false)->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms');
            $table->unique(['student_id', 'school_year_id']);
        });
    }
};
