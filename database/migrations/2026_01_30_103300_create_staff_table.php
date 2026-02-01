<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();

            $table->string('nip', 30)->unique();
            $table->string('full_name', 150);

            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();

            $table->string('employment_status', 50)->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('full_name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
