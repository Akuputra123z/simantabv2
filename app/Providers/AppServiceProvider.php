<?php

namespace App\Providers;

use App\Models\TindakLanjut;
use App\Policies\TindakLanjutPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Gate::policy(TindakLanjut::class, TindakLanjutPolicy::class);
    }
}
