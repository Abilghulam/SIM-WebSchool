<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // === STUDENTS ===
        $students = Student::query()
            ->visibleTo($user)
            ->with(['activeEnrollment.classroom.major'])
            ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                  ->orWhere('nis', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function ($s) {
                $enr = $s->activeEnrollment;
                return [
                    'type' => 'student',
                    'id' => $s->id,
                    'title' => $s->full_name,
                    'code' => $s->nis,
                    'classroom' => $enr?->classroom?->name,
                    'major' => $enr?->classroom?->major?->name,
                    'url' => route('students.show', $s),
                ];
            });

        // === TEACHERS ===
        // kamu minta tampil "wali kelas mengampu kelas apa"
        // kita ambil kelas di TA aktif (kalau ada) lewat relasi homeroomAssignments
        $activeYearId = SchoolYear::activeId();

        $teachers = Teacher::query()
            ->visibleTo($user)
            ->with([
                'homeroomAssignments' => function ($q) use ($activeYearId) {
                    if ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    }
                    $q->with('classroom');
                },
            ])
            ->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                  ->orWhere('nip', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->limit(8)
            ->get()
            ->map(function ($t) {
                $classes = $t->homeroomAssignments
                    ?->pluck('classroom.name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'type' => 'teacher',
                    'id' => $t->id,
                    'title' => $t->full_name,
                    'code' => $t->nip,
                    'homeroom' => !empty($classes) ? implode(', ', $classes) : null,
                    'url' => route('teachers.show', $t),
                ];
            });

        return response()->json([
            'query' => $q,
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Optional tapi recommended: halaman hasil lengkap (Enter = ke sini)
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $user = $request->user();

        $activeYearId = SchoolYear::activeId();

        $students = Student::query()
            ->visibleTo($user)
            ->with(['activeEnrollment.classroom.major'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('full_name', 'like', "%{$q}%")
                      ->orWhere('nis', 'like', "%{$q}%");
                });
            })
            ->orderBy('full_name')
            ->paginate(10, ['*'], 'students_page')
            ->withQueryString();

        $teachers = Teacher::query()
            ->visibleTo($user)
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
            })
            ->orderBy('full_name')
            ->paginate(10, ['*'], 'teachers_page')
            ->withQueryString();

        return view('search.index', compact('q', 'students', 'teachers'));
    }

}
