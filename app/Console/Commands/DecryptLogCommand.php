<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

class DecryptLogCommand extends Command
{
    protected $signature = 'log:decrypt 
                            {file? : Nama file log atau path relatif dari storage/logs} 
                            {--lines=50 : Jumlah baris terakhir yang ingin ditampilkan} 
                            {--filter= : Filter string pada pesan atau context}';

    protected $description = 'Mendekripsi dan membaca isi file log terenkripsi secara aman';

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
            $fileName = $this->choice('Pilih berkas log yang ingin dibaca/didekripsi:', $options, 0);
        }

        $fullPath = str_starts_with($fileName, '/') ? $fileName : $logDir . '/' . ltrim($fileName, '/');

        if (! file_exists($fullPath)) {
            $this->error("Berkas log tidak ditemukan: {$fullPath}");
            return self::FAILURE;
        }

        $this->info("Membaca berkas: {$fullPath}");
        $this->line(str_repeat('-', 80));

        $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            $this->comment('Berkas log kosong.');
            return self::SUCCESS;
        }

        $maxLines = (int) $this->option('lines');
        $filter = $this->option('filter');
        $selectedLines = array_slice($lines, -$maxLines);

        $count = 0;
        foreach ($selectedLines as $line) {
            if (preg_match('/\[(.*?)\] \[(.*?)\] \[ENC:(.*?)\](?: \[HMAC:(.*?)\])?/', $line, $matches)) {
                $time = $matches[1];
                $level = $matches[2];
                $encrypted = $matches[3];
                $hmac = $matches[4] ?? null;

                // Verifikasi integritas HMAC
                $tamperStatus = '';
                if ($hmac) {
                    $expectedHmac = hash_hmac('sha256', $encrypted, (string) config('app.key'));
                    if (hash_equals($expectedHmac, $hmac)) {
                        $tamperStatus = '<fg=green>[HMAC-VERIFIED]</>';
                    } else {
                        $tamperStatus = '<fg=red>[TAMPERED/CORRUPT]</>';
                    }
                }

                try {
                    $decryptedJson = Crypt::decryptString($encrypted);
                    $data = json_decode($decryptedJson, true);

                    $msg = $data['message'] ?? '-';
                    $context = ! empty($data['context']) ? json_encode($data['context'], JSON_UNESCAPED_SLASHES) : '';

                    if ($filter && ! str_contains(strtolower($msg . ' ' . $context), strtolower($filter))) {
                        continue;
                    }

                    $this->line(sprintf("<fg=gray>[%s]</> <fg=yellow>[%s]</> %s <fg=cyan>%s</> <fg=gray>%s</>", $time, $level, $tamperStatus, $msg, $context));
                    $count++;
                } catch (\Throwable $e) {
                    $this->line("<fg=red>[DECRYPT-ERROR]</> {$tamperStatus} Gagal mendekripsi baris pada {$time}: " . $e->getMessage());
                }
            } else {
                // Baris teks biasa
                if (! $filter || str_contains(strtolower($line), strtolower($filter))) {
                    $this->line($line);
                    $count++;
                }
            }
        }

        $this->line(str_repeat('-', 80));
        $this->info("Menampilkan {$count} baris log.");

        return self::SUCCESS;
    }
}
