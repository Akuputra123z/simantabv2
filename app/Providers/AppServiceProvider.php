<?php

namespace App\Providers;

use App\Models\TindakLanjut;
use App\Models\User;
use App\Policies\TindakLanjutPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // ── 7. Enforce HTTPS in production / when FORCE_HTTPS=true ─────────
        if ($this->app->environment('production') || config('app.force_https', false)) {
            URL::forceScheme('https');
        }

        // ── 3. Kebijakan Kata Sandi Kuat (Password Complexity Policy) ────────
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return app()->isProduction()
                ? $rule->uncompromised()
                : (app()->environment('testing') ? Password::min(8) : $rule);
        });

        // Super Admin memiliki akses penuh ke seluruh fitur dan permission
        Gate::before(function ($user, $ability) {
            return $user->hasRole(User::ROLE_SUPER_ADMIN) ? true : null;
        });

        Gate::policy(TindakLanjut::class, TindakLanjutPolicy::class);

        // ── Audit Logging: Mendaftarkan subscriber pencatatan aktivitas autentikasi ──
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\LogAuthenticationEvents::class);
    }
}
