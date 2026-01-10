<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // ==========================
        // DASHBOARD WALI KELAS
        // ==========================
        if (($user->role_label ?? null) === 'wali_kelas') {
            // pakai Gate biar konsisten dengan /my-class
            // kalau belum berhak (misal belum ada assignment), tampilkan halaman yang "ramah"
            if (!$user->can('viewMyClass')) {
                return view('dashboards.homeroom-teacher', [
                    'activeSchoolYear' => SchoolYear::query()->find(SchoolYear::activeId()),
                    'assignment' => null,
                    'classroom' => null,
                    'stats' => [
                        'students' => 0,
                        'major' => null,
                    ],
                    'studentsByGender' => collect(),
                    'studentsByStatus' => collect(),
                ])->with('warning', 'Akun wali kelas belum terhubung ke wali kelas aktif (homeroom assignment).');
            }

            $activeId = SchoolYear::activeId();
            $activeSchoolYear = SchoolYear::query()->find($activeId);

            $assignment = HomeroomAssignment::query()
                ->with(['classroom.major', 'schoolYear'])
                ->where('school_year_id', $activeId)
                ->where('teacher_id', $user->teacher_id)
                ->first();

            $classroom = $assignment?->classroom;

            $studentsTotal = 0;
            $studentsByGender = collect();
            $studentsByStatus = collect();

            if ($classroom) {
                // total siswa aktif di kelas ini (TA aktif)
                $studentsTotal = StudentEnrollment::query()
                    ->where('school_year_id', $activeId)
                    ->where('classroom_id', $classroom->id)
                    ->where('is_active', true)
                    ->count();

                // chart gender siswa (khusus kelas ini)
                $studentsByGender = Student::query()
                    ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
                    ->where('se.school_year_id', $activeId)
                    ->where('se.classroom_id', $classroom->id)
                    ->where('se.is_active', true)
                    ->selectRaw("COALESCE(NULLIF(students.gender,''), 'Tidak diisi') as label, COUNT(*) as value")
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                // chart status siswa (aktif/lulus/pindah/nonaktif) khusus kelas ini
                $studentsByStatus = Student::query()
                    ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
                    ->where('se.school_year_id', $activeId)
                    ->where('se.classroom_id', $classroom->id)
                    ->where('se.is_active', true)
                    ->selectRaw("COALESCE(NULLIF(students.status,''), 'Tidak diisi') as label, COUNT(*) as value")
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();
            }

            return view('dashboards.homeroom-teacher', [
                'activeSchoolYear' => $activeSchoolYear,
                'assignment' => $assignment,
                'classroom' => $classroom,
                'stats' => [
                    'students' => $studentsTotal,
                    'major' => $classroom?->major?->name,
                ],
                'studentsByGender' => $studentsByGender,
                'studentsByStatus' => $studentsByStatus,
            ]);
        }

        // ==========================
        // DASHBOARD GLOBAL (ADMIN/OPERATOR/LAINNYA)
        // ==========================
        // Ini meniru data yang dipakai blade Dashboard kamu sekarang :contentReference[oaicite:4]{index=4}
        $stats = [
            'students' => Student::query()->where('status', 'aktif')->count(),
            'teachers' => Teacher::query()->where('is_active', true)->count(),
            'classrooms' => Classroom::query()->count(),
            'activeSchoolYear' => SchoolYear::query()->where('is_active', true)->value('name'),
        ];

        $studentsByMajor = DB::table('majors as m')
            ->leftJoin('classrooms as c', 'c.major_id', '=', 'm.id')
            ->leftJoin('student_enrollments as se', 'se.classroom_id', '=', 'c.id')
            ->where('se.is_active', true)
            ->selectRaw('m.name as label, COUNT(se.id) as value')
            ->groupBy('m.name')
            ->orderBy('m.name')
            ->get();

        $studentsByGender = DB::table('students')
            ->selectRaw("COALESCE(NULLIF(gender,''), 'Tidak diisi') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $teachersByEmployment = DB::table('teachers')
            ->selectRaw("COALESCE(NULLIF(employment_status,''), 'Tidak diisi') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return view('dashboard', compact('stats', 'studentsByMajor', 'studentsByGender', 'teachersByEmployment'));
    }
}
