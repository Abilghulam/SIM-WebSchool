<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityUiFormatter
{
    /**
     * Mapping event -> label manusiawi + badge variant.
     */
    public static function eventMeta(?string $event): array
    {
        $event = (string) ($event ?? '');

        $map = [
            'created' => ['label' => 'Membuat data', 'variant' => 'green'],
            'updated' => ['label' => 'Mengubah data', 'variant' => 'blue'],
            'deleted' => ['label' => 'Menghapus data', 'variant' => 'red'],

            'school_year_activated' => ['label' => 'Aktivasi tahun ajaran', 'variant' => 'amber'],
            'bulk_placement_executed' => ['label' => 'Penempatan massal siswa', 'variant' => 'amber'],
            'promotion_executed' => ['label' => 'Promosi/kenaikan kelas', 'variant' => 'amber'],
            'enrollments_promoted' => ['label' => 'Promosi/kenaikan kelas', 'variant' => 'amber'],
            'import_committed' => ['label' => 'Import data siswa', 'variant' => 'amber'],
            'homeroom_assigned' => ['label' => 'Set wali kelas', 'variant' => 'amber'],

            'student_document_uploaded' => ['label' => 'Upload dokumen siswa', 'variant' => 'green'],
            'student_document_deleted' => ['label' => 'Hapus dokumen siswa', 'variant' => 'red'],
            'teacher_document_uploaded' => ['label' => 'Upload dokumen guru', 'variant' => 'green'],
            'teacher_document_deleted' => ['label' => 'Hapus dokumen guru', 'variant' => 'red'],

            // (opsional, kalau nanti kamu tambahkan)
            'teacher_account_created' => ['label' => 'Buat akun login guru', 'variant' => 'green'],
            'teacher_account_toggled' => ['label' => 'Ubah status akun guru', 'variant' => 'amber'],
            'teacher_account_force_change_password' => ['label' => 'Paksa ganti password', 'variant' => 'amber'],
            'teacher_account_password_reset' => ['label' => 'Reset password guru', 'variant' => 'amber'],
        ];

        return $map[$event] ?? [
            'label' => $event !== '' ? Str::of($event)->replace('_', ' ')->headline()->toString() : '-',
            'variant' => 'gray',
        ];
    }

    /**
     * Label subject yang manusiawi (untuk table index + show).
     */
    public static function subjectLabel(Activity $a): ?string
    {
        $subject = $a->subject;
        if (!$subject) return null;

        $type = class_basename((string) $a->subject_type);

        return match ($type) {
            'Student' => trim(($subject->nis ?? '-') . ' - ' . ($subject->full_name ?? '')),
            'Teacher' => trim(($subject->nip ?? '-') . ' - ' . ($subject->full_name ?? '')),
            'SchoolYear' => (string) ($subject->name ?? 'Tahun Ajaran'),
            'Classroom' => (string) ($subject->name ?? 'Kelas'),
            'Major' => trim(($subject->code ?? '') . (($subject->code ?? '') !== '' ? ' - ' : '') . ($subject->name ?? 'Jurusan')),

            // kalau suatu saat activity subject-nya dokumen
            'StudentDocument', 'TeacherDocument' => (string) ($subject->file_name ?? ($subject->title ?? 'Dokumen')),

            default => (string) ($subject->name ?? ($subject->title ?? null)),
        };
    }

    /**
     * URL subject (kalau ada halamannya).
     * Catatan: Classroom/Major kamu adanya edit page, bukan show.
     */
    public static function subjectUrl(Activity $a): ?string
    {
        $subject = $a->subject;
        if (!$subject) return null;

        $type = class_basename((string) $a->subject_type);

        return match ($type) {
            'Student' => route('students.show', $subject),
            'Teacher' => route('teachers.show', $subject),
            'SchoolYear' => route('school-years.show', $subject),
            'Classroom' => route('classrooms.edit', $subject),
            'Major' => route('majors.edit', $subject),
            default => null,
        };
    }

    /**
     * Deskripsi audit manusiawi (UI-only).
     * Menggunakan: causer, subjectLabel, event, properties.
     */
    public static function auditSentence(Activity $a): string
    {
        $event = (string) ($a->event ?? '');
        $causer = $a->causer?->name ?? 'Sistem';
        $subjectType = class_basename((string) $a->subject_type);
        $subjectLabel = self::subjectLabel($a) ?? ($subjectType !== '' ? $subjectType : 'data');
        $props = is_array($a->properties?->toArray()) ? $a->properties->toArray() : [];

        // helper kecil
        $p = fn(string $key, $default = null) => Arr::get($props, $key, $default);

        return match ($event) {
            'created' => "{$causer} membuat {$subjectType} ({$subjectLabel}).",
            'updated' => "{$causer} mengubah {$subjectType} ({$subjectLabel}).",
            'deleted' => "{$causer} menghapus {$subjectType} ({$subjectLabel}).",

            'school_year_activated' => "{$causer} mengaktifkan tahun ajaran {$p('name', $subjectLabel)}.",
            'bulk_placement_executed' => "{$causer} menjalankan penempatan massal siswa (created: {$p('created', 0)}, updated: {$p('updated', 0)}, skipped: {$p('skipped', 0)}).",
            'import_committed' => "{$causer} melakukan import data siswa (mode: {$p('mode', '-')}).",
            'promotion_executed', 'enrollments_promoted' => "{$causer} menjalankan promosi/kenaikan kelas (moved: {$p('moved_students', $p('moved', '-'))}, graduated: {$p('graduated_students', '-')}, skipped: {$p('skipped_students', '-')}).",

            'homeroom_assigned' => "{$causer} menyimpan penugasan wali kelas untuk {$subjectLabel}.",

            'student_document_uploaded' => "{$causer} mengupload dokumen siswa: {$p('file_name', '-')}.",
            'student_document_deleted' => "{$causer} menghapus dokumen siswa: {$p('file_name', '-')}.",
            'teacher_document_uploaded' => "{$causer} mengupload dokumen guru: {$p('file_name', '-')}.",
            'teacher_document_deleted' => "{$causer} menghapus dokumen guru: {$p('file_name', '-')}.",

            // (opsional, kalau kamu tambah lognya)
            'teacher_account_created' => "{$causer} membuat akun login untuk guru {$subjectLabel} (username: {$p('username', '-')}).",
            'teacher_account_toggled' => "{$causer} mengubah status akun guru {$subjectLabel} menjadi " . ($p('new_is_active', false) ? 'Aktif' : 'Nonaktif') . ".",
            'teacher_account_force_change_password' => "{$causer} memaksa guru {$subjectLabel} mengganti password pada login berikutnya.",
            'teacher_account_password_reset' => "{$causer} mereset password akun guru {$subjectLabel} dan mewajibkan ganti password saat login berikutnya.",

            default => ($a->description ?: "{$causer} melakukan aksi {$event} pada {$subjectLabel}."),
        };
    }

    /**
     * Resolver: inject subject model (termasuk SoftDeletes) ke collection Activity.
     * Menghindari N+1 di index.
     */
    public static function hydrateSubjects(iterable $activities): void
    {
        $items = collect($activities);

        $groups = $items
            ->filter(fn($a) => $a instanceof Activity)
            ->filter(fn(Activity $a) => $a->subject_type && $a->subject_id)
            ->groupBy(fn(Activity $a) => (string) $a->subject_type);

        foreach ($groups as $type => $acts) {
            /** @var class-string<Model> $type */
            if (!class_exists($type)) continue;

            $ids = $acts->pluck('subject_id')->filter()->unique()->values();

            $q = $type::query()->whereIn('id', $ids);

            if (in_array(SoftDeletes::class, class_uses_recursive($type), true)) {
                $q->withTrashed();
            }

            $models = $q->get()->keyBy('id');

            foreach ($acts as $a) {
                $model = $models->get($a->subject_id);
                if ($model) {
                    $a->setRelation('subject', $model);
                }
            }
        }
    }
}
