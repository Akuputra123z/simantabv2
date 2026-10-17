<?php

namespace App\Http\Middleware;

use App\Services\SessionSecurityManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionSecurity
{
    public function __construct(
        protected SessionSecurityManager $sessionManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Poin 5: Cegah Trans-SID dan manipulasi Session ID via URL query parameter
        if ($request->query->has('session_id') || $request->query->has('PHPSESSID') || $request->query->has('session-token')) {
            $request->query->remove('session_id');
            $request->query->remove('PHPSESSID');
            $request->query->remove('session-token');
        }

        // 2. Pemeriksaan khusus untuk sesi pengguna terautentikasi
        if (Auth::check()) {
            // Poin 4: Validasi Idle Session Timeout (waktu habis sesi tidak aktif)
            if ($this->sessionManager->isSessionIdle($request)) {
                $this->sessionManager->terminateSession($request);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.',
                    ], 401);
                }

                return redirect()->route('login')->with('status', 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.');
            }

            // Poin 6: Validasi Integritas Lingkungan Sesi (Anti-Session Hijacking)
            if (! $this->sessionManager->isValidFingerprint($request)) {
                $this->sessionManager->terminateSession($request);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Sesi dihentikan demi keamanan karena terdeteksi perubahan lingkungan perangkat.',
                    ], 401);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Sesi Anda dihentikan demi keamanan karena terdeteksi perubahan perangkat yang mencurigakan. Silakan masuk kembali.',
                ]);
            }

            // Poin 3: Rotasi ID Sesi secara berkala (Periodic Session ID Rotation)
            $this->sessionManager->rotateSessionIfDue($request);

            // Perbarui jejak waktu aktivitas pengguna
            $this->sessionManager->touchActivity($request);
        }

        $response = $next($request);

        // Poin 6: Perlindungan transmisi dan larangan caching untuk halaman sesi terautentikasi
        if (Auth::check() && $response instanceof Response) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }
}
