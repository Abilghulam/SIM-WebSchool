<?php

namespace App\Policies;

use App\Models\SchoolYear;
use App\Models\User;

class SchoolYearPolicy
{
    private function allowed(User $user): bool
    {
        return in_array($user->role_label, ['admin', 'operator'], true);
    }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, SchoolYear $schoolYear): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, SchoolYear $schoolYear): bool { return $this->allowed($user); }
    public function delete(User $user, SchoolYear $schoolYear): bool { return $this->allowed($user); }
}
