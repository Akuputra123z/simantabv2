<?php

namespace Tests\Unit;

use App\Helpers\QrCodeHelper;
use Tests\TestCase;

class QrCodeHelperTest extends TestCase
{
    public function test_can_generate_valid_data_url(): void
    {
        $payload = 'https://simantab.rembangkab.go.id/audit-assignment/1/preview';
        $dataUrl = QrCodeHelper::generateDataUrl($payload, 250, 2);

        $this->assertNotNull($dataUrl);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUrl);

        // Verify base64 decode produces valid PNG magic header
        $base64Data = substr($dataUrl, strlen('data:image/png;base64,'));
        $binary = base64_decode($base64Data);

        $this->assertNotEmpty($binary);
        // PNG header is \x89PNG\r\n\x1a\n
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $binary);
    }

    public function test_can_generate_raw_png_and_base64(): void
    {
        $payload = 'TEST-QR-PAYLOAD-12345';

        $png = QrCodeHelper::generatePng($payload, 200, 2);
        $this->assertNotNull($png);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);

        $base64 = QrCodeHelper::generateBase64($payload, 200, 2);
        $this->assertNotNull($base64);
        $this->assertSame(base64_encode($png), $base64);
    }

    public function test_can_save_qr_code_to_file(): void
    {
        $payload = 'SPT/REVIU/2026/001';
        $tempFile = tempnam(sys_get_temp_dir(), 'qr_test_') . '.png';

        try {
            $success = QrCodeHelper::saveToFile($payload, $tempFile, 200, 2);
            $this->assertTrue($success);
            $this->assertFileExists($tempFile);
            $this->assertGreaterThan(100, filesize($tempFile));

            $content = file_get_contents($tempFile);
            $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $content);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
