<?php

namespace App\Actions\Lhr;

use App\Models\Lhr\Lhr;
use App\Services\Lhr\LhrDocumentBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateLhrPdf
{
    public function __construct(
        protected LhrDocumentBuilder $builder
    ) {}

    /**
     * Generate PDF dokumen LHR, simpan di storage dan rekam hash SHA-256.
     *
     * @return array{path: string, sha256: string, binary: string}
     */
    public function execute(Lhr $lhr, bool $saveToDisk = true): array
    {
        $data = $this->builder->build($lhr);
        $data['isPdf'] = true;

        $view = match ($lhr->jenisDokumen?->kode) {
            'RKPD', 'KUA_PPAS' => 'lhr.pdf.rkpd_kua',
            default => 'lhr.pdf.shsd',
        };

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultMediaType' => 'print',
            ]);

        $binary = $pdf->output();
        $sha256 = hash('sha256', $binary);

        $filePath = "private/lhr/{$lhr->uuid}/v{$lhr->versi}.pdf";

        if ($saveToDisk) {
            Storage::put($filePath, $binary);

            $lhr->update([
                'dokumen_final_path' => $filePath,
                'dokumen_final_sha256' => $sha256,
            ]);
        }

        return [
            'path' => $filePath,
            'sha256' => $sha256,
            'binary' => $binary,
        ];
    }
}
