<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('major_id')->constrained('majors');
            $table->unsignedTinyInteger('grade_level'); // 10/11/12 (atau 1/2/3 sesuai kebijakan)
            $table->string('name', 50); // contoh: X TKJ 1

            $table->timestamps();
            $table->softDeletes();

            $table->index('grade_level');
            $table->index(['major_id', 'grade_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
