<?php

namespace App\Http\Controllers;

use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class MyStudentController extends BaseController
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewMyClass');

        $user = $request->user();

        $activeSchoolYearId = SchoolYear::activeId();
        $activeSchoolYear = $activeSchoolYearId
            ? SchoolYear::query()->find($activeSchoolYearId)
            : null;

        $assignment = HomeroomAssignment::query()
            ->with(['classroom.major', 'schoolYear'])
            ->where('school_year_id', $activeSchoolYearId)
            ->where('teacher_id', $user->teacher_id)
            ->firstOrFail();

        $classroom = $assignment->classroom;

        $students = Student::query()
            ->whereHas('enrollments', function ($q) use ($activeSchoolYearId, $classroom) {
                $q->where('school_year_id', $activeSchoolYearId)
                ->where('classroom_id', $classroom->id)
                ->where('is_active', 1);
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('my-class.index', compact(
            'activeSchoolYear',
            'assignment',
            'classroom',
            'students'
        ));
    }
}
