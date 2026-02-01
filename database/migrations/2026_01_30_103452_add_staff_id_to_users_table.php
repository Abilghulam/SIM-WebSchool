<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable()->after('teacher_id');
            $table->index('staff_id');

            $table->foreign('staff_id')
                ->references('id')->on('staff')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropIndex(['staff_id']);
            $table->dropColumn('staff_id');
        });
    }
};
