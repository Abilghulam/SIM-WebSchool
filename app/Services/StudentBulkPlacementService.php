<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentBulkPlacementService
{
    /**
     * Penempatan massal berdasarkan ID yang dipilih (match dengan Controller kamu saat ini).
     *
     * @param array<int> $studentIds
     * @return array{processed:int, created:int, updated:int, skipped:int, school_year_id:int}
     */
    public function placeSelected(array $studentIds, ?int $classroomId, ?string $note, User $actor): array
    {
        $activeYear = SchoolYear::query()->where('is_active', true)->first();

        if (!$activeYear) {
            throw ValidationException::withMessages([
                'school_year_id' => 'Tidak ada Tahun Ajaran aktif. Aktifkan dulu Tahun Ajaran.',
            ]);
        }

        if ($activeYear->is_locked) {
            throw ValidationException::withMessages([
                'school_year_id' => 'Tahun Ajaran aktif sedang terkunci. Penempatan massal tidak dapat dilakukan.',
            ]);
        }

        $activeYearId = (int) $activeYear->id;

        $ids = collect($studentIds)->map(fn ($x) => (int) $x)->values()->all();
        if (count($ids) === 0) {
            throw ValidationException::withMessages([
                'student_ids' => 'Pilih minimal 1 siswa untuk diproses.',
            ]);
        }

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use (
            $ids, $activeYearId, $classroomId, $note,
            &$processed, &$created, &$updated, &$skipped
        ) {
            $students = Student::query()
                ->whereIn('id', $ids)
                ->where('status', 'aktif')
                ->get(['id', 'status']);

            $foundIds = $students->pluck('id')->map(fn ($x) => (int) $x)->all();
            $skipped += max(0, count($ids) - count($foundIds));

            foreach ($students as $student) {
                $processed++;

                $enr = StudentEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('school_year_id', $activeYearId)
                    ->where('is_active', true)
                    ->first();

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

                if ($enr->classroom_id === null) {
                    $enr->update([
                        'classroom_id' => $classroomId, // null tetap boleh
                        'note' => $note,
                    ]);
                    $updated++;
                    continue;
                }

                $skipped++;
            }
        });

        // ✅ Activity log: bulk placement (di service)
        activity()
            ->useLog('domain')
            ->event('bulk_placement_executed')
            ->causedBy($actor)
            ->withProperties([
                'feature' => 'students_bulk_placement',
                'school_year_id' => $activeYearId,
                'classroom_id' => $classroomId,
                'note_filled' => !empty($note),

                'processed' => $processed,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,

                'selected_count' => count($ids),
            ])
            ->log('Students bulk placement executed');

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'school_year_id' => $activeYearId,
        ];
    }
}
