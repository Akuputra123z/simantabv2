<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Lhp;
use App\Models\Lhr\Lhr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class DocumentAccessSecurityController extends Controller
{
    /**
     * Verifikasi password akun pengguna untuk membuka dokumen PDF terenkripsi/terproteksi.
     */
    public function verifyPdfAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'document_type' => ['required', 'string', 'in:lhp_preview,lhp_download,lhr_pdf,opd_lhr_pdf,kepatuhan_pdf,kepatuhan_show,rekap_show'],
            'document_id' => ['required', 'string'],
        ], [
            'password.required' => 'Password akun wajib diisi untuk verifikasi keamanan.',
            'document_type.required' => 'Tipe dokumen tidak valid.',
            'document_id.required' => 'ID dokumen tidak valid.',
        ]);

        $user = Auth::user();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            Log::warning('Gagal verifikasi password untuk akses PDF', [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'ip' => $request->ip(),
                'document_type' => $validated['document_type'],
                'document_id' => $validated['document_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password akun yang Anda masukkan tidak sesuai. Akses ke dokumen ditolak.',
            ], 422);
        }

        $type = $validated['document_type'];
        $id = $validated['document_id'];

        // Validasi keberadaan dokumen
        $url = null;
        $expiresAt = now()->addMinutes(15);

        switch ($type) {
            case 'lhp_preview':
                $lhp = Lhp::where('id', $id)->orWhere('uuid', $id)->first();
                if (! $lhp) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHP tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('laporan.preview-pdf-per-lhp', $expiresAt, ['lhp' => $lhp->uuid]);
                break;

            case 'lhp_download':
                $lhp = Lhp::where('id', $id)->orWhere('uuid', $id)->first();
                if (! $lhp) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHP tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('laporan.download-pdf-per-lhp', $expiresAt, ['lhp' => $lhp->uuid]);
                break;

            case 'lhr_pdf':
                $lhr = Lhr::where('uuid', $id)->first();
                if (! $lhr) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHR tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('lhr.pdf', $expiresAt, ['uuid' => $lhr->uuid]);
                break;

            case 'opd_lhr_pdf':
                $lhr = Lhr::where('uuid', $id)->first();
                if (! $lhr) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHR tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('opd.lhr.pdf', $expiresAt, ['uuid' => $lhr->uuid]);
                break;

            case 'kepatuhan_pdf':
                $lhp = Lhp::where('id', $id)->orWhere('uuid', $id)->first();
                if (! $lhp) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHP tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('laporan.kepatuhan.pdf', $expiresAt, ['lhp' => $lhp->uuid]);
                break;

            case 'kepatuhan_show':
                $lhp = Lhp::where('id', $id)->orWhere('uuid', $id)->first();
                if (! $lhp) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHP tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('laporan.kepatuhan.show', $expiresAt, ['lhp' => $lhp->uuid]);
                break;

            case 'rekap_show':
                $lhp = Lhp::where('id', $id)->orWhere('uuid', $id)->first();
                if (! $lhp) {
                    return response()->json(['success' => false, 'message' => 'Dokumen LHP tidak ditemukan.'], 404);
                }
                $url = URL::temporarySignedRoute('laporan.rekap-per-lhp', $expiresAt, ['lhp' => $lhp->uuid]);
                break;
        }

        // Simpan sesi izin sementara 15 menit
        $sessionKey = "pdf_access_verified.{$type}.{$id}";
        $request->session()->put($sessionKey, now()->addMinutes(15)->timestamp);

        if (isset($lhp) && $lhp) {
            $request->session()->put("pdf_access_verified.{$type}.{$lhp->id}", now()->addMinutes(15)->timestamp);
            $request->session()->put("pdf_access_verified.{$type}.{$lhp->uuid}", now()->addMinutes(15)->timestamp);

            if (in_array($type, ['kepatuhan_show', 'kepatuhan_pdf'])) {
                $request->session()->put("pdf_access_verified.kepatuhan_show.{$lhp->id}", now()->addMinutes(15)->timestamp);
                $request->session()->put("pdf_access_verified.kepatuhan_show.{$lhp->uuid}", now()->addMinutes(15)->timestamp);
                $request->session()->put("pdf_access_verified.kepatuhan_pdf.{$lhp->id}", now()->addMinutes(15)->timestamp);
                $request->session()->put("pdf_access_verified.kepatuhan_pdf.{$lhp->uuid}", now()->addMinutes(15)->timestamp);
            }
        }

        Log::info('Berhasil verifikasi password untuk akses PDF', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'document_type' => $type,
            'document_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password terverifikasi dengan aman.',
            'url' => $url,
        ]);
    }
}
