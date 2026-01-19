<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentBulkPlacementStoreRequest;
use App\Services\StudentBulkPlacementService;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class StudentBulkPlacementController extends BaseController
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('manageSchoolData');

        $activeYear = SchoolYear::query()->where('is_active', true)->first();
        abort_if(!$activeYear, 422, 'Tidak ada Tahun Ajaran aktif. Aktifkan dulu Tahun Ajaran.');
        abort_if($activeYear->is_locked, 422, 'Tahun Ajaran aktif sedang terkunci. Penempatan massal tidak dapat dilakukan.');

        $activeYearId = (int) $activeYear->id;

        // Kandidat:
        // A) siswa aktif yang BELUM punya enrollment aktif di TA aktif
        // OR
        // B) siswa aktif yang SUDAH punya enrollment aktif di TA aktif tapi classroom_id masih NULL
        $q = Student::query()
            ->where('status', 'aktif')
            ->where(function ($w) use ($activeYearId) {
                $w->whereDoesntHave('enrollments', function ($e) use ($activeYearId) {
                    $e->where('school_year_id', $activeYearId)
                      ->where('is_active', true);
                })
                ->orWhereHas('enrollments', function ($e) use ($activeYearId) {
                    $e->where('school_year_id', $activeYearId)
                      ->where('is_active', true)
                      ->whereNull('classroom_id');
                });
            })
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $s = $request->string('search')->trim()->toString();
            $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                  ->orWhere('nis', 'like', "%{$s}%");
            });
        }

        if ($request->filled('entry_year')) {
            $q->where('entry_year', (int) $request->input('entry_year'));
        }

        $students = $q->paginate(15)->withQueryString();

        return view('enrollments.bulk-placement', [
            'students' => $students,
            'classrooms' => Classroom::query()
                ->with('major')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(),
            'activeYear' => $activeYear,
        ]);
    }


    public function store(StudentBulkPlacementStoreRequest $request, StudentBulkPlacementService $service)
    {
        $this->authorize('manageSchoolData');

        $studentIds = collect($request->validated('student_ids'))->map(fn ($x) => (int) $x)->values()->all();
        $classroomId = $request->validated('classroom_id'); // boleh null
        $note = $request->validated('note');

        $summary = $service->placeSelected($studentIds, $classroomId, $note, $request->user());

        return redirect()
            ->route('enrollments.bulk-placement.index')
            ->with(
                'success',
                "Penempatan massal selesai. Diproses: {$summary['processed']}. Dibuat: {$summary['created']}. Diperbarui: {$summary['updated']}. Dilewati: {$summary['skipped']}."
            );
    }
}
