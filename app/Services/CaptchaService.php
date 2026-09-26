<?php

namespace App\Services;

class CaptchaService
{
    public const SESSION_KEY = 'login_captcha_code';
    public const EXPIRES_KEY = 'login_captcha_expires_at';
    public const CHARSET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * Generate captcha code, save to session with TTL, and return Base64 Data URI.
     */
    public function generate(int $length = 5): array
    {
        $code = '';
        $charsetLen = strlen(self::CHARSET);
        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHARSET[random_int(0, $charsetLen - 1)];
        }

        session([
            self::SESSION_KEY => $code,
            self::EXPIRES_KEY => now()->addMinutes(5)->timestamp,
        ]);

        $width = 140;
        $height = 42;

        $image = imagecreatetruecolor($width, $height);

        // Background color (light slate #F1F5F9)
        $bgColor = imagecolorallocate($image, 241, 245, 249);
        imagefill($image, 0, 0, $bgColor);

        // Subtle noise lines
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate(
                $image,
                random_int(180, 220),
                random_int(190, 225),
                random_int(210, 240)
            );
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
        }

        // Noise dots
        for ($i = 0; $i < 80; $i++) {
            $dotColor = imagecolorallocate(
                $image,
                random_int(150, 210),
                random_int(160, 215),
                random_int(180, 235)
            );
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $dotColor);
        }

        // Render characters with built-in GD font 5
        $spacing = (int) (($width - 24) / $length);
        $font = 5;

        for ($i = 0; $i < $length; $i++) {
            $charColor = imagecolorallocate(
                $image,
                random_int(20, 60),
                random_int(60, 110),
                random_int(140, 190)
            );

            $x = 16 + ($i * $spacing) + random_int(-2, 2);
            $y = 13 + random_int(-3, 3);

            // Draw character with a 1px bold shadow effect
            imagestring($image, $font, $x, $y, $code[$i], $charColor);
            imagestring($image, $font, $x + 1, $y, $code[$i], $charColor);
        }

        // Frame border
        $borderColor = imagecolorallocate($image, 226, 232, 240);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return [
            'image' => 'data:image/png;base64,' . base64_encode($imageData),
        ];
    }

    /**
     * Validate user input against session code (self-destructing on first attempt).
     */
    public function validate(?string $input): bool
    {
        $sessionCode = session(self::SESSION_KEY);
        $expiresAt = session(self::EXPIRES_KEY);

        // Anti-replay protection: delete session code immediately
        session()->forget([self::SESSION_KEY, self::EXPIRES_KEY]);

        if (empty($input) || empty($sessionCode) || empty($expiresAt)) {
            return false;
        }

        if (now()->timestamp > $expiresAt) {
            return false;
        }

        return hash_equals(strtoupper($sessionCode), strtoupper(trim($input)));
    }
}
