<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin/Operator/Pimpinan boleh daftar guru
        if ($user->canManageSchoolData() || $user->isPimpinan()) return true;

        // Guru/WaliKelas minimal boleh “lihat data diri sendiri” (opsional)
        return $user->isGuru() || $user->isWaliKelas();
    }

    public function view(User $user, Teacher $teacher): bool
    {
        if ($user->canManageSchoolData() || $user->isPimpinan()) return true;

        // Guru/WaliKelas hanya boleh lihat dirinya sendiri
        return (bool) $user->teacher_id && $user->teacher_id === $teacher->id;
    }

    public function create(User $user): bool
    {
        return $user->canManageSchoolData();
    }

    public function update(User $user, Teacher $teacher): bool
    {
        if ($user->canManageSchoolData()) return true;

        // Guru/WaliKelas boleh update diri sendiri (field dibatasi di controller/request)
        return (bool) $user->teacher_id && $user->teacher_id === $teacher->id;
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin();
    }

    public function uploadDocument(User $user, Teacher $teacher): bool
    {
        if ($user->canManageSchoolData()) return true;
        return (bool) $user->teacher_id && $user->teacher_id === $teacher->id;
    }
}
