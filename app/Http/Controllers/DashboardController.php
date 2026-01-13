<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
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

        $activeYearId = SchoolYear::activeId();

        $alerts = [
            // 1) Tahun ajaran aktif wajib ada
            'missingActiveSchoolYear' => $activeYearId ? false : true,

            // 2) Siswa status aktif tapi tidak punya enrollment aktif (lebih penting kalau ada TA aktif)
            'studentsWithoutActiveEnrollment' => Student::query()
                ->where('status', 'aktif')
                ->whereDoesntHave('enrollments', function ($q) use ($activeYearId) {
                    $q->where('is_active', true);

                    if ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    }
                })
                ->count(),

            // 3) Guru aktif tapi belum punya akun login
            'teachersWithoutAccount' => Teacher::query()
                ->where('is_active', true)
                ->doesntHave('user')
                ->count(),

            // 4) Akun guru yang masih must_change_password
            // (anggap kolom must_change_password ada di users)
            'mustChangePasswordCount' => User::query()
                ->where('role_label', 'guru')
                ->where('must_change_password', true)
                ->count(),

            // 5) Kelas pada TA aktif yang belum punya wali kelas
            // (kalau belum ada TA aktif => 0 biar aman)
            'homeroomNotAssigned' => $activeYearId
                ? Classroom::query()
                    ->whereDoesntHave('homeroomAssignments', function ($q) use ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    })
                    ->count()
                : 0,
        ];

        /**
         * KPI tambahan (TA aktif)
         */
        $kpi = [
            'activeYearId' => $activeYearId,
            'enrollmentsActive' => $activeYearId
                ? StudentEnrollment::query()->where('school_year_id', $activeYearId)->where('is_active', true)->count()
                : 0,

            'classesWithHomeroom' => $activeYearId
                ? HomeroomAssignment::query()->where('school_year_id', $activeYearId)->distinct('classroom_id')->count('classroom_id')
                : 0,
        ];

        /**
         * Top kelas terbanyak siswa (TA aktif)
         */
        $topClassrooms = collect();
        if ($activeYearId) {
            $topClassrooms = Classroom::query()
                ->with('major')
                ->select('classrooms.*')
                ->selectSub(function ($q) use ($activeYearId) {
                    $q->from('student_enrollments as se')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('se.classroom_id', 'classrooms.id')
                        ->where('se.school_year_id', $activeYearId)
                        ->where('se.is_active', true);
                }, 'students_count')
                ->orderByDesc('students_count')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->limit(5)
                ->get();
        }

        /**
         * Data terbaru (buat kesan “hidup”)
         * NOTE: asumsi created_at ada (default Laravel). Kalau ternyata kamu disable timestamps, bilang ya.
         */
        $recentStudents = Student::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'nis', 'created_at']);

        $recentTeachers = Teacher::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'nip', 'created_at']);

        return view('dashboard', compact(
            'stats',
            'studentsByMajor',
            'studentsByGender',
            'teachersByEmployment',
            'alerts',
            'kpi',
            'topClassrooms',
            'recentStudents',
            'recentTeachers'
        ));

    }
}
