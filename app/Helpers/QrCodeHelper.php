<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class QrCodeHelper
{
    /**
     * Generate a base64 PNG data URL using npm qrcode package
     *
     * @param string $text
     * @param int $width
     * @param int $margin
     * @return string|null
     */
    public static function generateDataUrl(string $text, int $width = 250, int $margin = 2): ?string
    {
        try {
            $script = "import QRCode from 'qrcode'; QRCode.toDataURL(process.argv[1], { width: {$width}, margin: {$margin}, errorCorrectionLevel: 'M', color: { dark: '#000000', light: '#ffffff' } }).then(url => process.stdout.write(url));";
            $process = new Process(['node', '-e', $script, $text], base_path());
            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful()) {
                $output = trim($process->getOutput());
                if (str_starts_with($output, 'data:image/png;base64,')) {
                    return $output;
                }
            } else {
                Log::warning('QrCodeHelper node error: ' . $process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            Log::warning('QrCodeHelper exception: ' . $e->getMessage());
        }

        return null;
    }
}
