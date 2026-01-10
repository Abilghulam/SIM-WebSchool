<?php

namespace App\Policies;

use App\Models\HomeroomAssignment;
use App\Models\User;

class HomeroomAssignmentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->canManageSchoolData()) return true;
        return null;
    }

    public function viewAny(User $user): bool { return false; }
    public function create(User $user): bool { return false; }
    public function delete(User $user, HomeroomAssignment $ha): bool { return false; }
}
