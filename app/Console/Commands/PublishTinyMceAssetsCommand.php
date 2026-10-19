<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishTinyMceAssetsCommand extends Command
{
    protected $signature = 'tinymce:publish {--force : Overwrite existing public assets}';

    protected $description = 'Publikasikan asset TinyMCE dari vendor atau node_modules ke direktori public/tinymce';

    public function handle(): int
    {
        $this->info('Memulai sinkronisasi asset TinyMCE...');

        $vendorSource = base_path('vendor/tinymce/tinymce');
        $nodeSource = base_path('node_modules/tinymce');
        $targetDir = public_path('tinymce');

        $source = null;
        if (File::exists($vendorSource . '/tinymce.min.js')) {
            $source = $vendorSource;
            $this->line("Sumber asset: Vendor (Composer)");
        } elseif (File::exists($nodeSource . '/tinymce.min.js')) {
            $source = $nodeSource;
            $this->line("Sumber asset: node_modules (NPM)");
        } else {
            $this->error('Asset TinyMCE tidak ditemukan di vendor/tinymce/tinymce maupun node_modules/tinymce.');
            $this->line('Jalankan "composer require tinymce/tinymce" atau "npm install tinymce" terlebih dahulu.');
            return self::FAILURE;
        }

        // Baca versi dari package.json sumber jika ada
        $version = 'unknown';
        $pkgPath = $source . '/package.json';
        if (File::exists($pkgPath)) {
            $pkg = json_decode(File::get($pkgPath), true);
            $version = $pkg['version'] ?? 'unknown';
        }

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $items = ['tinymce.min.js', 'tinymce.js', 'models', 'plugins', 'skins', 'themes', 'icons', 'license.md', 'package.json'];

        foreach ($items as $item) {
            $srcItem = $source . '/' . $item;
            $dstItem = $targetDir . '/' . $item;

            if (!File::exists($srcItem)) {
                continue;
            }

            if (File::isDirectory($srcItem)) {
                File::copyDirectory($srcItem, $dstItem);
            } else {
                File::copy($srcItem, $dstItem);
            }
        }

        // Hapus file non-runtime seperti *.ts dan *.d.ts untuk menghemat ruang dan kerapian
        foreach (File::allFiles($targetDir) as $file) {
            if (in_array($file->getExtension(), ['ts', 'd.ts'])) {
                File::delete($file->getPathname());
            }
        }

        $this->info("✅ Berhasil mempublikasikan TinyMCE v{$version} ke {$targetDir}");
        return self::SUCCESS;
    }
}
