<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentBulkPlacementStoreRequest;
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

    public function store(StudentBulkPlacementStoreRequest $request)
    {
        $this->authorize('manageSchoolData');

        $activeYear = SchoolYear::query()->where('is_active', true)->first();
        abort_if(!$activeYear, 422, 'Tidak ada Tahun Ajaran aktif. Aktifkan dulu Tahun Ajaran.');
        abort_if($activeYear->is_locked, 422, 'Tahun Ajaran aktif sedang terkunci. Penempatan massal tidak dapat dilakukan.');

        $activeYearId = (int) $activeYear->id;

        $studentIds = collect($request->validated('student_ids'))->map(fn ($x) => (int) $x)->values()->all();
        $classroomId = $request->validated('classroom_id'); // boleh null
        $note = $request->validated('note');

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $studentIds, $activeYearId, $classroomId, $note,
            &$processed, &$created, &$updated, &$skipped
        ) {
            // Ambil siswa yang valid + status aktif
            $students = Student::query()
                ->whereIn('id', $studentIds)
                ->where('status', 'aktif')
                ->get(['id', 'status']);

            $foundIds = $students->pluck('id')->map(fn ($x) => (int) $x)->all();
            $skipped += max(0, count($studentIds) - count($foundIds));

            foreach ($students as $student) {
                $processed++;

                // Cari enrollment aktif pada TA aktif
                $enr = StudentEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('school_year_id', $activeYearId)
                    ->where('is_active', true)
                    ->first();

                // Case 1: belum ada enrollment aktif di TA aktif => buat baru
                if (!$enr) {
                    StudentEnrollment::query()->create([
                        'student_id' => $student->id,
                        'school_year_id' => $activeYearId,
                        'classroom_id' => $classroomId, // null boleh
                        'is_active' => true,
                        'note' => $note,
                    ]);
                    $created++;
                    continue;
                }

                // Case 2: sudah ada enrollment aktif tapi kelas kosong => update isi kelas (atau biarkan null)
                if ($enr->classroom_id === null) {
                    $enr->update([
                        'classroom_id' => $classroomId, // null tetap boleh
                        'note' => $note,
                    ]);
                    $updated++;
                    continue;
                }

                // Case 3: sudah punya kelas => skip
                $skipped++;
            }
        });

        return redirect()
            ->route('enrollments.bulk-placement.index')
            ->with('success', "Penempatan massal selesai. Diproses: {$processed}. Dibuat: {$created}. Diperbarui: {$updated}. Dilewati: {$skipped}.");
    }
}
