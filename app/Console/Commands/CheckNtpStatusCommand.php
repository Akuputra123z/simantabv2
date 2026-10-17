<?php

namespace App\Console\Commands;

use App\Services\TimeSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckNtpStatusCommand extends Command
{
    protected $signature = 'system:ntp-status 
                            {--alert : Catat log peringatan jika sinkronisasi waktu mengalami drift}';

    protected $description = 'Memeriksa status sinkronisasi waktu NTP, zona waktu aplikasi, dan database';

    public function handle(TimeSyncService $timeSyncService): int
    {
        $this->info('Memeriksa Sinkronisasi Sumber Waktu & Layanan NTP...');
        $this->line(str_repeat('=', 80));

        $status = $timeSyncService->checkSyncStatus();

        $rows = [
            ['Zona Waktu Aplikasi', $status['app_timezone']],
            ['Waktu Server Lokal', $status['local_time']],
            ['Zona Waktu Database', $status['db_timezone']],
            ['Waktu Database', $status['db_time']],
            ['Server NTP Dihubungi', $status['ntp_server']],
            ['Waktu Standar NTP', $status['ntp_time']],
            ['Selisih Waktu (Drift)', $status['drift_seconds'] . ' detik'],
            ['Status Sinkronisasi', $status['is_synced'] ? 'TERHUBUNG & SINKRON' : ($status['ntp_reachable'] ? 'DRIFT MELEBIHI BATAS' : 'NTP STANDALONE / OFFLINE')],
        ];

        $this->table(['Parameter', 'Nilai'], $rows);

        $this->line(str_repeat('-', 80));

        if ($status['is_synced']) {
            $this->info("[OK] " . $status['message']);
            return self::SUCCESS;
        }

        if ($status['ntp_reachable']) {
            $this->warn("[PERINGATAN] " . $status['message']);
            if ($this->option('alert')) {
                Log::warning('Sinkronisasi waktu NTP terdeteksi mengalami drift: ' . $status['drift_seconds'] . ' detik');
            }
            return self::FAILURE;
        }

        $this->comment("[CATATAN] " . $status['message']);
        return self::SUCCESS;
    }
}
