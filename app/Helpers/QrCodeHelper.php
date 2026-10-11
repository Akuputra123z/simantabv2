<?php

namespace App\Helpers;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Illuminate\Support\Facades\Log;

class QrCodeHelper
{
    /**
     * Generate an endroid QrCode result instance
     *
     * @param string $text
     * @param int $width
     * @param int $margin
     * @param ErrorCorrectionLevel $errorCorrectionLevel
     * @return ResultInterface
     */
    public static function generate(
        string $text,
        int $width = 250,
        int $margin = 2,
        ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::Medium
    ): ResultInterface {
        $qrCode = new QrCode(
            data: $text,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $errorCorrectionLevel,
            size: $width,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();

        return $writer->write($qrCode);
    }

    /**
     * Generate a base64 PNG data URL (data:image/png;base64,...)
     * Fully backwards-compatible with existing callers.
     *
     * @param string $text
     * @param int $width
     * @param int $margin
     * @return string|null
     */
    public static function generateDataUrl(string $text, int $width = 250, int $margin = 2): ?string
    {
        try {
            $result = self::generate($text, $width, $margin);

            return $result->getDataUri();
        } catch (\Throwable $e) {
            Log::warning('QrCodeHelper generateDataUrl error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Generate raw binary PNG content
     *
     * @param string $text
     * @param int $width
     * @param int $margin
     * @return string|null
     */
    public static function generatePng(string $text, int $width = 250, int $margin = 2): ?string
    {
        try {
            $result = self::generate($text, $width, $margin);

            return $result->getString();
        } catch (\Throwable $e) {
            Log::warning('QrCodeHelper generatePng error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Generate base64 string without data:image/png;base64, prefix
     *
     * @param string $text
     * @param int $width
     * @param int $margin
     * @return string|null
     */
    public static function generateBase64(string $text, int $width = 250, int $margin = 2): ?string
    {
        try {
            $result = self::generate($text, $width, $margin);

            return base64_encode($result->getString());
        } catch (\Throwable $e) {
            Log::warning('QrCodeHelper generateBase64 error: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Save QR Code directly to a filesystem path
     *
     * @param string $text
     * @param string $filePath
     * @param int $width
     * @param int $margin
     * @return bool
     */
    public static function saveToFile(string $text, string $filePath, int $width = 250, int $margin = 2): bool
    {
        try {
            $result = self::generate($text, $width, $margin);
            $result->saveToFile($filePath);

            return true;
        } catch (\Throwable $e) {
            Log::warning('QrCodeHelper saveToFile error: ' . $e->getMessage());

            return false;
        }
    }
}
