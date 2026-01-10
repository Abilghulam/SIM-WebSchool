<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Illuminate\Http\Request;
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
            return back()->with('success', 'Belum ada Tahun Ajaran aktif. Aktifkan dulu di Master Tahun Ajaran.');
        }

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        DB::transaction(function () use ($data, $activeSchoolYearId) {
            // Upsert: untuk 1 kelas pada tahun ajaran aktif, update jika sudah ada.
            HomeroomAssignment::query()->updateOrCreate(
                [
                    'school_year_id' => $activeSchoolYearId,
                    'classroom_id' => $data['classroom_id'],
                ],
                [
                    'teacher_id' => $data['teacher_id'],
                ]
            );
        });

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Wali kelas berhasil disimpan.');
    }

    public function destroy(HomeroomAssignment $homeroomAssignment)
    {
        $this->authorize('delete', $homeroomAssignment);

        $homeroomAssignment->delete();

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Penugasan wali kelas berhasil dihapus.');
    }
}
