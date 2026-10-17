<?php

namespace App\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class MaskSensitiveDataProcessor implements ProcessorInterface
{
    /**
     * Kunci-kunci sensitif yang wajib dimasking dari log.
     */
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'old_password',
        'token',
        'remember_token',
        'activation_token',
        'access_token',
        'refresh_token',
        'secret',
        'api_key',
        'app_key',
        'authorization',
        'cookie',
        'signature',
        'credit_card',
        'cvv',
        'pin',
    ];

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->maskArray($record->context);
        $extra = $this->maskArray($record->extra);

        return $record->with(context: $context, extra: $extra);
    }

    /**
     * Masking data sensitif secara rekursif pada array.
     */
    public function maskArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->maskArray($value);
            } elseif (is_string($key) && $this->isSensitiveKey($key)) {
                $data[$key] = '******** [REDACTED]';
            } elseif (is_string($value) && $this->containsSensitiveData($key, $value)) {
                $data[$key] = '******** [REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Periksa apakah key termasuk kategori data sensitif.
     */
    protected function isSensitiveKey(string $key): bool
    {
        $normalizedKey = strtolower(str_replace(['-', '_'], '', $key));

        foreach ($this->sensitiveKeys as $sensitive) {
            $normalizedSensitive = strtolower(str_replace(['-', '_'], '', $sensitive));
            if ($normalizedKey === $normalizedSensitive || str_contains($normalizedKey, $normalizedSensitive)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deteksi nilai berformat token bearer atau kunci otentikasi.
     */
    protected function containsSensitiveData(string $key, string $value): bool
    {
        if (str_starts_with(strtolower($value), 'bearer ')) {
            return true;
        }

        return false;
    }
}
