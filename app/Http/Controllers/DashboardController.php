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
use App\Services\NotificationCenter;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Notifications
        $nc = app(NotificationCenter::class);

        // Admin/Operator: sync system alerts
        $nc->syncAdminOperator($user);

        // Guru/Wali: sync personal alerts
        $nc->syncTeacher($user);

        // ==========================
        // DASHBOARD WALI KELAS
        // ==========================
        if (($user->role_label ?? null) === 'wali_kelas') {

            $activeId = SchoolYear::activeId();
            $activeSchoolYear = SchoolYear::query()->find($activeId);

            // kalau belum berhak (misal belum ada assignment), tampilkan halaman yang "ramah"
            if (!$user->can('viewMyClass')) {
                return view('dashboards.homeroom-teacher', [
                    'activeSchoolYear' => $activeSchoolYear,
                    'assignment' => null,
                    'classroom' => null,

                    // personal
                    'teacher' => $user->teacher,
                    'account' => $user,

                    // class stats
                    'stats' => [
                        'students' => 0,
                        'major' => null,
                    ],
                    'studentsByGender' => collect(),
                    'studentsByStatus' => collect(),
                    'recentStudents' => collect(),
                ])->with('warning', 'Akun wali kelas belum terhubung ke wali kelas aktif (homeroom assignment).');
            }

            $assignment = HomeroomAssignment::query()
                ->with(['classroom.major', 'schoolYear'])
                ->where('school_year_id', $activeId)
                ->where('teacher_id', $user->teacher_id)
                ->first();

            $classroom = $assignment?->classroom;

            $studentsTotal = 0;
            $studentsByGender = collect();
            $studentsByStatus = collect();
            $recentStudents = collect();

            if ($classroom && $activeId) {
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

                // chart status siswa (khusus kelas ini)
                $studentsByStatus = Student::query()
                    ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
                    ->where('se.school_year_id', $activeId)
                    ->where('se.classroom_id', $classroom->id)
                    ->where('se.is_active', true)
                    ->selectRaw("COALESCE(NULLIF(students.status,''), 'Tidak diisi') as label, COUNT(*) as value")
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get();

                // siswa terbaru di kelas (limit 8)
                $recentStudents = Student::query()
                    ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
                    ->where('se.school_year_id', $activeId)
                    ->where('se.classroom_id', $classroom->id)
                    ->where('se.is_active', true)
                    ->select('students.*')
                    ->orderByDesc('students.updated_at')
                    ->limit(8)
                    ->get()
                    ->map(function ($s) use ($classroom) {
                        return [
                            'name' => $s->full_name,
                            'nis' => $s->nis,
                            'classroom' => $classroom?->name,
                            'url' => route('students.show', $s),
                        ];
                    });
            }

            return view('dashboards.homeroom-teacher', [
                'activeSchoolYear' => $activeSchoolYear,
                'assignment' => $assignment,
                'classroom' => $classroom,

                // personal
                'teacher' => $user->teacher,
                'account' => $user,

                'stats' => [
                    'students' => $studentsTotal,
                    'major' => $classroom?->major?->name,
                ],
                'studentsByGender' => $studentsByGender,
                'studentsByStatus' => $studentsByStatus,
                'recentStudents' => $recentStudents,
            ]);
        }

        // ==========================
        // DASHBOARD GURU
        // ==========================
        if (($user->role_label ?? null) === 'guru') {
            $activeSchoolYear = SchoolYear::query()->find(SchoolYear::activeId());
            $teacher = $user->teacher;

            $profileChecks = [
                'missingPhone' => $teacher ? empty($teacher->phone) : true,
                'missingEmail' => $teacher ? empty($teacher->email) : true,
                'missingAddress' => $teacher ? empty($teacher->address) : true,
            ];

            return view('dashboards.teacher', [
                'activeSchoolYear' => $activeSchoolYear,
                'teacher' => $teacher,
                'account' => $user,
                'profileChecks' => $profileChecks,
            ]);
        }

        // ==========================
        // DASHBOARD ADMIN / OPERATOR / LAINNYA
        // ==========================
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
            'missingActiveSchoolYear' => $activeYearId ? false : true,

            'studentsWithoutActiveEnrollment' => Student::query()
                ->where('status', 'aktif')
                ->whereDoesntHave('enrollments', function ($q) use ($activeYearId) {
                    $q->where('is_active', true);
                    if ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    }
                })
                ->count(),

            'teachersWithoutAccount' => Teacher::query()
                ->where('is_active', true)
                ->doesntHave('user')
                ->count(),

            'mustChangePasswordCount' => User::query()
                ->where('role_label', 'guru')
                ->where('must_change_password', true)
                ->count(),

            'homeroomNotAssigned' => $activeYearId
                ? Classroom::query()
                    ->whereDoesntHave('homeroomAssignments', function ($q) use ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    })
                    ->count()
                : 0,
        ];

        $kpi = [
            'activeYearId' => $activeYearId,
            'enrollmentsActive' => $activeYearId
                ? StudentEnrollment::query()
                    ->where('school_year_id', $activeYearId)
                    ->where('is_active', true)
                    ->count()
                : 0,

            'classesWithHomeroom' => $activeYearId
                ? HomeroomAssignment::query()
                    ->where('school_year_id', $activeYearId)
                    ->distinct('classroom_id')
                    ->count('classroom_id')
                : 0,
        ];

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
