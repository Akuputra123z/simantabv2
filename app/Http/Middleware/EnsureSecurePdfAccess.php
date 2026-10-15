<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurePdfAccess
{
    /**
     * Pastikan pengguna telah memverifikasi password sebelum mengakses berkas PDF.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek validitas signed URL
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        // 2. Cek apakah ada session verifikasi password yang masih aktif
        $route = $request->route();
        $lhpParam = $route ? $route->parameter('lhp') : null;
        $uuidParam = $route ? $route->parameter('uuid') : null;

        $lhpId = is_object($lhpParam) ? ($lhpParam->id ?? null) : (is_numeric($lhpParam) ? $lhpParam : null);
        $lhpUuid = is_object($lhpParam) ? ($lhpParam->uuid ?? null) : (!is_numeric($lhpParam) ? $lhpParam : null);

        if ($lhpId || $lhpUuid) {
            $keys = array_filter([$lhpId, $lhpUuid]);
            foreach ($keys as $k) {
                $previewExpiry = session("pdf_access_verified.lhp_preview.{$k}");
                $downloadExpiry = session("pdf_access_verified.lhp_download.{$k}");
                $kepatuhanExpiry = session("pdf_access_verified.kepatuhan_pdf.{$k}");
                $kepatuhanShowExpiry = session("pdf_access_verified.kepatuhan_show.{$k}");
                $rekapShowExpiry = session("pdf_access_verified.rekap_show.{$k}");

                if (($previewExpiry && $previewExpiry >= now()->timestamp) ||
                    ($downloadExpiry && $downloadExpiry >= now()->timestamp) ||
                    ($kepatuhanExpiry && $kepatuhanExpiry >= now()->timestamp) ||
                    ($kepatuhanShowExpiry && $kepatuhanShowExpiry >= now()->timestamp) ||
                    ($rekapShowExpiry && $rekapShowExpiry >= now()->timestamp)) {
                    return $next($request);
                }
            }
        }

        if ($uuidParam) {
            $lhrExpiry = session("pdf_access_verified.lhr_pdf.{$uuidParam}");
            $opdLhrExpiry = session("pdf_access_verified.opd_lhr_pdf.{$uuidParam}");

            if (($lhrExpiry && $lhrExpiry >= now()->timestamp) ||
                ($opdLhrExpiry && $opdLhrExpiry >= now()->timestamp)) {
                return $next($request);
            }
        }

        // 3. Akses ditolak jika belum verifikasi password
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses ditolak: Verifikasi password keamanan akun diperlukan untuk mengakses dokumen PDF ini.',
            ], 403);
        }

        abort(403, 'Akses Ditolak: Dokumen ini dilindungi enkripsi keamanan. Anda wajib memverifikasi password akun terlebih dahulu untuk melihat dokumen PDF ini.');
    }
}
