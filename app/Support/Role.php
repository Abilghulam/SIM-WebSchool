<?php

namespace App\Support;

use App\Models\User;

class Role
{
    public static function is(?User $user, string ...$roles): bool
    {
        if (!$user) return false;
        return in_array($user->role_label, $roles, true);
    }
}
