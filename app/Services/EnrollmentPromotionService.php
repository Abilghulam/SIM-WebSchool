<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\SchoolYear;
use App\Models\EnrollmentPromotion;
use App\Models\EnrollmentPromotionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class EnrollmentPromotionService
{
    /**
     * Validasi mapping:
     * - kelas 12 => tujuan wajib null
     * - selain 12 => tujuan wajib ada dan grade_level = asal + 1
     *
     * @param array<int, int|null> $map [from_classroom_id => to_classroom_id|null]
     */
    public function validateMapping(array $map): void
    {
        if (empty($map)) {
            throw ValidationException::withMessages([
                'map' => 'Mapping kelas belum tersedia. Pilih TA tujuan lalu tampilkan mapping.',
            ]);
        }

        $fromClassrooms = Classroom::query()
            ->whereIn('id', array_keys($map))
            ->get()
            ->keyBy('id');

        $toIds = collect($map)->filter()->values()->all();

        $toClassrooms = Classroom::query()
            ->whereIn('id', $toIds)
            ->get()
            ->keyBy('id');

        foreach ($map as $fromId => $toId) {
            $from = $fromClassrooms->get((int) $fromId);

            if (!$from) {
                throw ValidationException::withMessages([
                    'map' => 'Kelas asal tidak valid.',
                ]);
            }

            // kelas 12 => tujuan wajib null
            if ((int) $from->grade_level >= 12) {
                if (!is_null($toId)) {
                    throw ValidationException::withMessages([
                        "map.$fromId" => "Kelas {$from->name} (kelas 12) harus kosong (otomatis lulus).",
                    ]);
                }
                continue;
            }

            // selain 12 => tujuan wajib ada
            if (!$toId) {
                throw ValidationException::withMessages([
                    "map.$fromId" => "Kelas tujuan untuk {$from->name} wajib dipilih.",
                ]);
            }

            $to = $toClassrooms->get((int) $toId);
            if (!$to) {
                throw ValidationException::withMessages([
                    "map.$fromId" => "Kelas tujuan untuk {$from->name} tidak valid.",
                ]);
            }

            if ((int) $to->grade_level !== ((int) $from->grade_level + 1)) {
                throw ValidationException::withMessages([
                    "map.$fromId" => "Kelas tujuan {$from->name} harus naik 1 tingkat (grade + 1).",
                ]);
            }
        }
    }

    /**
     * Jalankan promosi:
     * - nonaktifkan enrollment TA asal per kelas asal
     * - buat enrollment TA tujuan (kelas 10/11)
     * - kelas 12 => status siswa jadi lulus
     * - setelah sukses => lock TA asal
     * - ✅ buat log promote (header + items)
     *
     * @param array<int, int|null> $map [from_classroom_id => to_classroom_id|null]
     */
    public function promote(int $fromYearId, int $toYearId, array $map = [], ?int $executedBy = null): void
    {
        if (empty($map)) {
            throw ValidationException::withMessages([
                'map' => 'Tidak ada mapping yang dikirim. Promosi dibatalkan.',
            ]);
        }

        $fromClassrooms = Classroom::query()
            ->whereIn('id', array_keys($map))
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($fromYearId, $toYearId, $map, $executedBy, $fromClassrooms) {
            // ✅ row lock TA asal
            $fromYear = SchoolYear::query()->whereKey($fromYearId)->lockForUpdate()->first();
            if (!$fromYear) {
                throw ValidationException::withMessages(['from_year_id' => 'TA asal tidak ditemukan.']);
            }
            if ($fromYear->is_locked) {
                throw ValidationException::withMessages(['from_year_id' => 'TA asal sudah dikunci.']);
            }

            $promotion = EnrollmentPromotion::query()->create([
                'from_school_year_id' => $fromYearId,
                'to_school_year_id' => $toYearId,
                'executed_by' => $executedBy,
                'executed_at' => now(),
                'mapping_json' => $map,
                'total_students' => 0,
                'moved_students' => 0,
                'graduated_students' => 0,
                'skipped_students' => 0,
                'status' => 'running',
                'error_message' => null,
            ]);

            $totals = [
                'total_students' => 0,
                'moved_students' => 0,
                'graduated_students' => 0,
                'skipped_students' => 0,
            ];

            $processedAny = false;

            foreach ($map as $fromClassroomId => $toClassroomId) {
                $fromClassroomId = (int) $fromClassroomId;
                $toClassroomId = $toClassroomId ? (int) $toClassroomId : null;

                $fromClassroom = $fromClassrooms->get($fromClassroomId);
                if (!$fromClassroom) {
                    continue;
                }

                $enrollments = StudentEnrollment::query()
                    ->where('school_year_id', $fromYearId)
                    ->where('classroom_id', $fromClassroomId)
                    ->where('is_active', 1)
                    ->get();

                $activeCount = $enrollments->count();

                $moved = 0;
                $graduated = 0;
                $skipped = 0;

                if ($activeCount > 0) {
                    $processedAny = true;

                    foreach ($enrollments as $enrollment) {
                        $studentStatus = Student::query()
                            ->whereKey($enrollment->student_id)
                            ->value('status');

                        // ✅ tutup enrollment TA asal (sesuai pilihanmu untuk pindah/nonaktif)
                        $enrollment->update(['is_active' => 0]);

                        // kelas 12 => lulus
                        if ((int) $fromClassroom->grade_level >= 12) {
                            Student::query()
                                ->whereKey($enrollment->student_id)
                                ->update(['status' => 'lulus']);

                            $graduated++;
                            continue;
                        }

                        // selain aktif => tidak dipindah, hanya ditutup
                        if ($studentStatus !== 'aktif') {
                            $skipped++;
                            continue;
                        }

                        // ✅ buat/update enrollment TA tujuan (idempotent)
                        StudentEnrollment::query()->updateOrCreate(
                            [
                                'student_id' => $enrollment->student_id,
                                'school_year_id' => $toYearId,
                            ],
                            [
                                'classroom_id' => $toClassroomId, // (wajib ada menurut validateMapping)
                                'is_active' => 1,
                                'note' => null,
                            ]
                        );

                        $moved++;
                    }
                }

                $totals['total_students'] += $activeCount;
                $totals['moved_students'] += $moved;
                $totals['graduated_students'] += $graduated;
                $totals['skipped_students'] += $skipped;

                EnrollmentPromotionItem::query()->create([
                    'enrollment_promotion_id' => $promotion->id,
                    'from_classroom_id' => $fromClassroomId,
                    'to_classroom_id' => $toClassroomId,
                    'from_grade_level' => (int) $fromClassroom->grade_level,
                    'to_grade_level' => $toClassroomId ? ((int) $fromClassroom->grade_level + 1) : null,
                    'active_enrollments' => $activeCount,
                    'moved_students' => $moved,
                    'graduated_students' => $graduated,
                    'skipped_students' => $skipped,
                ]);
            }

            if (!$processedAny) {
                $promotion->update([
                    'status' => 'failed',
                    'error_message' => 'Tidak ada enrollment aktif yang dapat dipromosikan dari TA asal.',
                ]);

                throw ValidationException::withMessages([
                    'map' => 'Tidak ada enrollment aktif yang dapat dipromosikan dari TA asal.',
                ]);
            }

            // ✅ lock TA asal setelah sukses
            $fromYear->update(['is_locked' => 1]);

            // ✅ finalize log
            $promotion->update([
                'total_students' => $totals['total_students'],
                'moved_students' => $totals['moved_students'],
                'graduated_students' => $totals['graduated_students'],
                'skipped_students' => $totals['skipped_students'],
                'status' => 'success',
                'error_message' => null,
            ]);

            // ✅ Activity log: promotion executed (ringkasan)
            activity()
                ->useLog('domain')
                ->event('promotion_executed')
                ->causedBy($executedBy ? \App\Models\User::query()->find($executedBy) : null)
                ->performedOn($promotion)
                ->withProperties([
                    'feature' => 'enrollment_promotion',
                    'from_school_year_id' => $fromYearId,
                    'to_school_year_id' => $toYearId,
                    'mapping_classrooms_count' => count($map),

                    // hasil total (sinkron dengan kolom promotion)
                    'total_students' => $totals['total_students'],
                    'moved_students' => $totals['moved_students'],
                    'graduated_students' => $totals['graduated_students'],
                    'skipped_students' => $totals['skipped_students'],

                    // efek penting
                    'from_year_locked' => true,
                ])
                ->log('Enrollment promotion executed');
        });
    }

}
