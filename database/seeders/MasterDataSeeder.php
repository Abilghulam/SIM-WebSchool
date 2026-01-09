<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;
use App\Models\Classroom;
use App\Models\SchoolYear;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Tahun ajaran
        $sy = SchoolYear::query()->firstOrCreate(
            ['name' => '2025/2026'],
            ['is_active' => true]
        );

        // Kalau sudah ada beberapa school year, pastikan setidaknya ada 1 aktif
        if (!SchoolYear::query()->where('is_active', true)->exists()) {
            $sy->is_active = true;
            $sy->save();
        }

        // 2) Jurusan (opsional, tapi bagus)
        // Sesuaikan kolom: kalau major kamu tidak punya 'code', hapus bagian 'code'
        $majorsData = [
            ['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak'],
            ['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan'],
            ['code' => 'MM',  'name' => 'Multimedia'],
        ];

        $majors = collect();

        foreach ($majorsData as $m) {
            // Kalau model Major kamu tidak punya code, ganti jadi ['name' => ...] saja
            $majors->push(
                Major::query()->firstOrCreate(
                    ['code' => $m['code']],
                    ['name' => $m['name']]
                )
            );
        }

        // 3) Kelas (WAJIB)
        // Sesuaikan kolom classroom kamu: minimal harus ada name, grade_level, major_id
        // Kalau kolom kamu beda, bilang ya—nanti aku sesuaikan.
        if (!Classroom::query()->exists()) {
            foreach ($majors as $major) {
                foreach ([10, 11, 12] as $grade) {
                    // Buat 2 rombel per tingkat per jurusan (A, B)
                    foreach (['A', 'B'] as $suffix) {
                        Classroom::create([
                            'major_id' => $major->id,
                            'grade_level' => $grade,
                            'name' => "{$grade} {$major->code} {$suffix}",
                        ]);
                    }
                }
            }
        }
    }
}
