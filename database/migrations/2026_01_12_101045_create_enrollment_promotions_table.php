<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_promotions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_school_year_id')->constrained('school_years');
            $table->foreignId('to_school_year_id')->constrained('school_years');

            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('executed_at')->nullable();

            // mapping mentah yang dipakai saat promote (audit)
            $table->json('mapping_json');

            // ringkasan (biar gampang tampil di UI)
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('moved_students')->default(0);
            $table->unsignedInteger('graduated_students')->default(0);
            $table->unsignedInteger('skipped_students')->default(0);

            // status log
            $table->string('status', 20)->default('success'); // success|failed
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['from_school_year_id', 'to_school_year_id'], 'ep_from_to_idx');
            $table->index(['executed_by', 'executed_at'], 'ep_exec_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_promotions');
    }
};
