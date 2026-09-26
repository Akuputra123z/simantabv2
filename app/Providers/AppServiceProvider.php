<?php

namespace App\Providers;

use App\Models\TindakLanjut;
use App\Models\User;
use App\Policies\TindakLanjutPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Super Admin memiliki akses penuh ke seluruh fitur dan permission
        Gate::before(function ($user, $ability) {
            return $user->hasRole(User::ROLE_SUPER_ADMIN) ? true : null;
        });

        Gate::policy(TindakLanjut::class, TindakLanjutPolicy::class);
    }
}
