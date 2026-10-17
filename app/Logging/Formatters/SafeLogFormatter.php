<?php

namespace App\Logging\Formatters;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class SafeLogFormatter extends LineFormatter
{
    public const DEFAULT_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    public function __construct(?string $format = null, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = true)
    {
        // Default allowInlineLineBreaks false untuk mencegah log injection via multiline payloads
        parent::__construct($format ?? self::DEFAULT_FORMAT, $dateFormat ?? 'Y-m-d H:i:s.u', $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(LogRecord $record): string
    {
        // Sanitasi pesan dari potensi injeksi CRLF (\r\n) buatan penyerang
        $sanitizedMessage = $this->sanitizeForLog($record->message);

        $record = $record->with(message: $sanitizedMessage);

        return parent::format($record);
    }

    /**
     * Netralisir karakter carriage return & newline yang tidak disengaja dalam satu baris pesan.
     */
    public function sanitizeForLog(string $message): string
    {
        // Ganti newline mentah menjadi escape literal \n agar tidak memalsukan baris log baru
        return str_replace(["\r\n", "\r", "\n"], ['\n', '\n', '\n'], $message);
    }
}
