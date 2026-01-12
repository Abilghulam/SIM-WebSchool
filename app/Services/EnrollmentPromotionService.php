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

        // load classrooms sekali
        $fromClassrooms = Classroom::query()
            ->whereIn('id', array_keys($map))
            ->get()
            ->keyBy('id');

        // tempat nyimpen hasil untuk log
        $itemsPayload = [];
        $totals = [
            'total_students' => 0,
            'moved_students' => 0,
            'graduated_students' => 0,
            'skipped_students' => 0,
        ];

        try {
            DB::transaction(function () use ($fromYearId, $toYearId, $map, $fromClassrooms, &$itemsPayload, &$totals) {

                // safety: kalau TA asal sudah locked, jangan lanjut
                $fromYearLocked = SchoolYear::query()
                    ->whereKey($fromYearId)
                    ->value('is_locked');

                if ($fromYearLocked) {
                    throw ValidationException::withMessages([
                        'from_year_id' => 'TA asal sudah dikunci. Promosi tidak bisa dijalankan.',
                    ]);
                }

                $processedAny = false;

                foreach ($map as $fromClassroomId => $toClassroomId) {
                    $fromClassroomId = (int) $fromClassroomId;
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
                    if ($activeCount === 0) {
                        // tetap catat item-nya biar audit jelas (opsional)
                        $itemsPayload[] = [
                            'from_classroom_id' => $fromClassroomId,
                            'to_classroom_id' => $toClassroomId ? (int)$toClassroomId : null,
                            'from_grade_level' => (int) $fromClassroom->grade_level,
                            'to_grade_level' => $toClassroomId ? ((int)$fromClassroom->grade_level + 1) : null,
                            'active_enrollments' => 0,
                            'moved_students' => 0,
                            'graduated_students' => 0,
                            'skipped_students' => 0,
                        ];
                        continue;
                    }

                    $processedAny = true;

                    $moved = 0;
                    $graduated = 0;
                    $skipped = 0;

                    foreach ($enrollments as $enrollment) {
                        // nonaktifkan enrollment lama
                        $enrollment->update(['is_active' => 0]);

                        // kelas 12 => lulus, stop
                        if ((int) $fromClassroom->grade_level >= 12) {
                            Student::query()
                                ->where('id', $enrollment->student_id)
                                ->update(['status' => 'lulus']);

                            $graduated++;
                            continue;
                        }

                        // siswa status bukan aktif sebaiknya tidak dipromosikan
                        $studentStatus = Student::query()->where('id', $enrollment->student_id)->value('status');
                        if ($studentStatus !== 'aktif') {
                            $skipped++;
                            continue;
                        }

                        // buat enrollment baru di TA tujuan
                        StudentEnrollment::query()->updateOrCreate(
                            [
                                'student_id' => $enrollment->student_id,
                                'school_year_id' => $toYearId,
                            ],
                            [
                                'classroom_id' => (int) $toClassroomId,
                                'is_active' => 1,
                                'note' => null,
                            ]
                        );

                        $moved++;
                    }

                    // akumulasi totals
                    $totals['total_students'] += $activeCount;
                    $totals['moved_students'] += $moved;
                    $totals['graduated_students'] += $graduated;
                    $totals['skipped_students'] += $skipped;

                    // item log
                    $itemsPayload[] = [
                        'from_classroom_id' => $fromClassroomId,
                        'to_classroom_id' => $toClassroomId ? (int)$toClassroomId : null,
                        'from_grade_level' => (int) $fromClassroom->grade_level,
                        'to_grade_level' => $toClassroomId ? ((int)$fromClassroom->grade_level + 1) : null,
                        'active_enrollments' => $activeCount,
                        'moved_students' => $moved,
                        'graduated_students' => $graduated,
                        'skipped_students' => $skipped,
                    ];
                }

                if (!$processedAny) {
                    throw ValidationException::withMessages([
                        'map' => 'Tidak ada enrollment aktif yang dapat dipromosikan dari TA asal.',
                    ]);
                }

                // ✅ lock TA asal hanya setelah promosi benar-benar berjalan
                SchoolYear::query()
                    ->whereKey($fromYearId)
                    ->update(['is_locked' => 1]);
            });

            // ✅ setelah transaksi sukses, tulis audit log (header + items)
            DB::transaction(function () use ($fromYearId, $toYearId, $map, $executedBy, $itemsPayload, $totals) {
                $promotion = EnrollmentPromotion::query()->create([
                    'from_school_year_id' => $fromYearId,
                    'to_school_year_id' => $toYearId,
                    'executed_by' => $executedBy,
                    'executed_at' => now(),
                    'mapping_json' => $map,
                    'total_students' => (int) $totals['total_students'],
                    'moved_students' => (int) $totals['moved_students'],
                    'graduated_students' => (int) $totals['graduated_students'],
                    'skipped_students' => (int) $totals['skipped_students'],
                    'status' => 'success',
                    'error_message' => null,
                ]);

                foreach ($itemsPayload as $row) {
                    $row['enrollment_promotion_id'] = $promotion->id;
                    EnrollmentPromotionItem::query()->create($row);
                }
            });

        } catch (Throwable $e) {
            // ✅ log failure (di luar transaksi utama)
            try {
                EnrollmentPromotion::query()->create([
                    'from_school_year_id' => $fromYearId,
                    'to_school_year_id' => $toYearId,
                    'executed_by' => $executedBy,
                    'executed_at' => now(),
                    'mapping_json' => $map,
                    'total_students' => (int) ($totals['total_students'] ?? 0),
                    'moved_students' => (int) ($totals['moved_students'] ?? 0),
                    'graduated_students' => (int) ($totals['graduated_students'] ?? 0),
                    'skipped_students' => (int) ($totals['skipped_students'] ?? 0),
                    'status' => 'failed',
                    'error_message' => mb_substr($e->getMessage(), 0, 2000),
                ]);
            } catch (Throwable $ignored) {
                // kalau logging gagal, jangan nutupin error utamanya
            }

            throw $e;
        }
    }
}
