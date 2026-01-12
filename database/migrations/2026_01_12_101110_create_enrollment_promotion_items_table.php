<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_promotion_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_promotion_id')
                ->constrained('enrollment_promotions')
                ->cascadeOnDelete();

            $table->foreignId('from_classroom_id')->constrained('classrooms');
            $table->foreignId('to_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();

            $table->unsignedTinyInteger('from_grade_level')->nullable();
            $table->unsignedTinyInteger('to_grade_level')->nullable();

            // snapshot jumlah (audit + laporan)
            $table->unsignedInteger('active_enrollments')->default(0); // jumlah enrollment aktif di TA asal untuk kelas ini
            $table->unsignedInteger('moved_students')->default(0);     // benar-benar dibuat enrollment di TA tujuan
            $table->unsignedInteger('graduated_students')->default(0); // kelas 12 => lulus
            $table->unsignedInteger('skipped_students')->default(0);   // status siswa != aktif

            $table->timestamps();

            $table->index(['from_classroom_id'], 'epi_from_cls_idx');
            $table->index(['to_classroom_id'], 'epi_to_cls_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_promotion_items');
    }
};
