<?php

namespace App\Http\Controllers;

use App\Exports\StudentImportTemplateExport;
use App\Http\Requests\StudentImportCommitRequest;
use App\Http\Requests\StudentImportPreviewRequest;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    public function create(Request $request)
    {
        return view('imports.students', [
            'schoolYears' => SchoolYear::query()
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->get(),
            'classrooms'  => Classroom::query()
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(),

            // ✅ aman: null kalau belum pernah preview
            'result' => session('import.students.last_result'),
            'last_options' => session('import.students.last_options'),
        ]);
    }

    public function preview(StudentImportPreviewRequest $request, StudentImportService $service)
    {
        $file = $request->file('file');
        $token = (string) Str::uuid();

        $path = $file->storeAs(
            'imports/tmp',
            $token . '.' . $file->getClientOriginalExtension()
        );

        $absPath = Storage::path($path);

        if (!is_file($absPath)) {
            return back()->withErrors([
                'file' => 'File gagal disimpan. Silakan upload ulang.',
            ]);
        }

        $options = $request->validatedOptions();
        $result  = $service->preview($absPath, $options);

        session()->put("import.students.$token", [
            'path' => $path,
            'options' => $options,
        ]);

        // ✅ simpan ringkasan untuk ditampilkan di halaman upload
        session()->put('import.students.last_result', [
            'stats' => $result['stats'] ?? [],
            'has_more_errors' => $result['has_more_errors'] ?? false,
            'errors_count_shown' => is_array($result['errors'] ?? null) ? count($result['errors']) : 0,
        ]);
        session()->put('import.students.last_options', $options);

        // ==========================
        // BUILD: mapping label TA & kelas (di controller, bukan blade)
        // ==========================
        $preview = is_array($result['preview'] ?? null) ? $result['preview'] : [];

        $defaultSyId = $options['default_school_year_id'] ?? null;
        $defaultClId = $options['default_classroom_id'] ?? null;

        $schoolYearIds = collect($preview)
            ->pluck('school_year_id')
            ->filter()
            ->push($defaultSyId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $classroomIds = collect($preview)
            ->pluck('classroom_id')
            ->filter()
            ->push($defaultClId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $schoolYearMap = [];
        if (!empty($schoolYearIds)) {
            $schoolYearMap = SchoolYear::query()
                ->whereIn('id', $schoolYearIds)
                ->get(['id', 'name', 'is_active'])
                ->mapWithKeys(function ($sy) {
                    return [(string) $sy->id => $sy->name . ($sy->is_active ? ' (Aktif)' : '')];
                })
                ->all();
        }

        $classroomMap = [];
        if (!empty($classroomIds)) {
            $classroomMap = Classroom::query()
                ->whereIn('id', $classroomIds)
                ->get(['id', 'name'])
                ->mapWithKeys(fn ($c) => [(string) $c->id => $c->name])
                ->all();
        }

        // Helper aman (controller side)
        $syText = function ($id) use ($schoolYearMap) {
            if (!$id) return '-';
            return $schoolYearMap[(string) $id] ?? ('#' . $id);
        };

        $clText = function ($id) use ($classroomMap) {
            if (!$id) return '-';
            return $classroomMap[(string) $id] ?? ('#' . $id);
        };

        // ==========================
        // BUILD: rows preview yg sudah punya text (TA & Kelas)
        // ==========================
        $previewRows = collect($preview)->map(function ($r) use ($defaultSyId, $defaultClId, $syText, $clText) {
            $syId = $r['school_year_id'] ?? $defaultSyId;
            $clId = $r['classroom_id'] ?? $defaultClId;

            $r['school_year_text'] = $syText($syId);
            $r['classroom_text']   = $clText($clId);

            return $r;
        })->values()->all();

        // ==========================
        // BUILD: pengaturan import (badge / label / default text)
        // ==========================
        $mode = (string) ($options['mode'] ?? 'students_with_enrollment');
        $strategy = (string) ($options['strategy'] ?? 'upsert_by_nis');

        $modeLabel = $mode === 'students_only'
            ? 'Hanya data siswa'
            : 'Data siswa & Penempatan';

        $modePillClass = $mode === 'students_only'
            ? 'border-gray-200 bg-gray-50 text-gray-700'
            : 'border-indigo-200 bg-indigo-50 text-indigo-700';

        $strategyLabel = $strategy === 'create_only'
            ? 'Tambah data baru'
            : 'Perbarui data';

        $strategyPillClass = $strategy === 'create_only'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-amber-200 bg-amber-50 text-amber-700';

        $defaultSyText = $defaultSyId ? $syText($defaultSyId) : '-';
        $defaultClText = $defaultClId ? $clText($defaultClId) : '-';

        $tipsText = $mode === 'students_only'
            ? 'Mode ini hanya menyimpan biodata siswa, penempatan tidak akan diproses'
            : 'Mode ini menyimpan penempatanp, pastikan data Tahun Ajaran dan Kelas sudah benar';

        $importSettings = [
            'mode_label' => $modeLabel,
            'mode_pill' => $modePillClass,
            'strategy_label' => $strategyLabel,
            'strategy_pill' => $strategyPillClass,
            'default_school_year_text' => $defaultSyText,
            'default_classroom_text' => $defaultClText,
            'tips' => $tipsText,
        ];

        // ==========================
        // BUILD: errors yang lebih informatif (Baris X — ... )
        // ==========================
        $friendlyError = function (string $msg): string {
            $m = mb_strtolower($msg);

            // NIS
            if (str_contains($m, 'nis') && (str_contains($m, 'wajib') || str_contains($m, 'required'))) {
                return 'NIS siswa wajib diisi.';
            }

            // Nama / full_name
            if (
                (str_contains($m, 'nama') || str_contains($m, 'full_name') || str_contains($m, 'full name')) &&
                (str_contains($m, 'wajib') || str_contains($m, 'required'))
            ) {
                return 'Nama siswa wajib diisi.';
            }

            // Tahun ajaran
            if (
                (str_contains($m, 'tahun') || str_contains($m, 'school year')) &&
                (str_contains($m, 'wajib') || str_contains($m, 'required'))
            ) {
                return 'Tahun ajaran wajib diisi (di file atau pakai default).';
            }

            return $msg;
        };

        $rawErrors = is_array($result['errors'] ?? null) ? $result['errors'] : [];

        $errorRows = collect($rawErrors)->map(function ($e) use ($friendlyError) {
            $line = (int) ($e['line'] ?? 0);
            $messages = is_array($e['errors'] ?? null) ? $e['errors'] : [];

            $friendly = collect($messages)
                ->map(fn ($m) => $friendlyError((string) $m))
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'line' => $line,
                'messages' => $friendly,
            ];
        })->values()->all();

        return view('imports.students-preview', [
            'token' => $token,
            'options' => $options,
            'result' => $result,

            // ✅ tambahan aman untuk blade
            'importSettings' => $importSettings,
            'previewRows' => $previewRows,
            'errorRows' => $errorRows,
        ]);
    }

    public function commit(StudentImportCommitRequest $request, StudentImportService $service)
    {
        $token = $request->validated('token');
        $payload = session()->get("import.students.$token");

        abort_if(!$payload, 419, 'Token import tidak ditemukan / expired.');

        $path = $payload['path'] ?? null;
        $options = $payload['options'] ?? [];

        abort_if(!$path, 419, 'File import tidak ditemukan / expired.');

        if (!Storage::exists($path)) {
            // Session ada tapi file tmp sudah hilang/dibersihkan
            abort(419, 'File import tidak ditemukan / sudah dibersihkan. Silakan upload ulang.');
        }

        $absPath = Storage::path($path);

        $summary = $service->commit($absPath, $options, $request->user());

        // cleanup (session + file tmp)
        session()->forget("import.students.$token");
        Storage::delete($path);

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                "Import selesai. Inserted: {$summary['inserted']}, Updated: {$summary['updated']}, Enrollment Upsert: {$summary['enrollments_upserted']}, Skipped: {$summary['skipped']}"
            );
    }

    public function template(Request $request)
    {
        $filename = 'template-import-siswa.xlsx';
        return Excel::download(new StudentImportTemplateExport(), $filename);
    }
}