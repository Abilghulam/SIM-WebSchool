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
            return back()->with('success', 'Belum ada Tahun Ajaran aktif. Aktifkan dulu di Master Tahun Ajaran.');
        }

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        DB::transaction(function () use ($data, $activeSchoolYearId) {

            // 1) Ambil wali kelas lama untuk kelas ini (kalau ada)
            $existing = HomeroomAssignment::query()
                ->where('school_year_id', $activeSchoolYearId)
                ->where('classroom_id', $data['classroom_id'])
                ->first();

            $oldTeacherId = $existing?->teacher_id;

            // 2) Upsert assignment (seperti kode kamu sekarang)
            HomeroomAssignment::query()->updateOrCreate(
                [
                    'school_year_id' => $activeSchoolYearId,
                    'classroom_id' => $data['classroom_id'],
                ],
                [
                    'teacher_id' => $data['teacher_id'],
                ]
            );

            // 3) Promote teacher baru -> wali_kelas (hanya kalau user-nya role guru)
            User::query()
                ->where('teacher_id', $data['teacher_id'])
                ->where('role_label', 'guru')
                ->update(['role_label' => 'wali_kelas']);

            // 4) Kalau teacher lama beda dengan teacher baru, cek apakah teacher lama masih jadi wali kelas (di TA aktif).
            //    Jika tidak ada assignment lain, turunkan kembali ke guru.
            if ($oldTeacherId && (int)$oldTeacherId !== (int)$data['teacher_id']) {
                $stillHomeroom = HomeroomAssignment::query()
                    ->where('school_year_id', $activeSchoolYearId)
                    ->where('teacher_id', $oldTeacherId)
                    ->exists();

                if (!$stillHomeroom) {
                    User::query()
                        ->where('teacher_id', $oldTeacherId)
                        ->where('role_label', 'wali_kelas')
                        ->update(['role_label' => 'guru']);
                }
            }
        });

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Wali kelas berhasil disimpan.');
    }

    public function destroy(HomeroomAssignment $homeroomAssignment)
    {
        $this->authorize('delete', $homeroomAssignment);

        $activeSchoolYearId = SchoolYear::activeId();

        DB::transaction(function () use ($homeroomAssignment, $activeSchoolYearId) {
            $teacherId = $homeroomAssignment->teacher_id;

            $homeroomAssignment->delete();

            // kalau teacher ini sudah tidak punya assignment lain di TA aktif -> turunkan jadi guru
            if ($activeSchoolYearId) {
                $stillHomeroom = HomeroomAssignment::query()
                    ->where('school_year_id', $activeSchoolYearId)
                    ->where('teacher_id', $teacherId)
                    ->exists();

                if (!$stillHomeroom) {
                    User::query()
                        ->where('teacher_id', $teacherId)
                        ->where('role_label', 'wali_kelas')
                        ->update(['role_label' => 'guru']);
                }
            }
        });

        return redirect()->route('homeroom-assignments.index')
            ->with('success', 'Penugasan wali kelas berhasil dihapus.');
    }
}
