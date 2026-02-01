<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->enum('religion', [
                'Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya'
            ])->nullable()->after('birth_date');

            // hanya terpakai jika religion = Lainnya
            $table->string('religion_other', 80)->nullable()->after('religion');

            $table->enum('marital_status', [
                'Kawin','Belum Kawin','Cerai Hidup','Cerai Mati'
            ])->nullable()->after('employment_status');

            $table->index('religion');
            $table->index('marital_status');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['religion']);
            $table->dropIndex(['marital_status']);

            $table->dropColumn(['religion','religion_other','marital_status']);
        });
    }
};
