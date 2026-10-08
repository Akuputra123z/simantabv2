<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dashboard:sync', function () {
    $this->info('Memulai sinkronisasi seluruh data statistik dan dashboard...');

    // 1. Sinkronisasi Cicilan & Tindak Lanjut
    $tls = \App\Models\TindakLanjut::with('cicilans', 'recommendation')->get();
    foreach ($tls as $tl) {
        if ($tl->jenis_penyelesaian === 'cicilan') {
            $terbayar = (float) $tl->cicilans->where('status', 'diterima')->sum('nilai_bayar');
            $nilaiRekom = (float) ($tl->recommendation?->nilai_rekom ?? 0);
            $sisa = max(0, $nilaiRekom - $terbayar);
            $status = $terbayar <= 0 ? 'menunggu_verifikasi' : ($sisa <= 0 ? 'lunas' : 'berjalan');
            $tl->updateQuietly([
                'total_terbayar' => $terbayar,
                'sisa_belum_bayar' => $sisa,
                'status_verifikasi' => $status,
            ]);
        }
    }
    $this->line("✓ {$tls->count()} data Tindak Lanjut disinkronkan.");

    // 2. Sinkronisasi Rekomendasi
    $rekoms = \App\Models\Recommendation::with('tindakLanjuts.cicilans')->get();
    foreach ($rekoms as $rekom) {
        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);
        $nilaiLunas = 0;
        foreach ($rekom->tindakLanjuts as $tl) {
            if ($tl->jenis_penyelesaian === 'cicilan') {
                $nilaiLunas += (float) $tl->cicilans->where('status', 'diterima')->sum('nilai_bayar');
            } else {
                if ($tl->status_verifikasi === 'lunas') {
                    $nilaiLunas += (float) ($tl->total_terbayar > 0 ? $tl->total_terbayar : ($tl->nilai_tindak_lanjut > 0 ? $tl->nilai_tindak_lanjut : $nilaiRekom));
                } else {
                    $nilaiLunas += (float) $tl->total_terbayar;
                }
            }
        }
        $nilaiSisa = max(0, $nilaiRekom - $nilaiLunas);

        if ($rekom->isUang()) {
            if ($nilaiLunas >= $nilaiRekom && $nilaiRekom > 0) {
                $status = 'selesai';
            } elseif ($nilaiLunas > 0 || $rekom->tindakLanjuts->isNotEmpty()) {
                $status = 'dalam_proses';
            } else {
                $status = 'belum_ditindaklanjuti';
            }
        } else {
            if ($rekom->tindakLanjuts->where('status_verifikasi', 'lunas')->isNotEmpty() || $rekom->status === 'selesai') {
                $status = 'selesai';
            } elseif ($rekom->tindakLanjuts->isNotEmpty()) {
                $status = 'dalam_proses';
            } else {
                $status = 'belum_ditindaklanjuti';
            }
        }

        $rekom->updateQuietly([
            'nilai_tl_selesai' => $nilaiLunas,
            'nilai_sisa' => $nilaiSisa,
            'status' => $status,
        ]);
    }
    $this->line("✓ {$rekoms->count()} data Rekomendasi disinkronkan.");

    // 3. Sinkronisasi Temuan
    $temuans = \App\Models\Temuan::with('recommendations')->get();
    foreach ($temuans as $temuan) {
        $rCount = $temuan->recommendations->count();
        $rSelesai = $temuan->recommendations->where('status', 'selesai')->count();
        $rProses = $temuan->recommendations->whereIn('status', ['proses', 'dalam_proses'])->count();

        $statusTl = match (true) {
            $rCount > 0 && $rSelesai === $rCount => 'selesai',
            $rProses > 0 || $rSelesai > 0 => 'dalam_proses',
            default => 'belum_ditindaklanjuti',
        };

        $temuan->updateQuietly(['status_tl' => $statusTl]);
    }
    $this->line("✓ {$temuans->count()} data Temuan disinkronkan.");

    // 4. Hitung ulang statistik LHP & Audit Program
    $service = app(\App\Services\LhpStatistikService::class);
    $lhps = \App\Models\Lhp::all();
    foreach ($lhps as $lhp) {
        $service->updateStatistik($lhp->id);
    }
    $this->line("✓ Statistik {$lhps->count()} LHP dihitung ulang.");

    $programs = \App\Models\AuditProgram::all();
    foreach ($programs as $prog) {
        $service->sinkronStatusProgram($prog->id);
    }
    $this->line("✓ {$programs->count()} Audit Program disinkronkan.");

    // 5. Bersihkan cache dashboard & views
    \Illuminate\Support\Facades\Cache::flush();
    $this->call('view:clear');
    $this->call('config:clear');

    $this->info('Sinkronisasi database dengan Dashboard SIAPTUNTAS berhasil diselesaikan!');
})->purpose('Sinkronisasi ulang seluruh statistik LHP, Rekomendasi, Temuan, dan cache Dashboard');

