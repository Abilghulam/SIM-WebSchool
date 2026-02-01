<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolYear;
use App\Models\Staff;
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
            'staff' => [],
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

        // =========================
        // STAFF (TAS)
        // =========================
        $staffQ = Staff::query()
            ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%");
            });

        if ($isFullAccess) {
            // optional: kalau kamu punya scope visibleTo dan ingin konsisten
            $staffQ->visibleTo($user);
        } else {
            // selain full access, hanya boleh dirinya sendiri (kalau staff_id ada)
            if (!$user->staff_id) {
                $staffQ->whereRaw('1=0');
            } else {
                $staffQ->whereKey($user->staff_id);
            }
        }

        $staff = $staffQ
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function ($st) use ($user) {
                $canView = Gate::forUser($user)->allows('view', $st);

                return [
                    'type' => 'staff',
                    'id' => $st->id,
                    'title' => $st->full_name,
                    'code' => $st->nip,
                    'url' => $canView ? route('staff.show', $st) : null,
                ];
            })
            ->values();

        return response()->json([
            'query' => $q,
            'students' => $students,
            'teachers' => $teachers,
            'staff' => $staff,
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

        // STAFF
        $staffQ = Staff::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%");
                });
            });

        if ($isFullAccess) {
            $staffQ->visibleTo($user);
        } else {
            if (!$user->staff_id) {
                $staffQ->whereRaw('1=0');
            } else {
                $staffQ->whereKey($user->staff_id);
            }
        }

        $staffs = $staffQ
            ->orderBy('full_name')
            ->paginate(10, ['*'], 'staff_page')
            ->withQueryString();

        return view('search.index', compact('q', 'students', 'teachers', 'staffs'));
    }
}
