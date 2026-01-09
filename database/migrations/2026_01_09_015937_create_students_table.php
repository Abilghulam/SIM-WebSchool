<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('nis', 20)->unique();
            $table->string('full_name', 150);

            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();

            $table->string('religion', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();

            $table->string('father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('guardian_name', 150)->nullable();
            $table->string('parent_phone', 30)->nullable();

            $table->enum('status', ['aktif', 'lulus', 'pindah', 'nonaktif'])->default('aktif');
            $table->unsignedSmallInteger('entry_year')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('full_name');
            $table->index('status');
            $table->index('entry_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
