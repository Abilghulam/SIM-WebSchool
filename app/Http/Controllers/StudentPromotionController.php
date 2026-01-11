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
            return redirect()
                ->route('school-years.index')
                ->with('warning', 'TA asal harus dalam status aktif.');
        }
        if ($fromYear->is_locked) {
            return redirect()
                ->route('school-years.index')
                ->with('warning', 'TA asal sudah dikunci (promote sudah dilakukan). Silakan pilih tahun ajaran lain.');
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

            // fallback: grade_level+1 dan major sama
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

        $totalFromStudents = (int) $fromCounts->sum();

        // --- PROYEKSI KE TA TUJUAN (berdasarkan defaultMap) ---
        $toProjectedCounts = collect();
        $graduateCount = 0;

        foreach ($defaultMap as $fromClassId => $toClassId) {
            $count = (int) ($fromCounts[$fromClassId] ?? 0);

            if (!$toClassId) {
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

        $fromYear = SchoolYear::query()->findOrFail($fromYearId);
        if ($fromYear->is_locked) {
            return back()->withErrors([
                'from_year_id' => 'TA asal sudah dikunci. Promosi tidak bisa dilakukan.',
            ]);
        }

        $service->validateMapping($data['map']);
        $service->promote($fromYearId, $toYearId, $data['map']);

        return redirect()
            ->route('enrollments.promote.index', [
                'from_year_id' => $fromYearId,
                'to_year_id'   => $toYearId,
            ])
            ->with('success', 'Promosi tahun ajaran berhasil diproses. TA asal terkunci otomatis.');
    }
}
