<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homeroom_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_year_id')->constrained('school_years');
            $table->foreignId('classroom_id')->constrained('classrooms');
            $table->foreignId('teacher_id')->constrained('teachers');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_year_id', 'classroom_id']); // 1 rombel 1 wali / TA
            // optional: 1 guru 1 kelas / TA
            // $table->unique(['school_year_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeroom_assignments');
    }
};
