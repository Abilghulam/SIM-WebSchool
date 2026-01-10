<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    private function allowed(User $user): bool
    {
        return in_array($user->role_label, ['admin', 'operator'], true);
    }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, Major $major): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, Major $major): bool { return $this->allowed($user); }
    public function delete(User $user, Major $major): bool { return $this->allowed($user); }
}
