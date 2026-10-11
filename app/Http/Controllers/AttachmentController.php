<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Lhp;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * 🔥 SECURE DOWNLOAD LAMPIRAN (Terproteksi Autentikasi & Otorisasi Akses)
     */
    public function download(Attachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment);

        if (! $attachment->file_path) {
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
     * 🔥 SECURE PREVIEW LAMPIRAN IN BROWSER (Terproteksi Autentikasi & Otorisasi Akses)
     */
    public function show(Attachment $attachment)
    {
        $this->authorizeAttachmentAccess($attachment);

        if (! $attachment->file_path) {
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
            'Content-Disposition' => 'inline; filename="'.($attachment->file_name ?? basename($attachment->file_path)).'"',
        ]);
    }

    /**
     * 🔥 DELETE LAMPIRAN (Terproteksi Otorisasi)
     */
    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        $this->authorizeAttachmentDelete($attachment);

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

    /**
     * Periksa otorisasi apakah user yang login berhak melihat/mengunduh lampiran ini.
     */
    private function authorizeAttachmentAccess(Attachment $attachment): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        // Super Admin dan jajaran pimpinan inspektorat memiliki akses penuh
        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat'])) {
            return;
        }

        // Pengunggah berkas selalu berhak mengakses berkasnya
        if ($attachment->uploaded_by === $user->id) {
            return;
        }

        // Jika user adalah OPD, verifikasi kepemilikan unit kerja
        if ($user->hasRole('opd')) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            $unitId = $this->getAssociatedUnitId($attachment);

            if ($unitId && $unitIds->contains($unitId)) {
                return;
            }

            abort(403, 'Anda tidak memiliki hak untuk mengakses berkas lampiran ini.');
        }

        // Jika user auditor/inspektorat lainnya, izinkan jika terlibat dalam penugasan terkait
        $assignment = $this->getAssociatedAssignment($attachment);
        if ($assignment) {
            $isMember = $assignment->ketua_tim_id === $user->id
                || $assignment->members()->where('user_id', $user->id)->exists();
            if ($isMember) {
                return;
            }
        }

        // Default izinkan untuk staf/auditor inspektorat internal
        if ($user->hasRole(['irban', 'pengendali_teknis', 'ketua_tim', 'anggota', 'staff_inspektorat'])) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak untuk mengakses berkas lampiran ini.');
    }

    /**
     * Periksa otorisasi apakah user berhak menghapus lampiran ini.
     */
    private function authorizeAttachmentDelete(Attachment $attachment): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_inspektorat', 'kepala_inspektorat'])) {
            return;
        }

        // Pengunggah boleh menghapus jika belum diverifikasi lunas / terbit
        if ($attachment->uploaded_by === $user->id) {
            if ($attachment->attachable instanceof TindakLanjut) {
                if ($attachment->attachable->status_verifikasi === 'lunas') {
                    abort(403, 'Lampiran tindak lanjut yang sudah berstatus lunas tidak dapat dihapus.');
                }
            }
            if ($attachment->attachable instanceof \App\Models\Lhr\Lhr) {
                if ($attachment->attachable->isTerbit()) {
                    abort(403, 'Lampiran LHR yang sudah berstatus terbit resmi tidak dapat dihapus.');
                }
            }

            return;
        }

        abort(403, 'Anda tidak memiliki hak untuk menghapus lampiran ini.');
    }

    /**
     * Ambil unit_diperiksa_id yang terkait dengan lampiran.
     */
    private function getAssociatedUnitId(Attachment $attachment): ?int
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof TindakLanjut) {
            return $attachable->recommendation?->temuan?->lhp?->unit_diperiksa_id;
        }

        if ($attachable instanceof TindakLanjutCicilan) {
            return $attachable->tindakLanjut?->recommendation?->temuan?->lhp?->unit_diperiksa_id;
        }

        if ($attachable instanceof Lhp) {
            return $attachable->unit_diperiksa_id;
        }

        if ($attachable instanceof \App\Models\Lhr\Lhr) {
            return $attachable->unit_diperiksa_id;
        }

        if ($attachable instanceof Temuan) {
            return $attachable->lhp?->unit_diperiksa_id;
        }

        return null;
    }

    /**
     * Ambil audit assignment yang terkait dengan lampiran.
     */
    private function getAssociatedAssignment(Attachment $attachment)
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof TindakLanjut) {
            return $attachable->recommendation?->temuan?->lhp?->auditAssignment;
        }

        if ($attachable instanceof TindakLanjutCicilan) {
            return $attachable->tindakLanjut?->recommendation?->temuan?->lhp?->auditAssignment;
        }

        if ($attachable instanceof Lhp) {
            return $attachable->auditAssignment;
        }

        if ($attachable instanceof \App\Models\Lhr\Lhr) {
            return $attachable->auditAssignment;
        }

        if ($attachable instanceof Temuan) {
            return $attachable->lhp?->auditAssignment;
        }

        return null;
    }
}
