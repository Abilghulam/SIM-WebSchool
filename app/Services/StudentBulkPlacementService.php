<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentBulkPlacementService
{
    public function candidatesQuery(array $filters)
    {
        $q = Student::query()
            ->where('status', 'aktif')
            ->whereDoesntHave('enrollments', function ($e) {
                $e->withTrashed(); // penting: “belum pernah ada enrollment”
            });

        if (!empty($filters['search'])) {
            $s = trim((string) $filters['search']);
            $q->where(function ($w) use ($s) {
                $w->where('full_name', 'like', "%{$s}%")
                  ->orWhere('nis', 'like', "%{$s}%");
            });
        }

        if (!empty($filters['entry_year'])) {
            $q->where('entry_year', (int) $filters['entry_year']);
        }

        return $q->orderBy('full_name');
    }

    /**
     * @return array{processed:int, created:int, updated:int, skipped:int}
     */
    public function place(array $filters, string $applyMode, ?array $studentIds, ?int $classroomId, ?string $note): array
    {
        $activeYearId = SchoolYear::activeId();

        if (!$activeYearId) {
            throw ValidationException::withMessages([
                'school_year_id' => 'Tidak ada Tahun Ajaran aktif. Aktifkan Tahun Ajaran terlebih dahulu.',
            ]);
        }

        $q = $this->candidatesQuery($filters);

        if ($applyMode === 'selected') {
            $ids = collect($studentIds ?? [])->map(fn ($x) => (int) $x)->values()->all();

            if (count($ids) === 0) {
                throw ValidationException::withMessages([
                    'student_ids' => 'Pilih minimal 1 siswa untuk diproses.',
                ]);
            }

            $q->whereIn('id', $ids);
        }

        $summary = ['processed' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($q, $activeYearId, $classroomId, $note, &$summary) {
            // chunk biar aman untuk data banyak
            $q->chunkById(300, function ($students) use ($activeYearId, $classroomId, $note, &$summary) {
                foreach ($students as $student) {
                    $summary['processed']++;

                    // guard ekstra: pastikan masih eligible (race condition)
                    $hasAnyEnrollment = $student->enrollments()->withTrashed()->exists();
                    if ($hasAnyEnrollment || $student->status !== 'aktif') {
                        $summary['skipped']++;
                        continue;
                    }

                    $enrollment = StudentEnrollment::query()->updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'school_year_id' => $activeYearId,
                        ],
                        [
                            'classroom_id' => $classroomId, // nullable OK
                            'is_active' => true,
                            'note' => $note,
                        ]
                    );

                    // updateOrCreate tidak kasih flag created, jadi kita cek via wasRecentlyCreated
                    if ($enrollment->wasRecentlyCreated) $summary['created']++;
                    else $summary['updated']++;
                }
            });
        });

        return $summary;
    }
}
