<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\StudentEnrollment;
use App\Services\EnrollmentPromotionService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class StudentPromotionController extends BaseController
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('manageSchoolData');

        $fromYearId = (int) ($request->get('from_year_id') ?: SchoolYear::activeId());
        $toYearId   = (int) ($request->get('to_year_id') ?: 0);

        $fromYear = $fromYearId ? SchoolYear::query()->find($fromYearId) : null;
        $toYear   = $toYearId ? SchoolYear::query()->find($toYearId) : null;

        // ✅ hard guard
        if (!$fromYear) {
            abort(404, 'TA asal tidak ditemukan.');
        }
        if (!$fromYear->is_active) {
            abort(403, 'TA asal harus dalam status aktif.');
        }
        if ($fromYear->is_locked) {
            abort(403, 'TA asal sudah dikunci (promote sudah dilakukan).');
        }

        $schoolYears = SchoolYear::query()->orderByDesc('start_date')->get();

        // Kelas sumber: kelas yang memiliki enrollment aktif di TA asal
        $fromClassrooms = Classroom::query()
            ->whereHas('enrollments', function ($q) use ($fromYearId) {
                $q->where('school_year_id', $fromYearId)
                  ->where('is_active', 1);
            })
            ->with('major')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        // Kelas tujuan: untuk dropdown mapping
        $toClassrooms = collect();
        if ($toYearId) {
            $toClassrooms = Classroom::query()
                ->with('major')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get();
        }

        // Default mapping otomatis: "10 RPL A" -> "11 RPL A"
        $defaultMap = [];
        foreach ($fromClassrooms as $c) {
            if ((int) $c->grade_level >= 12) {
                $defaultMap[$c->id] = null; // kelas 12 -> lulus
                continue;
            }

            $rest = preg_replace('/^\s*\d+\s+/', '', (string) $c->name); // "RPL A"
            $targetName = ((int) $c->grade_level + 1) . ' ' . $rest;

            $dest = $toClassrooms->firstWhere('name', $targetName);

            // fallback: grade_level+1 dan major sama (kalau ada beda penamaan)
            if (!$dest) {
                $dest = $toClassrooms->first(function ($x) use ($c) {
                    return (int) $x->grade_level === ((int) $c->grade_level + 1)
                        && (int) $x->major_id === (int) $c->major_id;
                });
            }

            $defaultMap[$c->id] = $dest?->id;
        }

        // --- PREVIEW JUMLAH SISWA PER KELAS (TA ASAL) ---
        $fromCounts = StudentEnrollment::query()
            ->where('school_year_id', $fromYearId)
            ->where('is_active', 1)
            ->selectRaw('classroom_id, COUNT(*) as total')
            ->groupBy('classroom_id')
            ->pluck('total', 'classroom_id'); // [classroom_id => total]

        // total siswa aktif di TA asal (biar gampang tampil)
        $totalFromStudents = (int) $fromCounts->sum();

        // --- PROYEKSI KE TA TUJUAN (berdasarkan defaultMap / mapping yang tampil) ---
        $toProjectedCounts = collect(); // [to_classroom_id => total]
        $graduateCount = 0;

        foreach ($defaultMap as $fromClassId => $toClassId) {
            $count = (int) ($fromCounts[$fromClassId] ?? 0);

            if (!$toClassId) {
                // kelas 12 / mapping null => lulus
                $graduateCount += $count;
                continue;
            }

            $toProjectedCounts[$toClassId] = (int) ($toProjectedCounts[$toClassId] ?? 0) + $count;
        }

        return view('enrollments.promote', compact(
            'schoolYears',
            'fromYearId',
            'toYearId',
            'fromYear',
            'toYear',
            'fromClassrooms',
            'toClassrooms',
            'defaultMap',
            'fromCounts',
            'totalFromStudents',
            'toProjectedCounts',
            'graduateCount',
        ));

    }

    public function store(Request $request, EnrollmentPromotionService $service)
    {
        $this->authorize('manageSchoolData');

        $data = $request->validate([
            'from_year_id' => ['required', 'integer', 'exists:school_years,id'],
            'to_year_id'   => ['required', 'integer', 'exists:school_years,id', 'different:from_year_id'],
            'map'          => ['required', 'array'],
            'map.*'        => ['nullable', 'integer', 'exists:classrooms,id'],
        ]);

        $fromYearId = (int) $data['from_year_id'];
        $toYearId   = (int) $data['to_year_id'];

        // ✅ validasi mapping (grade + 1, kelas 12 harus null) dilakukan di service
        $service->validateMapping($data['map']);

        // ✅ eksekusi promosi dilakukan di service
        $service->promote($fromYearId, $toYearId, $data['map']);

        return redirect()
            ->route('enrollments.promote.index', [
                'from_year_id' => $fromYearId,
                'to_year_id'   => $toYearId,
            ])
            ->with('success', 'Promosi tahun ajaran berhasil diproses. Enrollment TA asal dinonaktifkan; TA tujuan dibuatkan untuk kelas 10/11; kelas 12 diluluskan.');
    }
}
