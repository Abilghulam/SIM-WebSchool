<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    private function isAdminOrOperator(User $user): bool
    {
        return in_array($user->role_label, ['admin', 'operator'], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdminOrOperator($user);
    }

    public function view(User $user, SchoolYear $schoolYear): bool
    {
        return $this->isAdminOrOperator($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrOperator($user);
    }

    public function update(User $user, SchoolYear $schoolYear): bool
    {
        // ✅ TA terkunci tidak boleh diubah
        if ($schoolYear->is_locked) return false;

        return $this->isAdminOrOperator($user);
    }

    public function delete(User $user, SchoolYear $schoolYear): bool
    {
        // ✅ TA terkunci tidak boleh dihapus
        if ($schoolYear->is_locked) return false;

        return $this->isAdminOrOperator($user);
    }

    /**
     * ✅ Ability untuk aktivasi TA
     */
    public function activate(User $user, SchoolYear $schoolYear): bool
    {
        // ✅ TA terkunci tidak boleh diaktifkan lagi
        if ($schoolYear->is_locked) return false;

        return $this->isAdminOrOperator($user);
    }

    /**
     * ✅ Ability untuk tombol Promote di index
     */
    public function promoteEnrollment(User $user): bool
    {
        return $this->isAdminOrOperator($user);
    }

    /**
     * (Opsional) Lock/Unlock manual via tombol/action khusus
     */
    public function lock(User $user, SchoolYear $schoolYear): bool
    {
        return $this->isAdminOrOperator($user);
    }

    public function unlock(User $user, SchoolYear $schoolYear): bool
    {
        return $this->isAdminOrOperator($user);
    }
}
