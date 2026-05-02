<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Events\LhpStatistikUpdated;
use Illuminate\Support\Facades\DB;

class LhpStatistikService
{
    private static array $processing = [];

    public function hitung($lhp): void
    {
        $lhpId = $lhp instanceof Lhp ? $lhp->id : $lhp;
        $this->updateStatistik($lhpId);
    }

   public function updateStatistik(int $lhpId): void
{
    if (isset(self::$processing[$lhpId])) return;
    self::$processing[$lhpId] = true;

    try {
        DB::transaction(function () use ($lhpId) {

            // ✅ FIX: Satu fresh() dengan SEMUA relasi yang dibutuhkan
            $lhp = Lhp::with([
                'auditAssignment',  // ← wajib ada, jangan di fresh() terpisah
                'temuans.recommendations.tindakLanjuts.cicilans',
            ])->find($lhpId);

            if (!$lhp) return;

            // ❌ HAPUS baris ini — fresh() membuang eager load auditAssignment
            // $lhp = $lhp->fresh(['temuans.recommendations.tindakLanjuts.cicilans']);

            $allRekom   = $lhp->temuans->flatMap->recommendations;
            $totalRekom = $allRekom->count();

            if ($totalRekom === 0) {
                $this->resetStatistik($lhpId);
                $totalKerugian = (float) $lhp->temuans->sum('nilai_temuan');

                LhpStatistik::updateOrCreate(
                    ['lhp_id' => $lhpId],
                    [
                        'total_temuan'   => $lhp->temuans->count(),
                        'total_kerugian' => $totalKerugian,
                        'dihitung_pada'  => now(),
                    ]
                );

                // ✅ Sinkronisasi assignment meski 0 rekomendasi
                $this->sinkronStatusAssignment($lhp, 0);

                event(new LhpStatistikUpdated($lhp->fresh()));
                return;
            }

            $bobotPerItem        = 100 / $totalRekom;
            $totalProgress       = 0;
            $rekomUangTotal      = 0;
            $rekomUangSelesai    = 0;
            $rekomNonuangTotal   = 0;
            $rekomNonuangSelesai = 0;
            $totalNilaiTlSelesai = 0;
            $totalSisaKerugian   = 0;
            $totalNilaiRekomUang = 0;

            foreach ($allRekom as $rekom) {
                $tls        = $rekom->tindakLanjuts;
                $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);
                $nilaiLunasRekomIni = 0;

                if ($rekom->isUang()) {
                    $rekomUangTotal++;
                    $totalNilaiRekomUang += $nilaiRekom;

                    foreach ($tls as $tl) {
                        if ($tl->jenis_penyelesaian === 'cicilan') {
                            $nilaiLunasRekomIni += (float) $tl->cicilans
                                ->where('status', 'diterima')
                                ->sum('nilai_bayar');
                        } elseif ($tl->status_verifikasi === 'lunas') {
                            $nilaiLunasRekomIni += (float) ($tl->nilai_tindak_lanjut ?? 0);
                        }
                    }

                    if ($nilaiRekom > 0) {
                        $persen = min($nilaiLunasRekomIni / $nilaiRekom, 1.0);
                        $totalProgress += $persen * $bobotPerItem;
                        if ($nilaiLunasRekomIni >= $nilaiRekom) $rekomUangSelesai++;
                    }
                } else {
                    $rekomNonuangTotal++;
                    if ($tls->where('status_verifikasi', 'lunas')->isNotEmpty()) {
                        $totalProgress += $bobotPerItem;
                        $rekomNonuangSelesai++;
                    }
                }

                $totalNilaiTlSelesai += $nilaiLunasRekomIni;
                $totalSisaKerugian   += max(0, $nilaiRekom - $nilaiLunasRekomIni);
            }

            $rekomSelesai = $rekomUangSelesai + $rekomNonuangSelesai;

            $rekomProses = $allRekom->filter(function ($r) {
                $tls = $r->tindakLanjuts;
                return $tls->where('status_verifikasi', 'lunas')->isEmpty()
                    && $tls->isNotEmpty();
            })->count();

            $rekomBelum    = max(0, $totalRekom - $rekomSelesai - $rekomProses);
            $totalKerugian = (float) $lhp->temuans->sum('nilai_temuan');
            $progresFinal  = round(min($totalProgress, 100), 2);

            $persenNilai = $totalNilaiRekomUang > 0
                ? round(min($totalNilaiTlSelesai / $totalNilaiRekomUang, 1.0) * 100, 2)
                : 0;

            LhpStatistik::updateOrCreate(
                ['lhp_id' => $lhpId],
                [
                    'total_temuan'            => $lhp->temuans->count(),
                    'total_rekomendasi'       => $totalRekom,
                    'rekom_selesai'           => $rekomSelesai,
                    'rekom_proses'            => $rekomProses,
                    'rekom_belum'             => $rekomBelum,
                    'rekom_uang_total'        => $rekomUangTotal,
                    'rekom_uang_selesai'      => $rekomUangSelesai,
                    'rekom_nonutang_total'    => $rekomNonuangTotal,   // ✅ sesuai migration
                    'rekom_nonutang_selesai'  => $rekomNonuangSelesai, // ✅ sesuai migration
                    'total_kerugian'          => $totalKerugian,
                    'total_nilai_tl_selesai'  => $totalNilaiTlSelesai,
                    'total_sisa_kerugian'     => $totalSisaKerugian,
                    'persen_selesai'          => round(($rekomSelesai / $totalRekom) * 100, 2),
                    'persen_selesai_nilai'    => $persenNilai,          // ✅ kolom yang ada di migration
                    'persen_selesai_gabungan' => $progresFinal,
                    'dihitung_pada'           => now(),
                ]
            );

            // ✅ Sinkronisasi status assignment — pisah ke method sendiri
            $this->sinkronStatusAssignment($lhp, $progresFinal);

            event(new LhpStatistikUpdated($lhp->fresh()));
        });
    } finally {
        unset(self::$processing[$lhpId]);
    }
}

