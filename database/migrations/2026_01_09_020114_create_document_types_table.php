<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 80);
            $table->enum('for', ['student', 'teacher']);
            $table->boolean('is_required')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['for', 'is_required']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
