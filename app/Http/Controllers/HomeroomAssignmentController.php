<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class HomeroomAssignmentController extends BaseController
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', HomeroomAssignment::class);

        $activeSchoolYearId = SchoolYear::activeId(); // dari model kamu
        $activeSchoolYear = $activeSchoolYearId
            ? SchoolYear::query()->find($activeSchoolYearId)
            : null;

        $classrooms = Classroom::query()
            ->with('major')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        $assignments = collect();

        if ($activeSchoolYearId) {
            $assignments = HomeroomAssignment::query()
                ->with(['classroom.major', 'teacher'])
                ->where('school_year_id', $activeSchoolYearId)
                ->orderBy('classroom_id')
                ->get();
        }

        return view('homeroom-assignments.index', compact(
            'activeSchoolYear',
            'activeSchoolYearId',
            'classrooms',
            'teachers',
            'assignments'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', HomeroomAssignment::class);

        $activeSchoolYearId = SchoolYear::activeId();
        if (!$activeSchoolYearId) {
            return back()->with('warning', 'Belum ada Tahun Ajaran aktif. Aktifkan dulu di Master Tahun Ajaran.');
        }

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        // ✅ guard: 1 guru hanya boleh 1 kelas di TA aktif
        $teacherAlreadyAssigned = HomeroomAssignment::query()
            ->where('school_year_id', $activeSchoolYearId)
            ->where('teacher_id', $data['teacher_id'])
            ->where('classroom_id', '!=', $data['classroom_id'])
            ->exists();

        if ($teacherAlreadyAssigned) {
            return back()->with('warning', 'Guru ini sudah menjadi wali kelas untuk kelas lain di Tahun Ajaran aktif.');
        }

        // (Unique classroom sudah ada di DB: (school_year_id, classroom_id))
        HomeroomAssignment::query()->updateOrCreate(
            [
                'school_year_id' => $activeSchoolYearId,
                'classroom_id' => $data['classroom_id'],
            ],
            [
                'teacher_id' => $data['teacher_id'],
            ]
        );

        $existing = HomeroomAssignment::query()
            ->where('school_year_id', $activeSchoolYearId)
            ->where('classroom_id', $data['classroom_id'])
            ->withTrashed()
            ->first();

        $oldTeacherId = $existing?->teacher_id;

        $assignment = HomeroomAssignment::query()->updateOrCreate(
            [
                'school_year_id' => $activeSchoolYearId,
                'classroom_id' => $data['classroom_id'],
            ],
            [
                'teacher_id' => $data['teacher_id'],
            ]
        );

        activity()
            ->useLog('domain')
            ->event('homeroom_assigned')
            ->causedBy($request->user())
            ->performedOn($assignment)
            ->withProperties([
                'school_year_id' => (int) $activeSchoolYearId,
                'classroom_id' => (int) $data['classroom_id'],
                'teacher_id' => (int) $data['teacher_id'],
                'previous_teacher_id' => $oldTeacherId ? (int) $oldTeacherId : null,
                'operation' => $existing ? 'reassign' : 'assign',
            ])
            ->log('Homeroom assignment saved');

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Wali kelas berhasil disimpan.');
    }

    public function destroy(HomeroomAssignment $homeroomAssignment)
    {
        $this->authorize('delete', $homeroomAssignment);

        $props = [
            'school_year_id' => (int) $homeroomAssignment->school_year_id,
            'classroom_id' => (int) $homeroomAssignment->classroom_id,
            'teacher_id' => (int) $homeroomAssignment->teacher_id,
        ];

        $homeroomAssignment->delete();

        activity()
            ->useLog('domain')
            ->event('homeroom_unassigned')
            ->causedBy(request()->user())
            ->withProperties($props)
            ->log('Homeroom assignment deleted');

        $homeroomAssignment->delete();

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Penugasan wali kelas berhasil dihapus.');
    }

}
