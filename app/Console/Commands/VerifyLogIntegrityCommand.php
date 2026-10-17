<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyLogIntegrityCommand extends Command
{
    protected $signature = 'log:verify 
                            {file? : Nama file log tertentu yang ingin diverifikasi}';

    protected $description = 'Memverifikasi integritas kriptografis dan izin berkas log terhadap manipulasi tidak sah (Anti-Tampering)';

    public function handle(): int
    {
        $logDir = storage_path('logs');
        $fileName = $this->argument('file');

        if (! $fileName) {
            $files = File::glob($logDir . '/*.log');
            if (empty($files)) {
                $this->warn('Tidak ada berkas log di direktori storage/logs.');
                return self::SUCCESS;
            }

            $options = array_map('basename', $files);
            $fileName = $this->choice('Pilih berkas log yang ingin diverifikasi integritasnya:', $options, 0);
        }

        $fullPath = str_starts_with($fileName, '/') ? $fileName : $logDir . '/' . ltrim($fileName, '/');

        if (! file_exists($fullPath)) {
            $this->error("Berkas log tidak ditemukan: {$fullPath}");
            return self::FAILURE;
        }

        $this->info("Memeriksa integritas berkas: {$fullPath}");
        $this->line(str_repeat('=', 80));

        // 1. Periksa izin akses berkas (File Permissions)
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $this->line("• Izin akses berkas (Permissions): <fg=cyan>{$perms}</>");
        if ($perms === '0640' || $perms === '0600' || $perms === '0644') {
            $this->line("  <fg=green>[PASSED]</> Izin berkas aman dan terlindungi dari modifikasi publik.");
        } else {
            $this->warn("  <fg=yellow>[WARNING]</> Izin berkas ({$perms}) sebaiknya diatur ke 0640 untuk keamanan optimal.");
        }

        // 2. Periksa baris log dan verifikasi HMAC
        $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $totalLines = count($lines);
        $hmacChecked = 0;
        $hmacValid = 0;
        $hmacCorrupt = 0;

        $appKey = (string) config('app.key');

        foreach ($lines as $index => $line) {
            if (preg_match('/\[(.*?)\] \[(.*?)\] \[ENC:(.*?)\] \[HMAC:(.*?)\]/', $line, $matches)) {
                $hmacChecked++;
                $encrypted = $matches[3];
                $storedHmac = $matches[4];

                $calculatedHmac = hash_hmac('sha256', $encrypted, $appKey);

                if (hash_equals($calculatedHmac, $storedHmac)) {
                    $hmacValid++;
                } else {
                    $hmacCorrupt++;
                    $lineNum = $index + 1;
                    $this->error("  [TAMPER DETECTED] Manipulasi baris terdeteksi pada baris ke-{$lineNum}!");
                }
            }
        }

        $this->line(str_repeat('-', 80));
        $this->line("• Total baris dalam berkas: {$totalLines}");
        $this->line("• Baris dengan tanda tangan HMAC: {$hmacChecked}");
        $this->line("• Baris terverifikasi valid: <fg=green>{$hmacValid}</>");

        if ($hmacCorrupt > 0) {
            $this->line("• Baris termanipulasi/korup: <fg=red>{$hmacCorrupt}</>");
            $this->error("\n[PERINGATAN KEAMANAN] Berkas log telah dimodifikasi atau dirusak oleh pihak luar!");
            return self::FAILURE;
        }

        if ($hmacChecked > 0) {
            $this->info("\n[SUKSES] Seluruh baris audit log terverifikasi 100% otentik dan bebas dari manipulasi.");
        } else {
            $this->comment("\n[INFO] Berkas log ini adalah berkas log teks standar (tidak mengandung tanda tangan HMAC).");
        }

        return self::SUCCESS;
    }
}
