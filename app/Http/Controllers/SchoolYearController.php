<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\StudentEnrollment;
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

    public function show(SchoolYear $schoolYear)
    {
        $this->authorize('view', $schoolYear);

        // Statistik enrollment per kelas untuk TA ini
        // - total enrollment
        // - total enrollment yang is_active = 1
        $classroomStats = StudentEnrollment::query()
            ->where('school_year_id', $schoolYear->id)
            ->selectRaw('classroom_id,
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_enrollments
            ')
            ->groupBy('classroom_id')
            ->with(['classroom.major'])
            ->orderBy('classroom_id')
            ->get();

        return view('school-years.show', compact('schoolYear', 'classroomStats'));
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

        if ($schoolYear->is_locked) {
            return back()->with('warning', 'Tahun ajaran ini sudah dikunci dan tidak bisa diubah.');
        }

        return view('school-years.edit', compact('schoolYear'));
    }

    public function update(Request $request, SchoolYear $schoolYear)
    {
        $this->authorize('update', $schoolYear);

        if ($schoolYear->is_locked) {
            return back()->with('warning', 'Tahun ajaran ini sudah dikunci dan tidak bisa diubah.');
        }

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

        if ($schoolYear->is_locked) {
            return back()->with('warning', 'Tahun ajaran ini sudah dikunci dan tidak bisa diubah.');
        }

        // kalau yang aktif, minta user set tahun lain aktif dulu
        if ($schoolYear->is_active) {
            return back()->with('success', 'Tidak bisa menghapus tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        $schoolYear->delete();

        return redirect()->route('school-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(SchoolYear $schoolYear)
    {
        // NOTE: kamu pakai route PATCH school-years/{schoolYear}/activate
        // dan di index kamu submit form dari sana.
        $this->authorize('update', $schoolYear);

        if ($schoolYear->is_locked) {
            return back()->with('warning', 'Tahun ajaran ini sudah dikunci dan tidak bisa diaktifkan.');
        }

        DB::transaction(function () use ($schoolYear) {
            // 1) nonaktifkan semua
            SchoolYear::query()->where('is_active', 1)->update(['is_active' => 0]);

            // 2) aktifkan yang dipilih
            $schoolYear->update(['is_active' => 1]);

            // 3) OPTIONAL: matikan enrollment aktif TA lain agar "current active TA" bersih
            // Ini sesuai konsep kamu: saat ganti TA aktif, halaman wali kelas / siswa kelas saya berubah.
            StudentEnrollment::query()
                ->where('school_year_id', '!=', $schoolYear->id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);
        });

        // IMPORTANT: di sini kita tidak auto promote.
        // Promote dilakukan via /enrollments/promote supaya mapping jelas dan aman.
        return redirect()
            ->route('school-years.index')
            ->with('success', "Tahun ajaran {$schoolYear->name} berhasil diaktifkan. Silakan jalankan Promote untuk membuat enrollment TA ini.");
    }
}
