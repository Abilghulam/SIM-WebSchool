<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\HomeroomAssignment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Cache;

class NotificationCenter
{
    /**
     * Jalankan sync notif “system alerts” untuk Admin/Operator.
     * Dedup pakai data->key di notifications table.
     */
    public function syncAdminOperator(User $user): void
    {
        if (!($user->isAdmin() || $user->isOperator())) {
            return;
        }

        $activeYearId = SchoolYear::activeId();

        $alerts = [
            'missingActiveSchoolYear' => $activeYearId ? false : true,

            'studentsWithoutActiveEnrollment' => Student::query()
                ->where('status', 'aktif')
                ->whereDoesntHave('enrollments', function ($q) use ($activeYearId) {
                    $q->where('is_active', true);
                    if ($activeYearId) $q->where('school_year_id', $activeYearId);
                })
                ->count(),

            'teachersWithoutAccount' => Teacher::query()
                ->where('is_active', true)
                ->doesntHave('user')
                ->count(),

            'mustChangePasswordCount' => User::query()
                ->where('role_label', 'guru')
                ->where('must_change_password', true)
                ->count(),

            'homeroomNotAssigned' => $activeYearId
                ? Classroom::query()
                    ->whereDoesntHave('homeroomAssignments', function ($q) use ($activeYearId) {
                        $q->where('school_year_id', $activeYearId);
                    })
                    ->count()
                : 0,
        ];

        $items = [];

        if ($alerts['missingActiveSchoolYear']) {
            $items[] = [
                'key' => 'sys.missing_active_school_year',
                'title' => 'Tahun Ajaran belum aktif',
                'message' => 'Belum ada Tahun Ajaran yang aktif. Aktifkan agar proses akademik berjalan.',
                'level' => 'amber',
                'group' => 'Akademik',
                'action_url' => route('school-years.index'),
                'action_text' => 'Atur TA',
            ];
        }

        if ($alerts['studentsWithoutActiveEnrollment'] > 0) {
            $items[] = [
                'key' => 'sys.students_without_active_enrollment',
                'title' => 'Siswa aktif tanpa enrollment aktif',
                'message' => $alerts['studentsWithoutActiveEnrollment'].' siswa aktif belum punya enrollment aktif.',
                'level' => 'amber',
                'group' => 'Data',
                'action_url' => route('students.index', ['status' => 'aktif']),
                'action_text' => 'Cek Siswa',
            ];
        }

        if ($alerts['teachersWithoutAccount'] > 0) {
            $items[] = [
                'key' => 'sys.teachers_without_account',
                'title' => 'Guru aktif belum punya akun',
                'message' => $alerts['teachersWithoutAccount'].' guru aktif belum memiliki akun login.',
                'level' => 'blue',
                'group' => 'Akun',
                'action_url' => route('teachers.index'),
                'action_text' => 'Cek Guru',
            ];
        }

        if ($alerts['mustChangePasswordCount'] > 0) {
            $items[] = [
                'key' => 'sys.must_change_password',
                'title' => 'Akun wajib ganti password',
                'message' => $alerts['mustChangePasswordCount'].' akun guru masih wajib ganti password.',
                'level' => 'gray',
                'group' => 'Akun',
                'action_url' => route('teachers.index'),
                'action_text' => 'Review',
            ];
        }

        if ($alerts['homeroomNotAssigned'] > 0) {
            $items[] = [
                'key' => 'sys.homeroom_not_assigned',
                'title' => 'Kelas belum punya wali kelas',
                'message' => $alerts['homeroomNotAssigned'].' kelas pada TA aktif belum punya wali kelas.',
                'level' => 'amber',
                'group' => 'Akademik',
                'action_url' => route('homeroom-assignments.index'),
                'action_text' => 'Atur Wali',
            ];
        }

        foreach ($items as $payload) {
            $this->notifyOnce($user, $payload);
        }
    }

    /**
     * Notif personal untuk Guru/Wali: profil belum lengkap + must_change_password.
     */
    public function syncTeacher(User $user): void
    {
        if (!($user->isGuru() || $user->isWaliKelas())) {
            return;
        }

        // throttle biar gak generate notif tiap refresh (sekali per 12 jam)
        $cacheKey = "notif.sync.teacher.{$user->id}";
        if (!Cache::add($cacheKey, true, now()->addHours(12))) {
            return;
        }

        // must_change_password
        if ($user->must_change_password) {
            $this->notifyOnce($user, [
                'key' => 'me.must_change_password',
                'title' => 'Wajib ganti password',
                'message' => 'Akun kamu masih wajib ganti password. Silakan ganti password agar akses fitur penuh.',
                'level' => 'amber',
                'group' => 'Akun',
                'action_url' => route('password.change'),
                'action_text' => 'Ganti Password',
            ]);
        }

        // profil guru belum lengkap: phone/email/address
        $teacher = $user->teacher;
        if ($teacher) {
            $missing = [];
            if (blank($teacher->phone)) $missing[] = 'Telepon';
            if (blank($teacher->email)) $missing[] = 'Email';
            if (blank($teacher->address)) $missing[] = 'Alamat';

            if (!empty($missing)) {
                $this->notifyOnce($user, [
                    'key' => 'me.profile_incomplete',
                    'title' => 'Profil belum lengkap',
                    'message' => 'Lengkapi: '.implode(', ', $missing).'.',
                    'level' => 'blue',
                    'group' => 'Profil',
                    'action_url' => route('teachers.edit', $teacher),
                    'action_text' => 'Lengkapi Profil',
                ]);
            }
        }
    }

    /**
     * Simpan notif sekali saja (dedup by data->key).
     * Kalau sudah ada notif dengan key sama (unread atau read), tidak buat duplikat.
     */
    private function notifyOnce(User $user, array $payload): void
    {
        $key = $payload['key'] ?? null;
        if (!$key) {
            $user->notify(new SystemAlertNotification($payload));
            return;
        }

        $exists = $user->notifications()
            ->where('data->key', $key)
            ->exists();

        if (!$exists) {
            $user->notify(new SystemAlertNotification($payload));
        }
    }
}
