<?php

use App\Http\Middleware\EnforceSessionSecurity;
use App\Http\Middleware\EnsureSecurePdfAccess;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Poin 2 & 7: Pengendali sesi framework & pembatalan sesi ganda saat kredensial diubah
        $middleware->authenticateSessions();

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'secure.pdf' => EnsureSecurePdfAccess::class,
            'session.secure' => EnforceSessionSecurity::class,
        ]);

        $middleware->web(append: [
            EnforceSessionSecurity::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Poin 1 & 2: Mencegah kebocoran SQL error dan menangani kesalahan terprediksi serta tidak terduga
        $exceptions->render(function (\Illuminate\Database\QueryException|\PDOException $e, \Illuminate\Http\Request $request) {
            $incidentId = 'INC-' . strtoupper(substr(\Illuminate\Support\Str::uuid()->toString(), 0, 12));

            // Catat rincian insiden ke audit log terenkripsi
            try {
                app(\App\Services\AuditLoggerService::class)->logSystemIncident($e, $incidentId, $request);
            } catch (\Throwable $logEx) {
                \Illuminate\Support\Facades\Log::critical("[{$incidentId}] Database Error: " . $e->getMessage());
            }

            // Kembalikan respons aman tanpa detail SQL
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kendala pada pemrosesan database. Permintaan Anda telah dicatat untuk ditindaklanjuti.',
                    'incident_id' => $incidentId,
                ], 500);
            }

            return response()->view('errors.500', [
                'incidentId' => $incidentId,
            ], 500);
        });

        // Tangani Token CSRF Kedaluwarsa (419)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                ], 419);
            }

            return response()->view('errors.419', [], 419);
        });

        // Tangani Tautan Tanda Tangan Digital Kedaluwarsa/Tidak Valid (Signed URL Expiration)
        $exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, \Illuminate\Http\Request $request) {
            if ($request->is('verify-account/*')) {
                return redirect()->route('account.verify.request')->withErrors([
                    'identifier' => 'Tautan verifikasi akun telah kedaluwarsa atau tidak valid (misal: database baru saja di-reset atau masa berlaku 60 menit habis). Silakan masukkan Email atau NIP untuk meminta tautan verifikasi baru.',
                ]);
            }

            if ($request->is('verify-email/*') || $request->is('email/verify/*')) {
                return redirect()->route('verification.notice')->with('error', 'Tautan verifikasi email telah kedaluwarsa. Silakan klik tombol kirim ulang.');
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan tidak valid atau telah kedaluwarsa.',
                ], 403);
            }

            return response()->view('errors.403', [
                'exception' => new \Exception('Tautan yang Anda akses tidak valid atau telah kedaluwarsa demi alasan keamanan. Silakan minta tautan baru.'),
            ], 403);
        });

        // Tangani Akses Ditolak (403)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface|\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : 403;

            if ($statusCode === 403) {
                try {
                    app(\App\Services\AuditLoggerService::class)->logSecurityIncident('UNAUTHORIZED_ACCESS_ATTEMPT', $request, [
                        'reason' => $e->getMessage() ?: 'Akses ditolak (403 Forbidden)',
                    ]);
                } catch (\Throwable) {
                    // Ignore
                }

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Anda tidak memiliki hak akses atau izin untuk mengakses fitur ini.',
                    ], 403);
                }

                return response()->view('errors.403', [
                    'exception' => $e,
                ], 403);
            }

            return null; // Biarkan Laravel menangani status code lainnya secara standar
        });
    })->create();
