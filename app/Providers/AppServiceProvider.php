<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share the authenticated user with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user()->load('roles');
                $view->with('auth', ['user' => $user]);
            }
        });
    }
}
