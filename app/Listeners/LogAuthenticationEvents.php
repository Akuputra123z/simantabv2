<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LogAuthenticationEvents
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Handle event Login sukses.
     */
    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->auditLogger->logAuthSuccess($event->user, request());
        }
    }

    /**
     * Handle event Login gagal (Bad credentials / non-existent user).
     */
    public function handleFailed(Failed $event): void
    {
        $identifier = $event->credentials['email'] 
            ?? $event->credentials['nip'] 
            ?? $event->credentials['name'] 
            ?? 'UNKNOWN';

        $this->auditLogger->logAuthFailed((string) $identifier, request(), 'Kredensial atau kata sandi tidak cocok.');
    }

    /**
     * Handle event Logout.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;
        $this->auditLogger->logAuthLogout($user, request());
    }

    /**
     * Handle event Lockout (Rate limiting exceeded).
     */
    public function handleLockout(Lockout $event): void
    {
        $this->auditLogger->logSecurityIncident('LOGIN_LOCKOUT_TRIGGERED', $event->request, [
            'reason' => 'Batas percobaan login terlampaui (Brute-force protection dipicu)',
        ]);
    }

    /**
     * Daftarkan seluruh listener peristiwa autentikasi ke dispatcher.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
            Lockout::class => 'handleLockout',
        ];
    }
}
