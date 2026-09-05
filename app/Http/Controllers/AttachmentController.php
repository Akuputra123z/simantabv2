<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * 🔥 SECURE DOWNLOAD LAMPIRAN (Terproteksi Autentikasi & Private Storage)
     */
    public function download(Attachment $attachment)
    {
        if (!$attachment->file_path) {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        // Cek disk privat 'local' terlebih dahulu, lalu disk 'public'
        if (Storage::disk('local')->exists($attachment->file_path)) {
            $filePath = Storage::disk('local')->path($attachment->file_path);
        } elseif (Storage::disk('public')->exists($attachment->file_path)) {
            $filePath = Storage::disk('public')->path($attachment->file_path);
        } else {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        $fileName = $attachment->file_name ?? basename($attachment->file_path);

        return response()->download($filePath, $fileName);
    }

    /**
     * 🔥 SECURE PREVIEW LAMPIRAN IN BROWSER (Terproteksi Autentikasi & Private Storage)
     */
    public function show(Attachment $attachment)
    {
        if (!$attachment->file_path) {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        if (Storage::disk('local')->exists($attachment->file_path)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($attachment->file_path)) {
            $disk = Storage::disk('public');
        } else {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        $filePath = $disk->path($attachment->file_path);
        $mimeType = $disk->mimeType($attachment->file_path);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($attachment->file_name ?? basename($attachment->file_path)) . '"'
        ]);
    }

    /**
     * 🔥 DELETE LAMPIRAN (Support kedua disk)
     */
    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        if ($attachment->file_path) {
            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $attachment->forceDelete();

        return back()->with('success', 'Lampiran berhasil dihapus');
    }
}