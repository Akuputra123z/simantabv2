<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = file_get_contents(storage_path('test_lhr.html'));
$parts = preg_split('/<section class=[\"\']page/i', $html);
echo "Total sections found: " . (count($parts) - 1) . "\n";

for ($i = 1; $i < count($parts); $i++) {
    $sec = $parts[$i];
    $pos = strpos($sec, '</section>');
    if ($pos !== false) {
        $sec = substr($sec, 0, $pos);
    }
    
    $fullHtml = '<!DOCTYPE html><html><head><style>
        @page { size: 210mm 297mm; margin: 15mm 15mm 15mm 20mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; line-height: 1.35; margin:0; padding:0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 8.5pt; }
        table.bordered, table.bordered th, table.bordered td { border: 1px solid #000; }
        table th, table td { padding: 3px 5px; vertical-align: top; }
        table th { background-color: #f2f2f2; text-align: center; }
        .page-footer { margin-top: 15px; padding-top: 4px; border-top: 1px solid #ccc; font-size: 8pt; font-style: italic; }
        .footer-table { width: 100%; border-collapse: collapse; font-size: 8pt; font-style: italic; }
        .footer-table td { padding: 0; border: none; }
        .header-kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 4px; margin-bottom: 10px; }
        .header-kop h3 { margin: 0; font-size: 11pt; }
        .header-kop h2 { margin: 0; font-size: 13pt; }
        .header-kop p { margin: 1px 0; font-size: 8pt; }
        .cover-logo { width: 100px; margin: 0 auto 10px; display: block; }
        .cover-header { font-weight: bold; font-size: 13pt; }
        .cover-title { font-size: 13.5pt; font-weight: bold; margin: 20px 0 15px; line-height: 1.3; }
        .cover-meta { text-align: left; margin: 20px auto; width: 85%; font-weight: bold; font-size: 10pt; }
        .surat-meta td { padding: 1px 4px; font-size: 9pt; }
        ol, ul { margin-top: 0; margin-bottom: 5px; padding-left: 18px; }
        li { margin-bottom: 3px; text-align: justify; }
        p { margin-top: 4px; margin-bottom: 4px; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
    </style></head><body><section class="page' . $sec . '</section></body></html>';

    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML($fullHtml)
        ->setPaper('a4', 'portrait')
        ->setWarnings(false);
    $d = $pdf->getDomPDF();
    $d->render();
    $pCount = $d->getCanvas()->get_page_count();
    if ($pCount > 1) {
        echo ">>> SECTION $i OVERFLOWS: $pCount pages! <<<\n";
    } else {
        echo "Section $i: 1 page\n";
    }
}
