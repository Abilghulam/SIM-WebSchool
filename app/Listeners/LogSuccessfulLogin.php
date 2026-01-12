<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $userId = $event->user?->getAuthIdentifier();

        if (!$userId) {
            return;
        }

        User::query()
            ->whereKey($userId)
            ->update(['last_login_at' => now()]);
    }
}
