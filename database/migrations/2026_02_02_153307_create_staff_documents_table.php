<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('document_type_id')->nullable();

            $table->string('title', 120)->nullable();

            $table->string('file_path', 255);
            $table->string('file_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('staff_id')->references('id')->on('staff');
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->foreign('uploaded_by')->references('id')->on('users');

            $table->index('document_type_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};
