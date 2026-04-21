<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * 🔥 DELETE LAMPIRAN
     */
    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        // 🔥 HAPUS FILE DI STORAGE
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // 🔥 HAPUS DATA (force delete biar trigger model jalan)
        $attachment->forceDelete();

        return back()->with('success', 'Lampiran berhasil dihapus');
    }
}