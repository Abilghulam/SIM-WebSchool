<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\Classroom;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = SchoolYear::query()->where('is_active', true)->first();

        if (!$activeYear) {
            throw new \RuntimeException("Tidak ada school_year aktif. Jalankan MasterDataSeeder dulu.");
        }

        $classrooms = Classroom::query()->get();
        if ($classrooms->isEmpty()) {
            throw new \RuntimeException("Classroom masih kosong. Jalankan MasterDataSeeder dulu.");
        }

        $students = Student::factory()->count(100)->create();

        foreach ($students as $student) {
            $classroom = $classrooms->random();

            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_year_id' => $activeYear->id,
                'classroom_id' => $classroom->id,
                'is_active' => true,
                'note' => null,
            ]);
        }
    }
}
