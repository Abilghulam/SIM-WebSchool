<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navigation', function ($view) {
        $user = auth()->user();
        if (!$user) return;

        $unreadCount = $user->unreadNotifications()->count();
        $latest = $user->notifications()->latest()->limit(8)->get();

        $view->with('navUnreadCount', $unreadCount);
        $view->with('navNotifications', $latest);
    });
    }
}
