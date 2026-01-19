<?php

namespace App\Services;

use App\Imports\StudentImportHeading;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class StudentImportService
{
    /**
     * Preview: baca file, validasi per-baris, return ringkasan + sample preview + daftar error.
     */
    public function preview(string $absPath, array $options): array
    {
        [$rows, $meta] = $this->readRows($absPath);

        // kalau file kosong / ga ada data
        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'file' => 'File kosong / tidak ada baris data. Silakan isi template minimal 1 baris.',
            ]);
        }

        $errors = [];
        $normalized = [];

        $stats = [
            'total_rows' => count($rows),
            'valid_rows' => 0,
            'invalid_rows' => 0,
        ];

        foreach ($rows as $i => $row) {
            $line = $meta['line_map'][$i] ?? null; // line asli di excel
            $n = $this->normalizeRow($row, $options);
            $rowErrors = $this->validateRow($n, $options);

            if (!empty($rowErrors)) {
                $stats['invalid_rows']++;
                $errors[] = [
                    'line' => $line ?? ($i + 2),
                    'errors' => $rowErrors,
                    'row' => $row,
                ];
                continue;
            }

            $stats['valid_rows']++;
            $normalized[] = $n;
        }

        return [
            'stats' => $stats,
            'preview' => array_slice($normalized, 0, 20),
            'errors' => array_slice($errors, 0, 50),
            'has_more_errors' => count($errors) > 50,
        ];
    }

    /**
     * Commit: insert/update student + optional enrollment.
     */
    public function commit(string $absPath, array $options, User $actor): array
    {
        [$rows] = $this->readRows($absPath);

        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'file' => 'File kosong / tidak ada baris data. Silakan isi template minimal 1 baris.',
            ]);
        }

        $inserted = 0;
        $updated = 0;
        $enrollmentsUpserted = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $rows, $options, $actor,
            &$inserted, &$updated, &$enrollmentsUpserted, &$skipped
        ) {
            foreach ($rows as $row) {
                $n = $this->normalizeRow($row, $options);
                $rowErrors = $this->validateRow($n, $options);

                if (!empty($rowErrors)) {
                    $skipped++;
                    continue;
                }

                // ====== STUDENT ======
                if (($options['strategy'] ?? 'upsert_by_nis') === 'create_only') {
                    if (Student::query()->where('nis', $n['nis'])->exists()) {
                        $skipped++;
                        continue;
                    }

                    $student = new Student();
                    $student->nis = $n['nis'];
                    $student->fill($this->studentFillable($n));
                    $student->created_by = $actor->id;
                    $student->updated_by = $actor->id;
                    $student->save();

                    $inserted++;
                } else {
                    // upsert_by_nis (default)
                    $student = Student::query()->where('nis', $n['nis'])->first();

                    if (!$student) {
                        $student = new Student();
                        $student->nis = $n['nis'];
                        $student->created_by = $actor->id;
                        $inserted++;
                    } else {
                        $updated++;
                    }

                    $student->fill($this->studentFillable($n));
                    $student->updated_by = $actor->id;
                    $student->save();
                }

                // ====== ENROLLMENT (optional) ======
                if (($options['mode'] ?? 'students_only') === 'students_with_enrollment') {
                    $schoolYearId = $n['school_year_id'] ?? ($options['default_school_year_id'] ?? null);
                    $classroomId  = $n['classroom_id'] ?? ($options['default_classroom_id'] ?? null);

                    // Enrollment dibuat kalau school_year ada (classroom boleh null)
                    if ($schoolYearId) {
                        StudentEnrollment::query()->updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'school_year_id' => (int) $schoolYearId,
                            ],
                            [
                                'classroom_id' => $classroomId ? (int) $classroomId : null,
                                'is_active' => array_key_exists('enrollment_is_active', $n) && $n['enrollment_is_active'] !== null
                                    ? (bool) $n['enrollment_is_active']
                                    : (bool) ($options['default_enrollment_is_active'] ?? true),
                                'note' => $n['enrollment_note'] ?? null,
                            ]
                        );

                        $enrollmentsUpserted++;
                    }
                }

            }
        });

        // ✅ Activity log: import commit (ringkasan)
        activity()
            ->useLog('domain')
            ->event('import_committed')
            ->causedBy($actor)
            ->withProperties([
                'feature' => 'students_import',
                'mode' => $options['mode'] ?? 'students_only',
                'strategy' => $options['strategy'] ?? 'upsert_by_nis',
                'default_school_year_id' => $options['default_school_year_id'] ?? null,
                'default_classroom_id' => $options['default_classroom_id'] ?? null,
                'default_enrollment_is_active' => $options['default_enrollment_is_active'] ?? null,

                // hasil
                'inserted' => $inserted,
                'updated' => $updated,
                'enrollments_upserted' => $enrollmentsUpserted,
                'skipped' => $skipped,
                'total_processed' => ($inserted + $updated + $skipped),

                // audit kecil (tanpa simpan isi file)
                'source' => [
                    'path_hash' => sha1($absPath), // aman untuk audit tanpa bocor path asli
                ],
            ])
            ->log('Students import committed');

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'enrollments_upserted' => $enrollmentsUpserted,
            'skipped' => $skipped,
        ];
    }

    // =========================
    // Helpers
    // =========================

    /**
     * Baca sheet pertama dengan heading row.
     * - heading -> key snake_lower
     * - skip baris kosong
     * - return line_map agar line error akurat
     *
     * @return array{0: array<int, array>, 1: array{line_map: array<int,int>}}
     */
    private function readRows(string $absPath): array
    {
        try {
            $sheets = Excel::toArray(new StudentImportHeading(), $absPath);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'file' => 'File tidak bisa dibaca. Pastikan format .xlsx/.csv valid.',
            ]);
        }

        $rows = $sheets[0] ?? [];
        if (!is_array($rows)) $rows = [];

        $out = [];
        $lineMap = [];

        // Dengan WithHeadingRow: rows[0] adalah baris data pertama di excel (baris 2)
        // Jadi line asli = (index + 2)
        foreach ($rows as $i => $row) {
            if (!is_array($row)) continue;

            // normalize keys & values (lebih tahan banting)
            $fixed = [];
            foreach ($row as $k => $v) {
                $key = is_string($k) ? $this->normalizeKey($k) : $k;
                $fixed[$key] = is_string($v) ? trim($v) : $v;
            }

            // skip baris kosong (semua nilai kosong/null/"")
            if ($this->isBlankRow($fixed)) {
                continue;
            }

            $out[] = $fixed;
            $lineMap[] = $i + 2;
        }

        return [$out, ['line_map' => $lineMap]];
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);
        $key = Str::lower($key);

        // ganti pemisah umum jadi underscore
        $key = str_replace([' ', '-', '.', '/'], '_', $key);

        // rapikan underscore berulang
        $key = preg_replace('/_+/', '_', $key) ?? $key;

        return $key;
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $v) {
            if (is_string($v) && trim($v) !== '') return false;
            if (!is_string($v) && $v !== null && $v !== '') return false;
        }
        return true;
    }

    private function normalizeRow(array $row, array $options): array
    {
        // student
        $nis = (string) ($row['nis'] ?? '');
        $fullName = (string) ($row['full_name'] ?? ($row['nama'] ?? ''));

        $gender = $row['gender'] ?? null;
        if (is_string($gender)) {
            $g = strtoupper(trim($gender));
            if (in_array($g, ['L', 'LAKI', 'LAKI-LAKI', 'MALE'], true)) $gender = 'L';
            if (in_array($g, ['P', 'PEREMPUAN', 'FEMALE'], true)) $gender = 'P';
        }

        $status = $row['status'] ?? 'aktif';
        if (is_string($status)) $status = strtolower(trim($status));

        // enrollment resolve
        $schoolYearId = $row['school_year_id'] ?? null;
        if (!$schoolYearId && !empty($row['school_year_name'])) {
            $schoolYearId = SchoolYear::query()
                ->where('name', trim((string) $row['school_year_name']))
                ->value('id');
        }

        $classroomId = $row['classroom_id'] ?? null;
        if (!$classroomId && !empty($row['classroom_name'])) {
            $classroomId = Classroom::query()
                ->where('name', trim((string) $row['classroom_name']))
                ->value('id');
        }

        $enrollmentIsActive = $row['is_active'] ?? ($row['enrollment_is_active'] ?? null);
        if (is_string($enrollmentIsActive)) {
            $vv = strtolower(trim($enrollmentIsActive));
            $enrollmentIsActive = in_array($vv, ['1', 'true', 'ya', 'yes', 'aktif'], true);
        }

        return [
            'nis' => trim($nis),
            'full_name' => trim($fullName),
            'gender' => $gender ?: null,
            'birth_place' => $row['birth_place'] ?? null,
            'birth_date' => $this->parseDate($row['birth_date'] ?? null),
            'religion' => $row['religion'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? null,
            'father_name' => $row['father_name'] ?? null,
            'mother_name' => $row['mother_name'] ?? null,
            'guardian_name' => $row['guardian_name'] ?? null,
            'parent_phone' => $row['parent_phone'] ?? null,
            'status' => $status ?: 'aktif',
            'entry_year' => $row['entry_year'] ?? null,

            'school_year_id' => $schoolYearId ? (int) $schoolYearId : null,
            'classroom_id' => $classroomId ? (int) $classroomId : null,
            'enrollment_is_active' => is_bool($enrollmentIsActive) ? $enrollmentIsActive : null,
            'enrollment_note' => $row['note'] ?? ($row['enrollment_note'] ?? null),
        ];
    }

    private function validateRow(array $n, array $options): array
    {
        $err = [];

        if (empty($n['nis'])) $err[] = 'NIS wajib diisi.';
        if (empty($n['full_name'])) $err[] = 'Nama (full_name) wajib diisi.';

        if (!empty($n['gender']) && !in_array($n['gender'], ['L', 'P'], true)) {
            $err[] = 'Gender harus L atau P (boleh kosong).';
        }

        $allowedStatus = ['aktif', 'lulus', 'pindah', 'nonaktif'];
        if (!empty($n['status']) && !in_array($n['status'], $allowedStatus, true)) {
            $err[] = 'Status harus salah satu: aktif/lulus/pindah/nonaktif.';
        }

        // mode enrollment:
        // - school_year WAJIB (dari kolom atau default)
        // - classroom BOLEH kosong (untuk kasus awal masuk)
        if (($options['mode'] ?? 'students_only') === 'students_with_enrollment') {
            $sy = $n['school_year_id'] ?? ($options['default_school_year_id'] ?? null);

            if (!$sy) {
                $err[] = 'Enrollment butuh school_year (kolom school_year_id atau default_school_year_id).';
            }
        }

        return $err;
    }

    private function studentFillable(array $n): array
    {
        return [
            'full_name' => $n['full_name'],
            'gender' => $n['gender'],
            'birth_place' => $n['birth_place'],
            'birth_date' => $n['birth_date'],
            'religion' => $n['religion'],
            'phone' => $n['phone'],
            'email' => $n['email'],
            'address' => $n['address'],
            'father_name' => $n['father_name'],
            'mother_name' => $n['mother_name'],
            'guardian_name' => $n['guardian_name'],
            'parent_phone' => $n['parent_phone'],
            'status' => $n['status'],
            'entry_year' => $n['entry_year'],
        ];
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel date numeric
        if (is_numeric($value)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $v = trim((string) $value);

        if ($v === '') return null;

        // Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;

        // d/m/Y or d-m-Y
        if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $v)) {
            $v = str_replace('-', '/', $v);
            [$d, $m, $y] = explode('/', $v);
            $d = str_pad($d, 2, '0', STR_PAD_LEFT);
            $m = str_pad($m, 2, '0', STR_PAD_LEFT);
            return "$y-$m-$d";
        }

        return null;
    }
}
