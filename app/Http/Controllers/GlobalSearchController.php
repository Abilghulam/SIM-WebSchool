<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolYear;
use App\Models\HomeroomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GlobalSearchController extends Controller
{
    public function suggest(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'students' => [],
                'teachers' => [],
            ]);
        }

        $user = $request->user();
        $activeYearId = SchoolYear::activeId();

        // =========================
        // ROLE: ADMIN/OPERATOR/PIMPINAN => full search
        // =========================
        $isFullAccess = $user->canManageSchoolData() || $user->isPimpinan();

        // =========================
        // STUDENTS
        // =========================
        $studentsQ = Student::query()
            ->with(['activeEnrollment.classroom.major'])
            ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });

        if ($isFullAccess) {
            // biarkan full (atau tetap pakai visibleTo kalau kamu butuh filtering internal)
            $studentsQ->visibleTo($user);
        } else {
            // Guru/Wali: hanya siswa kelas yang diampu (TA aktif)
            // Kalau tidak punya assignment / TA aktif kosong => hasil kosong
            if (!$activeYearId || !$user->teacher_id || !Gate::allows('viewMyClass')) {
                $studentsQ->whereRaw('1=0');
            } else {
                $classroomIds = HomeroomAssignment::query()
                    ->where('school_year_id', $activeYearId)
                    ->where('teacher_id', $user->teacher_id)
                    ->pluck('classroom_id');

                $studentsQ->whereHas('enrollments', function ($e) use ($activeYearId, $classroomIds) {
                    $e->where('school_year_id', $activeYearId)
                        ->where('is_active', true)
                        ->whereIn('classroom_id', $classroomIds);
                });
            }
        }

        $students = $studentsQ
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function ($s) use ($user) {
                $enr = $s->activeEnrollment;

                // safety: url hanya diberikan kalau benar-benar boleh view detail
                $canView = Gate::forUser($user)->allows('view', $s);

                return [
                    'type' => 'student',
                    'id' => $s->id,
                    'title' => $s->full_name,
                    'code' => $s->nis,
                    'classroom' => $enr?->classroom?->name,
                    'major' => $enr?->classroom?->major?->name,
                    'url' => $canView ? route('students.show', $s) : null,
                ];
            })
            ->values();

        // =========================
        // TEACHERS
        // =========================
        $teachersQ = Teacher::query()
            ->with([
                'homeroomAssignments' => function ($q2) use ($activeYearId) {
                    if ($activeYearId) {
                        $q2->where('school_year_id', $activeYearId);
                    }
                    $q2->with('classroom');
                },
            ])
            ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%");
            });

        if ($isFullAccess) {
            $teachersQ->visibleTo($user);
        } else {
            // Guru/Wali: hanya diri sendiri (biar gak nirfungsi tapi tetap aman)
            if (!$user->teacher_id) {
                $teachersQ->whereRaw('1=0');
            } else {
                $teachersQ->where('id', $user->teacher_id);
            }
        }

        $teachers = $teachersQ
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function ($t) use ($user) {
                $classes = $t->homeroomAssignments
                    ?->pluck('classroom.name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $canView = Gate::forUser($user)->allows('view', $t);

                return [
                    'type' => 'teacher',
                    'id' => $t->id,
                    'title' => $t->full_name,
                    'code' => $t->nip,
                    'homeroom' => !empty($classes) ? implode(', ', $classes) : null,
                    'url' => $canView ? route('teachers.show', $t) : null,
                ];
            })
            ->values();

        return response()->json([
            'query' => $q,
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $user = $request->user();
        $activeYearId = SchoolYear::activeId();

        $isFullAccess = $user->canManageSchoolData() || $user->isPimpinan();

        // STUDENTS
        $studentsQ = Student::query()
            ->with(['activeEnrollment.classroom.major'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%");
                });
            });

        if ($isFullAccess) {
            $studentsQ->visibleTo($user);
        } else {
            if (!$activeYearId || !$user->teacher_id || !Gate::allows('viewMyClass')) {
                $studentsQ->whereRaw('1=0');
            } else {
                $classroomIds = HomeroomAssignment::query()
                    ->where('school_year_id', $activeYearId)
                    ->where('teacher_id', $user->teacher_id)
                    ->pluck('classroom_id');

                $studentsQ->whereHas('enrollments', function ($e) use ($activeYearId, $classroomIds) {
                    $e->where('school_year_id', $activeYearId)
                        ->where('is_active', true)
                        ->whereIn('classroom_id', $classroomIds);
                });
            }
        }

        $students = $studentsQ
            ->orderBy('full_name')
            ->paginate(10, ['*'], 'students_page')
            ->withQueryString();

        // TEACHERS
        $teachersQ = Teacher::query()
            ->with([
                'homeroomAssignments' => function ($q2) use ($activeYearId) {
                    if ($activeYearId) {
                        $q2->where('school_year_id', $activeYearId);
                    }
                    $q2->with('classroom');
                },
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%");
                });
            });

        if ($isFullAccess) {
            $teachersQ->visibleTo($user);
        } else {
            if (!$user->teacher_id) {
                $teachersQ->whereRaw('1=0');
            } else {
                $teachersQ->where('id', $user->teacher_id);
            }
        }

        $teachers = $teachersQ
            ->orderBy('full_name')
            ->paginate(10, ['*'], 'teachers_page')
            ->withQueryString();

        return view('search.index', compact('q', 'students', 'teachers'));
    }
}
