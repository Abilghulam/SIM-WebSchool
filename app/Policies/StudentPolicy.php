<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolYear;
use App\Models\HomeroomAssignment;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin/Operator/Pimpinan boleh lihat daftar
        if ($user->canManageSchoolData() || $user->isPimpinan()) {
            return true;
        }

        // Homeroom assignment TA aktif => boleh akses konteks siswa kelasnya
        // (meskipun role_label belum/ tidak wali_kelas)
        if ($user->can('viewMyClass')) {
            return true;
        }

        return false;
    }

    public function view(User $user, Student $student): bool
    {
        // Admin/Operator/Pimpinan boleh lihat semua
        if ($user->canManageSchoolData() || $user->isPimpinan()) {
            return true;
        }

        // Homeroom assignment TA aktif => boleh lihat siswa di kelasnya
        if ($user->can('viewMyClass') && $user->teacher_id) {
            $activeYearId = SchoolYear::activeId();
            if (!$activeYearId) return false;

            $classroomIds = HomeroomAssignment::query()
                ->where('school_year_id', $activeYearId)
                ->where('teacher_id', $user->teacher_id)
                ->pluck('classroom_id');

            if ($classroomIds->isEmpty()) return false;

            return $student->enrollments()
                ->where('school_year_id', $activeYearId)
                ->where('is_active', true)
                ->whereIn('classroom_id', $classroomIds)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageSchoolData();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->canManageSchoolData();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function changeStatus(User $user, Student $student): bool
    {
        return $user->canManageSchoolData();
    }

    public function assignClass(User $user, Student $student): bool
    {
        return $user->canManageSchoolData();
    }

    public function uploadDocument(User $user, Student $student): bool
    {
        return $user->canManageSchoolData();
    }
}
