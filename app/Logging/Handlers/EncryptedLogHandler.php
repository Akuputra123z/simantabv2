<?php

namespace App\Logging\Handlers;

use Illuminate\Support\Facades\Crypt;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class EncryptedLogHandler extends AbstractProcessingHandler
{
    protected string $filePath;

    public function __construct(string $filePath, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->filePath = $filePath;

        $dir = dirname($filePath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
    }

    protected function write(LogRecord $record): void
    {
        $payload = [
            'timestamp' => $record->datetime->format('Y-m-d H:i:s.u P'),
            'channel' => $record->channel,
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $record->context,
            'extra' => $record->extra,
        ];

        try {
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $encryptedPayload = Crypt::encryptString($jsonPayload);
            $hmac = hash_hmac('sha256', $encryptedPayload, (string) config('app.key'));

            $logLine = sprintf(
                "[%s] [%s] [ENC:%s] [HMAC:%s]\n",
                $record->datetime->format('Y-m-d H:i:s'),
                $record->level->getName(),
                $encryptedPayload,
                $hmac
            );
        } catch (\Throwable $e) {
            // Fallback aman jika enkripsi gagal
            $logLine = sprintf(
                "[%s] [ERROR] [ENC-FAIL: Encryption failed: %s]\n",
                $record->datetime->format('Y-m-d H:i:s'),
                $e->getMessage()
            );
        }

        // Tulis dengan flag LOCK_EX untuk concurrency safety
        file_put_contents($this->filePath, $logLine, FILE_APPEND | LOCK_EX);

        // Amankan izin berkas (Poin 5: 0640)
        @chmod($this->filePath, 0640);
    }
}
