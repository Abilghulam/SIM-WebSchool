<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // username login alternatif (opsional)
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique()->after('name');
            }

            // label role cepat (opsional, konsep role tetap via tabel roles)
            if (!Schema::hasColumn('users', 'role_label')) {
                $table->string('role_label', 50)->nullable()->after('password');
            }

            // relasi ke guru (akun guru)
            if (!Schema::hasColumn('users', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')->nullable()->after('role_label');
            }

            // status akun
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('teacher_id');
            }

            // tracking login
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
        });

        // FK harus di luar closure supaya aman di MySQL lama
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'teacher_id')) {
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('teachers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('users', 'role_label')) {
                $table->dropColumn('role_label');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
