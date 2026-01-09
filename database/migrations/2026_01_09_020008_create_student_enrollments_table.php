<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years');
            $table->foreignId('classroom_id')->constrained('classrooms');

            $table->boolean('is_active')->default(true);
            $table->string('note', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 1 siswa hanya 1 rombel per tahun ajaran (sesuai konsep "sekedar pendataan")
            $table->unique(['student_id', 'school_year_id']);

            $table->index(['school_year_id', 'classroom_id']);
            $table->index(['student_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
