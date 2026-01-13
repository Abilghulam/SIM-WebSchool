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
        ]);
    }

    public function preview(StudentImportPreviewRequest $request, StudentImportService $service)
    {
        $file = $request->file('file');
        $token = (string) Str::uuid();

        // Simpan ke disk default (sesuai FILESYSTEM_DISK), folder imports/tmp
        $path = $file->storeAs(
            'imports/tmp',
            $token . '.' . $file->getClientOriginalExtension()
        );

        // Path absolut yang benar sesuai disk yang dipakai storeAs()
        $absPath = Storage::path($path);

        if (!is_file($absPath)) {
            return back()->withErrors([
                'file' => 'File gagal disimpan. Silakan upload ulang.',
            ]);
        }

        $options = $request->validatedOptions();

        // Preview via service (akan lempar ValidationException kalau file kosong/invalid)
        $result = $service->preview($absPath, $options);

        // simpan metadata untuk commit (biar ga simpan seluruh row di session)
        session()->put("import.students.$token", [
            'path' => $path,
            'options' => $options,
        ]);

        return view('imports.students-preview', [
            'token' => $token,
            'options' => $options,
            'result' => $result,
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