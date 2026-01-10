<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Services\EnrollmentPromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class SchoolYearController extends BaseController
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', SchoolYear::class);

        $q = SchoolYear::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $q->where('name', 'like', "%{$search}%");
        }

        $schoolYears = $q->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // ✅ untuk tombol Promote: butuh minimal ada 1 TA lain sebagai tujuan
        $otherYearsExist = SchoolYear::query()->exists()
            && SchoolYear::query()->where('is_active', 0)->exists();

        return view('school-years.index', compact('schoolYears', 'otherYearsExist'));
    }

    public function create()
    {
        $this->authorize('create', SchoolYear::class);
        return view('school-years.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', SchoolYear::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', Rule::unique('school_years', 'name')->whereNull('deleted_at')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? false);

        DB::transaction(function () use ($data) {
            if ($data['is_active']) {
                SchoolYear::where('is_active', 1)->update(['is_active' => 0]);
            }
            SchoolYear::create($data);
        });

        return redirect()->route('school-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(SchoolYear $schoolYear)
    {
        $this->authorize('update', $schoolYear);
        return view('school-years.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $this->authorize('update', $schoolYear);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', Rule::unique('school_years', 'name')->ignore($schoolYear->id)->whereNull('deleted_at')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? false);

        DB::transaction(function () use ($data, $schoolYear) {
            if ($data['is_active']) {
                SchoolYear::where('id', '!=', $schoolYear->id)->where('is_active', 1)->update(['is_active' => 0]);
            }
            $schoolYear->update($data);
        });

        return redirect()->route('school-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(SchoolYear $schoolYear)
    {
        $this->authorize('delete', $schoolYear);

        // kalau yang aktif, minta user set tahun lain aktif dulu
        if ($schoolYear->is_active) {
            return back()->with('success', 'Tidak bisa menghapus tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        $schoolYear->delete();

        return redirect()->route('school-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(SchoolYear $schoolYear, EnrollmentPromotionService $service)
    {
        $this->authorize('update', $schoolYear);

        DB::transaction(function () use ($schoolYear, $service) {
            $oldActiveId = SchoolYear::query()->where('is_active', 1)->value('id');

            // 1) set aktif baru
            SchoolYear::query()->where('is_active', 1)->update(['is_active' => 0]);
            $schoolYear->update(['is_active' => 1]);

            // 2) matikan semua enrollment TA lama
            if ($oldActiveId) {
                \App\Models\StudentEnrollment::query()
                    ->where('school_year_id', $oldActiveId)
                    ->update(['is_active' => 0]);
            }

            // 3) jalankan promosi: buat enrollment TA baru (otomatis/mapping)
            //    *ini kamu sudah rencanakan: grade+1, kelas 12 => lulus, dsb.
            $service->promote($oldActiveId, $schoolYear->id);
        });

        return redirect()->route('school-years.index')
            ->with('success', "Tahun ajaran {$schoolYear->name} berhasil diaktifkan & promosi dijalankan.");
    }

}
