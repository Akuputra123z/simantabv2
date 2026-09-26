<?php

namespace App\Actions\Lhr;

use App\Enums\LhrStatus;
use App\Models\Lhr\Lhr;
use App\Models\User;

class ReviewLhr
{
    /**
     * Telaah LHR oleh Pengendali Teknis (Irban): setujui atau kembalikan untuk perbaikan.
     */
    public function execute(Lhr $lhr, User $reviewer, string $action, ?string $catatan = null): Lhr
    {
        if ($lhr->status !== LhrStatus::MenungguTelaah) {
            throw new \DomainException('Dokumen LHR belum dalam status menunggu telaah.');
        }

        if ($action === 'setujui') {
            $lhr->update([
                'status' => LhrStatus::MenungguPersetujuan,
            ]);
        } elseif ($action === 'kembalikan') {
            if (empty($catatan)) {
                throw new \InvalidArgumentException('Catatan perbaikan wajib diisi jika telaah dikembalikan.');
            }

            $lhr->update([
                'status' => LhrStatus::PerluPerbaikan,
            ]);
        } else {
            throw new \InvalidArgumentException("Aksi telaah tidak valid: {$action}");
        }

        return $lhr;
    }
}