/**
 * Sinkronisasi status AuditAssignment berdasarkan progres LHP.
 * Dipisah agar bisa dipanggil dari berbagai kondisi (0 rekom, ada rekom).
 */
private function sinkronStatusAssignment(Lhp $lhp, float $progresFinal): void
{
    if (! $lhp->audit_assignment_id) return;

    $assignment = $lhp->relationLoaded('auditAssignment')
        ? $lhp->auditAssignment
        : $lhp->auditAssignment()->first();

    if (! $assignment) return;

    // ── Sinkron status Assignment ──────────────────────────────
    $statusAssignment = match(true) {
        $progresFinal >= 100 => 'selesai',
        $progresFinal > 0    => 'berjalan',
        default              => 'draft',
    };

    if ($assignment->status !== $statusAssignment) {
        $assignment->updateQuietly(['status' => $statusAssignment]);
    }

    // ── Sinkron status AuditProgram ────────────────────────────
    $this->sinkronStatusProgram($assignment->audit_program_id);
}


private function sinkronStatusProgram(?int $auditProgramId): void
{
    if (! $auditProgramId) return;

    // Ambil semua status assignment yang terhubung ke program ini
    $statuses = \App\Models\AuditAssignment::where('audit_program_id', $auditProgramId)
        ->pluck('status');

    if ($statuses->isEmpty()) return;

    $statusProgram = match(true) {
        $statuses->every(fn($s) => $s === 'selesai')           => 'selesai',
        $statuses->contains(fn($s) => in_array($s, ['berjalan', 'selesai'])) => 'berjalan',
        default                                                  => 'draft',
    };

    \App\Models\AuditProgram::where('id', $auditProgramId)
        ->where('status', '!=', $statusProgram) // hanya update jika berbeda
        ->update(['status' => $statusProgram]);
}

    private function resetStatistik(int $lhpId): void
    {
        LhpStatistik::updateOrCreate(
            ['lhp_id' => $lhpId],
            [
                'total_temuan' => 0, 'total_rekomendasi' => 0, 'rekom_selesai' => 0,
                'rekom_proses' => 0, 'rekom_belum' => 0, 'rekom_uang_total' => 0,
                'rekom_uang_selesai' => 0, 'rekom_nonutang_total' => 0, 'rekom_nonutang_selesai' => 0,
                'total_kerugian' => 0, 'total_nilai_tl_selesai' => 0, 'total_sisa_kerugian' => 0,
                'persen_selesai' => 0, 'persen_selesai_gabungan' => 0, 'persen_selesai_nilai' => 0,
                'dihitung_pada' => now(),
            ]
        );
    }
}