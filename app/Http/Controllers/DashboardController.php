<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Staff;
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
        $nc->syncAdminOperator($user);
        $nc->syncTeacher($user);

        // ==========================
        // DASHBOARD (GURU / WALI KELAS)
        // ==========================
        if (in_array(($user->role_label ?? null), ['guru', 'wali_kelas'], true)) {
            return $this->teacherDashboard($user);
        }

        // ==========================
        // DASHBOARD ADMIN / OPERATOR / LAINNYA
        // ==========================
        return $this->adminDashboard();
    }

    private function teacherDashboard(User $user)
    {
        $activeYearId = SchoolYear::activeId();
        $activeSchoolYear = $activeYearId ? SchoolYear::query()->find($activeYearId) : null;

        $teacher = $user->teacher;

        $payload = [
            'activeSchoolYear' => $activeSchoolYear,
            'assignment' => null,
            'classroom' => null,
            'teacher' => $teacher,
            'account' => $user,
            'stats' => ['students' => 0, 'major' => null],
            'studentsByGender' => collect(),
            'studentsByStatus' => collect(),
            'recentStudents' => collect(),
            'isHomeroomMode' => false,

            'profileChecks' => [
                'missingPhone' => $teacher ? empty($teacher->phone) : true,
                'missingEmail' => $teacher ? empty($teacher->email) : true,
                'missingAddress' => $teacher ? empty($teacher->address) : true,
            ],
        ];

        if ($activeYearId && $user->teacher_id && $user->can('viewMyClass')) {
            $homeroom = $this->buildHomeroomData($activeYearId, (int) $user->teacher_id);

            if ($homeroom['assignment']) {
                $payload = array_merge($payload, $homeroom);
                $payload['isHomeroomMode'] = true;
            } else {
                session()->flash('warning', 'Akun kamu belum terhubung ke wali kelas pada Tahun Ajaran aktif.');
            }
        }

        return view('dashboards.teacher', $payload);
    }

    /**
     * @return array{
     *   assignment: ?\App\Models\HomeroomAssignment,
     *   classroom: ?\App\Models\Classroom,
     *   stats: array{students:int, major:?string},
     *   studentsByGender: \Illuminate\Support\Collection,
     *   studentsByStatus: \Illuminate\Support\Collection,
     *   recentStudents: \Illuminate\Support\Collection
     * }
     */
    private function buildHomeroomData(int $activeYearId, int $teacherId): array
    {
        $assignment = HomeroomAssignment::query()
            ->with(['classroom.major', 'schoolYear'])
            ->where('school_year_id', $activeYearId)
            ->where('teacher_id', $teacherId)
            ->first();

        $classroom = $assignment?->classroom;

        if (!$classroom) {
            return [
                'assignment' => null,
                'classroom' => null,
                'stats' => ['students' => 0, 'major' => null],
                'studentsByGender' => collect(),
                'studentsByStatus' => collect(),
                'recentStudents' => collect(),
            ];
        }

        $studentsTotal = StudentEnrollment::query()
            ->where('school_year_id', $activeYearId)
            ->where('classroom_id', $classroom->id)
            ->where('is_active', true)
            ->count();

        $studentsByGender = Student::query()
            ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
            ->where('se.school_year_id', $activeYearId)
            ->where('se.classroom_id', $classroom->id)
            ->where('se.is_active', true)
            ->selectRaw("COALESCE(NULLIF(students.gender,''), 'Tidak diisi') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $studentsByStatus = Student::query()
            ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
            ->where('se.school_year_id', $activeYearId)
            ->where('se.classroom_id', $classroom->id)
            ->where('se.is_active', true)
            ->selectRaw("COALESCE(NULLIF(students.status,''), 'Tidak diisi') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $recentStudents = Student::query()
            ->join('student_enrollments as se', 'se.student_id', '=', 'students.id')
            ->where('se.school_year_id', $activeYearId)
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

        return [
            'assignment' => $assignment,
            'classroom' => $classroom,
            'stats' => [
                'students' => (int) $studentsTotal,
                'major' => $classroom?->major?->name,
            ],
            'studentsByGender' => $studentsByGender,
            'studentsByStatus' => $studentsByStatus,
            'recentStudents' => $recentStudents,
        ];
    }

    private function adminDashboard()
    {
        $activeYearId = SchoolYear::activeId();

        // ===== Stats cards =====
        $stats = [
            'students' => Student::query()->where('status', 'aktif')->count(),
            'teachers' => Teacher::query()->where('is_active', true)->count(),
            'staff' => Staff::query()->where('is_active', true)->count(),
            'classrooms' => Classroom::query()->count(),
            'activeSchoolYear' => SchoolYear::query()->where('is_active', true)->value('name'),
        ];

        // ===== Charts =====
        // Disarankan pakai TA aktif (biar data relevan). Kalau tidak ada TA aktif, chart major bisa kosong.
        $studentsByMajor = collect();
        if ($activeYearId) {
            $studentsByMajor = DB::table('majors as m')
                ->leftJoin('classrooms as c', 'c.major_id', '=', 'm.id')
                ->leftJoin('student_enrollments as se', function ($join) use ($activeYearId) {
                    $join->on('se.classroom_id', '=', 'c.id')
                        ->where('se.school_year_id', '=', $activeYearId)
                        ->where('se.is_active', '=', true);
                })
                ->selectRaw('m.name as label, COUNT(se.id) as value')
                ->groupBy('m.name')
                ->orderBy('m.name')
                ->get();
        }

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

        $staffByEmployment = DB::table('staff')
            ->selectRaw("COALESCE(NULLIF(employment_status,''), 'Tidak diisi') as label, COUNT(*) as value")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // ===== Alerts =====
        $alerts = [
            'missingActiveSchoolYear' => $activeYearId ? false : true,

            'studentsWithoutActiveEnrollment' => Student::query()
                ->where('status', 'aktif')
                ->whereDoesntHave('enrollments', function ($q) use ($activeYearId) {
                    $q->where('is_active', true);
                    if ($activeYearId) $q->where('school_year_id', $activeYearId);
                })
                ->count(),

            'teachersWithoutAccount' => Teacher::query()
                ->where('is_active', true)
                ->doesntHave('user')
                ->count(),

            'staffWithoutAccount' => Staff::query()
                ->where('is_active', true)
                ->doesntHave('user')
                ->count(),

            // biar relevan: hitung untuk guru + operator (TAS)
            'mustChangePasswordCount' => User::query()
                ->whereIn('role_label', ['guru', 'operator'])
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

        // ===== KPI =====
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

        // ===== Top Classrooms =====
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

        // ===== Recents =====
        $recentStudents = Student::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'nis', 'created_at']);

        $recentTeachers = Teacher::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'nip', 'created_at']);

        $recentStaff = Staff::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'full_name', 'nip', 'created_at']);

        return view('dashboard', compact(
            'stats',
            'studentsByMajor',
            'studentsByGender',
            'teachersByEmployment',
            'staffByEmployment',
            'alerts',
            'kpi',
            'topClassrooms',
            'recentStudents',
            'recentTeachers',
            'recentStaff'
        ));
    }
}
