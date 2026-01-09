<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('username', 50)->nullable()->unique();
            $table->string('email', 120)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            // Konsep role/permission akan ditangani via tabel roles/permissions nanti (Spatie),
            // tapi ini field opsional untuk label cepat (boleh dipakai atau diabaikan).
            $table->string('role_label', 50)->nullable();

            // Link ke teachers (FK-nya kita tambahkan belakangan setelah tabel teachers dibuat)
            $table->unsignedBigInteger('teacher_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('teacher_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
