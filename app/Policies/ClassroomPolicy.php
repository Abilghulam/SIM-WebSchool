<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        // admin/operator boleh semua aksi master data
        if ($user->canManageSchoolData()) return true;
        return null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Classroom $classroom): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Classroom $classroom): bool { return false; }
    public function delete(User $user, Classroom $classroom): bool { return false; }
}
