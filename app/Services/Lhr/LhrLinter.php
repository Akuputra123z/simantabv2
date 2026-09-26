<?php

namespace App\Services\Lhr;

use App\Enums\SimpulanStatus;
use App\Models\Lhr\Lhr;

class LhrLinterResult
{
    /** @var array<int, array{code: string, message: string, target?: string}> */
    public array $errors = [];

    /** @var array<int, array{code: string, message: string, target?: string}> */
    public array $warnings = [];

    public function addError(string $code, string $message, ?string $target = null): void
    {
        $this->errors[] = [
            'code' => $code,
            'message' => $message,
            'target' => $target,
        ];
    }

    public function addWarning(string $code, string $message, ?string $target = null): void
    {
        $this->warnings[] = [
            'code' => $code,
            'message' => $message,
            'target' => $target,
        ];
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return ! empty($this->warnings);
    }
}

class LhrLinter
{
    /**
     * Jalankan seluruh aturan validasi linter untuk LHR.
     */
    public function lint(Lhr $lhr): LhrLinterResult
    {
        $result = new LhrLinterResult;

        $this->checkPengujianStatus($lhr, $result);
        $this->checkCatatanRekomendasi($lhr, $result);
        $this->checkPlaceholders($lhr, $result);
        $this->checkKronologiTanggal($lhr, $result);
        $this->checkInkonsistensiSimpulan($lhr, $result);

        return $result;
    }

    /**
     * L-02: Ada pengujian berstatus 'belum_dinilai' (Galat)
     */
    protected function checkPengujianStatus(Lhr $lhr, LhrLinterResult $result): void
    {
        $pengujians = $lhr->pengujians()->with('children')->get();

        foreach ($pengujians as $peng) {
            // Jika butir ini adalah induk yang memiliki sub-pengujian anak
            if ($peng->children->isNotEmpty()) {
                $unassessedChildren = $peng->children->filter(fn ($c) => $c->status_simpulan === SimpulanStatus::BelumDinilai);
                if ($unassessedChildren->isNotEmpty() && $peng->status_simpulan === SimpulanStatus::BelumDinilai) {
                    $result->addError(
                        'L-02',
                        "Sub-pengujian pada '{$peng->kode} - {$peng->judul}' belum lengkap dinilai (" . $unassessedChildren->count() . ' butir tersisa).',
                        "pengujian_{$peng->id}"
                    );
                }
                continue;
            }

            // Jika butir daun (leaf)
            if ($peng->status_simpulan === SimpulanStatus::BelumDinilai) {
                $result->addError(
                    'L-02',
                    "Pengujian '{$peng->kode} - {$peng->judul}' belum dinilai simpulan akhirnya.",
                    "pengujian_{$peng->id}"
                );
            }
        }
    }

    /**
     * L-03: Ada catatan tanpa rekomendasi (Galat)
     * L-12: Kategori catatan kosong (Galat)
     */
    protected function checkCatatanRekomendasi(Lhr $lhr, LhrLinterResult $result): void
    {
        $pengujians = $lhr->pengujians()->with(['catatans.rekomendasis', 'catatans.kategori'])->get();

        foreach ($pengujians as $peng) {
            foreach ($peng->catatans as $cat) {
                if ($cat->rekomendasis->isEmpty()) {
                    $result->addError(
                        'L-03',
                        "Catatan pada pengujian '{$peng->kode}' tidak memiliki rekomendasi perbaikan.",
                        "catatan_{$cat->id}"
                    );
                }

                if (empty($cat->kategori_id)) {
                    $result->addError(
                        'L-12',
                        "Catatan pada pengujian '{$peng->kode}' wajib memilih Kategori Catatan.",
                        "catatan_{$cat->id}"
                    );
                }
            }
        }
    }

    /**
     * L-04: Placeholder {{...}} tersisa di teks naratif (Galat)
     */
    protected function checkPlaceholders(Lhr $lhr, LhrLinterResult $result): void
    {
        foreach ($lhr->sections as $section) {
            if ($section->isi && preg_match('/\{\{[^}]+\}\}/', $section->isi, $matches)) {
                $result->addError(
                    'L-04',
                    "Bagian '{$section->judul}' masih memuat placeholder yang belum terisi: {$matches[0]}.",
                    "section_{$section->id}"
                );
            }
        }
    }

    /**
     * L-05: Kronologi tidak valid: tanggal SPT <= mulai <= selesai <= tanggal LHR (Galat)
     */
    protected function checkKronologiTanggal(Lhr $lhr, LhrLinterResult $result): void
    {
        $spt = $lhr->auditAssignment;
        $tanggalSpt = $spt?->tanggal_mulai;
        $mulai = $lhr->tanggal_mulai;
        $selesai = $lhr->tanggal_selesai;
        $tanggalLhr = $lhr->tanggal_lhr;

        if ($mulai && $selesai && $mulai->gt($selesai)) {
            $result->addError(
                'L-05',
                'Tanggal mulai pelaksanaan ('.$mulai->format('d-m-Y').') melampaui tanggal selesai ('.$selesai->format('d-m-Y').').',
                'tanggal_mulai'
            );
        }

        if ($selesai && $tanggalLhr && $selesai->gt($tanggalLhr)) {
            $result->addError(
                'L-05',
                'Tanggal laporan LHR ('.$tanggalLhr->format('d-m-Y').') mendahului tanggal selesai reviu ('.$selesai->format('d-m-Y').').',
                'tanggal_lhr'
            );
        }

        if ($tanggalSpt && $mulai && $tanggalSpt->gt($mulai)) {
            $result->addError(
                'L-05',
                'Tanggal mulai reviu ('.$mulai->format('d-m-Y').') mendahului tanggal surat tugas SPT ('.$tanggalSpt->format('d-m-Y').').',
                'tanggal_mulai'
            );
        }
    }

    /**
     * L-07: Simpulan sesuai tetapi memiliki catatan (Peringatan)
     * L-08: Pengujian belum_sesuai / sebagian_sesuai tanpa rekomendasi (Peringatan)
     */
    protected function checkInkonsistensiSimpulan(Lhr $lhr, LhrLinterResult $result): void
    {
        $pengujians = $lhr->pengujians()->with(['catatans.rekomendasis'])->get();

        foreach ($pengujians as $peng) {
            $hasCatatan = $peng->catatans->isNotEmpty();
            $status = $peng->status_simpulan;

            if ($status === SimpulanStatus::Sesuai && $hasCatatan) {
                $result->addWarning(
                    'L-07',
                    "Pengujian '{$peng->kode}' berstatus 'Sesuai' namun memiliki catatan temuan.",
                    "pengujian_{$peng->id}"
                );
            }

            if (in_array($status, [SimpulanStatus::SebagianSesuai, SimpulanStatus::BelumSesuai], true)) {
                $hasRekomendasi = $peng->catatans->pluck('rekomendasis')->flatten()->isNotEmpty();
                if (! $hasRekomendasi) {
                    $result->addWarning(
                        'L-08',
                        "Pengujian '{$peng->kode}' berstatus '{$status->label()}' namun belum memiliki rekomendasi perbaikan.",
                        "pengujian_{$peng->id}"
                    );
                }
            }
        }
    }
}
