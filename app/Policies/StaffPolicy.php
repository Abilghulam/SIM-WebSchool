<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        // admin/operator/pimpinan boleh lihat list
        return $user->canManageSchoolData() || $user->isPimpinan();
    }

    public function view(User $user, Staff $staff): bool
    {
        if ($user->canManageSchoolData() || $user->isPimpinan()) return true;

        // kalau suatu saat TAS boleh lihat profil sendiri:
        return (bool) $user->staff_id && (int)$user->staff_id === (int)$staff->id;
    }

    public function create(User $user): bool
    {
        return $user->canManageSchoolData();
    }

    public function update(User $user, Staff $staff): bool
    {
        return $user->canManageSchoolData();
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $user->isAdmin();
    }

    public function createAccount(User $user, Staff $staff): bool
    {
        // hanya admin/operator; pencipta akun TAS yang role=operator nanti tetap super admin yang buat
        return $user->isAdmin() || $user->isOperator();
    }
}
