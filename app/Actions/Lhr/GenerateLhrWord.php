<?php

namespace App\Actions\Lhr;

use App\Models\Lhr\Lhr;
use App\Services\Lhr\LhrDocumentBuilder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class GenerateLhrWord
{
    public function __construct(
        protected LhrDocumentBuilder $builder
    ) {}

    /**
     * Generate Word (.docx) dokumen LHR lengkap mengacu pada template resmi Rembang.
     *
     * @return array{path: string, binary: string}
     */
    public function execute(Lhr $lhr, bool $saveToDisk = false): array
    {
        // Enforce auto-escaping XML agar simbol &, <, > tidak menyebabkan corrupt OpenXML
        Settings::setOutputEscapingEnabled(true);

        $data = $this->builder->build($lhr);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        // Siapkan variabel data terikat database & fallback
        $nomorLhr = $lhr->nomor_lhr ?: '700/026/REVIU/INSP/2026';
        $tanggalLhr = $lhr->tanggal_lhr 
            ? Carbon::parse($lhr->tanggal_lhr)->locale('id')->isoFormat('D MMMM Y') 
            : '11 Mei 2026';
        $tahunAnggaran = $lhr->tahun_anggaran ?: 2027;
        $tahunTerbit = $lhr->tanggal_lhr ? Carbon::parse($lhr->tanggal_lhr)->format('Y') : '2026';
        $namaUnit = $data['unit']->nama_unit ?? 'BADAN PERENCANAAN PEMBANGUNAN DAERAH KABUPATEN REMBANG';
        $assignment = $data['assignment'];
        $nomorSpt = $assignment->nomor_surat ?? '000.1.2.3/020/INSP/2026';
        $tanggalSpt = $assignment?->tanggal_mulai 
            ? Carbon::parse($assignment->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y') 
            : '29 April 2026';
        $nomorPermohonan = $lhr->permohonan_nomor ?: '000.7/250/2026';
        $tanggalPermohonan = $lhr->permohonan_tanggal 
            ? Carbon::parse($lhr->permohonan_tanggal)->locale('id')->isoFormat('D MMMM Y') 
            : '28 April 2026';
        $team = $data['team'];

        /** @var Collection $pengujians */
        $pengujians = ($data['pengujians'] ?? $lhr->pengujians)->keyBy('kode');

        $getSimpulan = function(string $kode, string $fallback) use ($pengujians): string {
            $val = $pengujians->get($kode)?->teks_simpulan;
            return !empty(trim($val ?? '')) ? $val : $fallback;
        };

        $getCatatans = function(string $kode) use ($pengujians): Collection {
            $p = $pengujians->get($kode);
            if (!$p) return collect();
            $cats = $p->catatans ?? collect();
            if ($p->relationLoaded('children') && $p->children && $p->children->isNotEmpty()) {
                foreach ($p->children as $child) {
                    $cats = $cats->concat($child->catatans ?? collect());
                }
            }
            return $cats;
        };

        // ══════════════════════════════════════════════════════════════════════════
        // 1. HALAMAN COVER (SECTION 1)
        // ══════════════════════════════════════════════════════════════════════════
        $coverSection = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1700,
            'marginBottom' => 1417,
            'marginLeft' => 1417,
            'marginRight' => 1417,
        ]);

        $coverSection->addText('KABUPATEN REMBANG', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $coverSection->addText('INSPEKTORAT DAERAH KABUPATEN REMBANG', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

        $logoPath = public_path('images/logos/rembang.png');
        if (file_exists($logoPath)) {
            $coverSection->addImage($logoPath, [
                'width' => 90,
                'height' => 105,
                'alignment' => Jc::CENTER,
                'spaceAfter' => 60,
            ]);
        }

        $coverSection->addText('BerAKHLAK', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $coverSection->addText('bangga melayani bangsa', ['size' => 8.5], ['alignment' => Jc::CENTER, 'spaceAfter' => 240]);

        $coverSection->addText('LAPORAN HASIL REVIU', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
        $coverSection->addText('DOKUMEN RANCANGAN AKHIR', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
        $coverSection->addText("RENCANA KERJA PEMERINTAH DAERAH (RKPD) KABUPATEN REMBANG", ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
        $coverSection->addText("TAHUN {$tahunAnggaran}", ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);
        $coverSection->addText(strtoupper($namaUnit), ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER, 'spaceAfter' => 300]);

        // Box Nomor & Tanggal di Cover
        $metaCoverTable = $coverSection->addTable([
            'borderSize' => 6,
            'borderColor' => 'CCCCCC',
            'cellMargin' => 80,
            'alignment' => Jc::CENTER,
            'width' => 80 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $metaCoverTable->addRow();
        $metaCoverTable->addCell(3000)->addText('Nomor', ['bold' => true, 'size' => 10]);
        $metaCoverTable->addCell(7000)->addText(": {$nomorLhr}", ['bold' => true, 'size' => 10]);
        $metaCoverTable->addRow();
        $metaCoverTable->addCell(3000)->addText('Tanggal', ['bold' => true, 'size' => 10]);
        $metaCoverTable->addCell(7000)->addText(": {$tanggalLhr}", ['bold' => true, 'size' => 10]);

        $coverSection->addTextBreak(2);
        $coverSection->addText((string)$tahunTerbit, ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);

        // ══════════════════════════════════════════════════════════════════════════
        // 2. MAIN DOCUMENT SECTION (DENGAN HEADER / FOOTER RESMI)
        // ══════════════════════════════════════════════════════════════════════════
        $mainSection = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1134,    // 20 mm
            'marginBottom' => 1134, // 20 mm
            'marginLeft' => 1417,   // 25 mm
            'marginRight' => 1134,  // 20 mm
        ]);

        // Running Footer
        $footer = $mainSection->addFooter();
        $footerTable = $footer->addTable(['width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        $footerTable->addRow();
        $footerTable->addCell(8000)->addText("Reviu Dokumen Rankir RKPD Kabupaten Rembang Tahun {$tahunAnggaran}", ['italic' => true, 'size' => 8.5, 'color' => '555555']);
        $footerTable->addCell(2000, ['alignment' => Jc::RIGHT])->addPreserveText('{PAGE}', ['italic' => true, 'size' => 8.5, 'color' => '555555'], ['alignment' => Jc::RIGHT]);

        // ── HALAMAN 2: SURAT PENGANTAR ────────────────────────────────────────────
        $this->addHeaderKop($mainSection);

        $suratMetaTable = $mainSection->addTable(['width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        $suratMetaTable->addRow();
        $c1 = $suratMetaTable->addCell(1200); $c1->addText('Nomor', null, ['spaceAfter' => 20]);
        $c2 = $suratMetaTable->addCell(200); $c2->addText(':', null, ['spaceAfter' => 20]);
        $c3 = $suratMetaTable->addCell(4600); $c3->addText($nomorLhr, null, ['spaceAfter' => 20]);
        $c4 = $suratMetaTable->addCell(4000); $c4->addText("Rembang, {$tanggalLhr}", null, ['spaceAfter' => 20]);

        $suratMetaTable->addRow();
        $suratMetaTable->addCell(1200)->addText('Lampiran', null, ['spaceAfter' => 20]);
        $suratMetaTable->addCell(200)->addText(':', null, ['spaceAfter' => 20]);
        $suratMetaTable->addCell(4600)->addText('-', null, ['spaceAfter' => 20]);
        $suratMetaTable->addCell(4000)->addText('', null, ['spaceAfter' => 20]);

        $suratMetaTable->addRow();
        $suratMetaTable->addCell(1200)->addText('Perihal', null, ['spaceAfter' => 20]);
        $suratMetaTable->addCell(200)->addText(':', null, ['spaceAfter' => 20]);
        $suratMetaTable->addCell(4600)->addText("Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran}", null, ['spaceAfter' => 20]);
        $cKepada = $suratMetaTable->addCell(4000);
        $cKepada->addText("Kepada Yth.", null, ['spaceAfter' => 10]);
        $cKepada->addText("Kepala {$namaUnit}", ['bold' => true], ['spaceAfter' => 10]);
        $cKepada->addText("di - REMBANG", null, ['spaceAfter' => 20]);

        $mainSection->addTextBreak(1);
        $mainSection->addText(
            "Inspektorat Daerah Kabupaten Rembang sebagai Aparat Pengawasan Intern Pemerintah telah melakukan reviu atas Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} berupa pengujian terbatas terhadap substansi yang termuat pada Rancangan Akhir Rencana Kerja Pemerintah Daerah. Reviu ditujukan untuk membantu terlaksananya penyusunan dan penyajikan dokumen Rencana Kerja Pemerintah Daerah (RKPD) serta memberikan keyakinan terbatas mengenai kepatuhan dan keabsahan informasi yang disajikan telah sesuai dengan kaidah-kaidah perencanaan, sehingga dapat menghasilkan dokumen Rencana Kerja Pemerintah Daerah (RKPD) yang berkualitas.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 80, 'lineHeight' => 1.25]
        );
        $mainSection->addText(
            "Dalam pelaksanaan reviu, kami telah melakukan serangkaian aktivitas untuk menguji proses penyusunan atas penyajian dan substansi dalam dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Tahun {$tahunAnggaran} dengan dokumen pendukung lainnya, permintaan keterangan mengenai proses penyusunan untuk mengetahui hubungan dan hal-hal yang tidak terdapat dalam dokumen penyajian. Bersama ini kami sampaikan Laporan Hasil Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran}. Uraian rinci atas pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} adalah sebagai berikut:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.25]
        );

        $mainSection->addPageBreak();

        // ── HALAMAN 3-4: A. PERNYATAAN REVIU & B. DASAR HUKUM & TIM ───────────────
        $mainSection->addText('A. Pernyataan Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $mainSection->addText(
            "Kami telah mereviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} dengan mempedomani Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah. Semua informasi yang dimuat dalam laporan adalah penyajian manajemen Badan Perencanaan Pembangunan Daerah Kabupaten Rembang dan Aplikasi Sistem Informasi Pemerintahan Daerah Kementerian Dalam Negeri Republik Indonesia.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 80, 'lineHeight' => 1.2]
        );
        $mainSection->addText(
            "Reviu dilaksanakan sesuai dengan Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah, yaitu tertera dalam:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60]
        );

        $pasalTable = $mainSection->addTable(['width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        $pasalTable->addRow();
        $pasalTable->addCell(2200)->addText('Pasal 2 ayat (1)', ['bold' => true]);
        $pasalTable->addCell(7800)->addText(': APIP Daerah melaksanakan Reviu terhadap: a. dokumen Perencanaan Pembangunan Daerah; dan b. dokumen Keuangan Daerah;', null, ['alignment' => Jc::BOTH]);
        $pasalTable->addRow();
        $pasalTable->addCell(2200)->addText('Pasal 3 ayat (1)', ['bold' => true]);
        $pasalTable->addCell(7800)->addText(': Dokumen Perencanaan Pembangunan Daerah sebagaimana dimaksud dalam Pasal 2 ayat (1) huruf a, terdiri atas: a. rancangan akhir RPJMD; b. rancangan akhir Renstra Perangkat Daerah; dan c. rancangan akhir RKPD.', null, ['alignment' => Jc::BOTH]);
        $pasalTable->addRow();
        $pasalTable->addCell(2200)->addText('Pasal 8', ['bold' => true]);
        $pasalTable->addCell(7800)->addText(': Ketentuan lebih lanjut mengenai pedoman Reviu dokumen Perencanaan Pembangunan Daerah dan dokumen Keuangan Daerah tercantum dalam Lampiran yang merupakan bagian tidak terpisahkan dari Peraturan Menteri ini.', null, ['alignment' => Jc::BOTH]);

        $mainSection->addTextBreak(1);
        $mainSection->addText(
            'Reviu mempunyai lingkup yang jauh lebih sempit dibandingkan dengan lingkup audit yang dilakukan sesuai dengan peraturan terkait dengan tujuan audit.',
            ['italic' => true],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
        );

        // B. Dasar Hukum Reviu
        $mainSection->addText('B. Dasar Hukum Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $dasarHukums = [
            'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, dan Rencana Kerja Pemerintah Daerah;',
            'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 10 Tahun 2018 tentang Reviu atas Dokumen Perencanaan Pembangunan dan Anggaran Daerah Tahunan;',
            'Peraturan Menteri Dalam Negeri Nomor 14 Tahun 2025 tentang Pedoman Penyusunan Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;',
            'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 3 Tahun 2026 tentang Reviu Dokumen Perencanaan Pembangunan Daerah dan Dokumen Keuangan Daerah;',
            'Peraturan Daerah Kabupaten Rembang Nomor 6 Tahun 2021 tentang Perubahan Kedua atas Peraturan Daerah Kabupaten Rembang Nomor 5 Tahun 2016 tentang Pembentukan dan Susunan Perangkat daerah Kabupaten Rembang;',
            'Peraturan Bupati Rembang Nomor 54 Tahun 2021 tentang Kedudukan Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Inspektorat Kabupaten Rembang;',
            'Peraturan Bupati Rembang Nomor 4 Tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 Tahun 2025 tentang Penjabaran APBD Tahun Anggaran 2026;',
            'Surat dari Inspektorat Jenderal Kementerian Dalam Negeri Republik Indonesia Nomor: 700.1.1.1/1642/IJ perihal Reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah Tahun Anggaran 2027;',
            'Keputusan Bupati Rembang Nomor: 100.3.3.2/0282/2026 tentang Program Kerja Pengawasan Tahunan Berbasis Risiko Inspektorat Daerah Kabupaten Rembang Tahun 2026;',
            "Surat dari Kepala Badan Perencanaan Pembangunan Daerah Kabupaten Rembang Nomor: {$nomorPermohonan} tanggal {$tanggalPermohonan} tentang Permohonan Reviu Dokumen Rancangan Akhir RKPD Kabupaten Rembang Tahun {$tahunAnggaran};",
            "Surat Perintah Tugas Inspektur Daerah Kabupaten Rembang Nomor: {$nomorSpt} tanggal {$tanggalSpt} dengan susunan Tim:",
        ];

        foreach ($dasarHukums as $i => $dh) {
            $mainSection->addText(($i + 1) . '. ' . $dh, null, ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 280]);
        }

        $mainSection->addTextBreak(1);

        // Susunan Tim Reviu
        $mainSection->addText('➤ PENANGGUNGJAWAB', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 280]);
        $this->addTeamTable($mainSection, [
            ['Nama', ': ' . ($lhr->signer?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.')],
            ['NIP', ': ' . ($lhr->signer?->nip ?? '19741128 199903 2 003')],
            ['Jabatan', ': Inspektur Daerah'],
        ]);

        $mainSection->addText('➤ PENGENDALI TEKNIS', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 280]);
        $this->addTeamTable($mainSection, [
            ['Nama', ': ' . ($team['pengendali_teknis']['nama'] !== '-' ? $team['pengendali_teknis']['nama'] : 'SRI SURYANDARI, S.Sos.')],
            ['NIP', ': ' . ($team['pengendali_teknis']['nip'] !== '-' ? $team['pengendali_teknis']['nip'] : '19690827 199603 2 005')],
            ['Jabatan', ': ' . ($team['pengendali_teknis']['jabatan'] ?: 'Inspektur Pembantu I')],
        ]);

        $mainSection->addText('➤ KETUA TIM', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 280]);
        $this->addTeamTable($mainSection, [
            ['Nama', ': ' . ($team['ketua_tim']['nama'] !== '-' ? $team['ketua_tim']['nama'] : 'SITI AMINAH, S.E.')],
            ['NIP', ': ' . ($team['ketua_tim']['nip'] !== '-' ? $team['ketua_tim']['nip'] : '19920714 201902 2 002')],
            ['Jabatan', ': ' . ($team['ketua_tim']['jabatan'] ?: 'Auditor Ahli Pertama')],
        ]);

        $mainSection->addText('➤ ANGGOTA TIM', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 280]);
        if (!empty($team['anggota']) && count($team['anggota']) > 0) {
            $agtRows = [];
            foreach ($team['anggota'] as $agt) {
                $agtRows[] = ['Nama', ': ' . $agt['nama']];
                $agtRows[] = ['NIP', ': ' . $agt['nip']];
                $agtRows[] = ['Jabatan', ': ' . $agt['jabatan']];
            }
            $this->addTeamTable($mainSection, $agtRows);
        } else {
            $defaultAnggotas = [
                ['Nama' => 'PARIJAN, S.E., M.M.', 'NIP' => '19680314 199103 1 014', 'Jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Madya'],
                ['Nama' => 'AGUNG TRI PRASTIYO, S.E.', 'NIP' => '19820821 200501 1 006', 'Jabatan' => 'Auditor Ahli Muda'],
                ['Nama' => 'IKA PERTIWI, S.H.', 'NIP' => '19860908 202203 2 002', 'Jabatan' => 'Auditor Ahli Pertama'],
                ['Nama' => 'TAUFIQUR RACHMAN, S.E.', 'NIP' => '19940326 202203 1 016', 'Jabatan' => 'Auditor Ahli Pertama'],
                ['Nama' => 'SIHONO, S.I.P.', 'NIP' => '19960703 202203 1 007', 'Jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama'],
                ['Nama' => 'WAHYU ANJAR, S.Tr.I.P', 'NIP' => '20000727 202409 2 001', 'Jabatan' => 'Pengawas Pemerintahan Urusan Pemerintah Daerah Ahli Pertama'],
                ['Nama' => 'DEWI ZAHRO WAHYUNINGTYAS, S.T.', 'NIP' => '20010524 202505 2 004', 'Jabatan' => 'Calon Auditor'],
            ];
            $agtRows = [];
            foreach ($defaultAnggotas as $agt) {
                $agtRows[] = ['Nama', ': ' . $agt['Nama']];
                $agtRows[] = ['NIP', ': ' . $agt['NIP']];
                $agtRows[] = ['Jabatan', ': ' . $agt['Jabatan']];
            }
            $this->addTeamTable($mainSection, $agtRows);
        }

        $mainSection->addPageBreak();

        // ── HALAMAN 5-6: C. TUJUAN, D. RUANG LINGKUP, E. OBJEK, F. PELAKSANAAN, G. METODOLOGI
        $mainSection->addText('C. Tujuan Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $mainSection->addText(
            'Tujuan Reviu rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) yaitu untuk memberikan keyakinan terbatas bahwa Rencana Kerja Pemerintah Daerah telah disusun berpedoman kepada RPJMD, Rencana Kerja Pemerintah, Program Strategis Nasional, pedoman penyusunan RKPD dan kaidah-kaidah perencanaan pembangunan lainnya.',
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 100, 'lineHeight' => 1.2]
        );

        $mainSection->addText('D. Ruang Lingkup Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $mainSection->addText(
            "Ruang lingkup reviu adalah pengujian atas penyusunan Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} yang bersifat tahunan berupa Dokumen RKPD Tahun {$tahunAnggaran}.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40]
        );
        $mainSection->addText('Ruang lingkup reviu mencakup pengujian terbatas atas:', null, ['spaceAfter' => 40]);
        $mainSection->addText('➤ FORMAT', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('Kesesuaian Format Rancangan Akhir RKPD.', null, ['spaceAfter' => 40, 'leftIndent' => 400]);

        $mainSection->addText('➤ SUBSTANSI', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $lingkupSubstansi = [
            'Pengujian atas Kelengkapan Dokumen Pendukung Rancangan Akhir RKPD;',
            'Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD;',
            'Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD;',
            'Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan Rancangan Akhir RKPD;',
            'Kewajaran proyeksi pendanaan pada rancangan akhir RKPD;',
            "Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional:\n  a. Kesesuaian urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil Rakortekbang dengan urusan pada rancangan akhir RKPD;\n  b. Kesesuaian penetapan target Indikator Makro Pembangunan pada rancangan akhir RKPD berdasarkan RKP;\n  c. Kesesuaian dan ketepatan dukungan pelaksanaan ProSN pada rancangan akhir RKPD;\n  d. Memastikan Program, Kegiatan dan Sub Kegiatan SPM tersedia pada rancangan akhir RKPD;\n  e. Memastikan kesesuaian dukungan Sub Kegiatan Tematik Pembangunan pada rancangan akhir RKPD;",
            'Kesesuaian Informasi pada rancangan akhir RKPD dengan tata cara dan kaidah Perencanaan Pembangunan Daerah lainnya (kesesuaian pengusulan pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) sesuai dengan ketentuan peraturan perundang-undangan);',
            'Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD.',
        ];
        foreach ($lingkupSubstansi as $idx => $ls) {
            $mainSection->addText(($idx + 1) . '. ' . $ls, null, ['alignment' => Jc::BOTH, 'spaceAfter' => 30, 'leftIndent' => 400]);
        }

        $mainSection->addTextBreak(1);
        $mainSection->addText('E. Objek Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
        $mainSection->addText("Objek reviu adalah Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran}.", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 80]);

        $mainSection->addText('F. Pelaksanaan Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
        $mainSection->addText("Pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahunAnggaran} dilaksanakan pada mulai tanggal {$tanggalSpt} sampai dengan selesai.", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 80]);

        $mainSection->addText('G. Metodologi Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
        $mainSection->addText("Reviu dilaksanakan dengan menggunakan metodologi mengumpulkan dan melakukan penelaahan atas Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahunAnggaran}, konfirmasi, analisis dan prosedur lainnya terkait proses penyusunan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahunAnggaran}.", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 100]);

        $mainSection->addPageBreak();

        // ── HALAMAN 7: H. GAMBARAN UMUM & I. URAIAN HASIL REVIU (SIMPULAN) ────────
        $mainSection->addText('H. Gambaran Umum', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $gambaranUmums = [
            'Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 3 Tahun 2024 tentang Rencana Pembangunan Jangka Panjang Daerah Kabupaten Rembang Tahun 2025-2045;',
            'Pemerintah Kabupaten Rembang telah menyusun Peraturan Daerah Nomor 7 tahun 2025 tentang Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang tahun 2025-2029 sebagai acuan dalam menentukan arah kebijakan pembangunan setiap tahunnya dengan membuat Rancangan Rencana Kerja Pemerintah Daerah Kabupaten Rembang;',
            "Sebagai penjabaran Rencana Pembangunan Jangka Menengah Daerah Kabupaten Rembang 2025-2029, kedudukan RKPD Kabupaten Rembang Tahun {$tahunAnggaran} dalam pelaksanaan pembangunan di Kabupaten Rembang adalah menjembatani antara perencanaan strategis jangka menengah dengan perencanaan dan penganggaran tahunan yang memuat rancangan kerangka ekonomi daerah, prioritas pembangunan daerah, rencana kerja dan pendanaan yang disusun berdasarkan pada orientasi proses dengan pendekatan teknokratik, partisipatif, politis dan top-down, bottom-up, serta berdasarkan pada orientasi substansi dengan pendekatan secara holistik, integratif, tematik dan spasial, yang selanjutnya sebagai pedoman dalam penyusunan Kebijakan Umum Anggaran serta penentuan Prioritas Plafon Anggaran Sementara yang menjadi dasar penyusunan Rancangan Anggaran Pendapatan dan Belanja Daerah (RAPBD) Tahun {$tahunAnggaran};",
            "Inspektorat Daerah Kabupaten Rembang berdasarkan kewenangannya melakukan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahunAnggaran}.",
        ];
        foreach ($gambaranUmums as $idx => $gu) {
            $mainSection->addText(($idx + 1) . '. ' . $gu, null, ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 280]);
        }

        $mainSection->addTextBreak(1);
        $mainSection->addText('I. Uraian Hasil Reviu', ['bold' => true, 'size' => 11], ['spaceAfter' => 40]);
        $mainSection->addText("Hasil pelaksanaan Reviu Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah Kabupaten Rembang Tahun {$tahunAnggaran} dapat dijelaskan sebagai berikut:", null, ['spaceAfter' => 40]);

        $mainSection->addText('1. Simpulan', ['bold' => true], ['spaceAfter' => 40]);

        // Simpulan a. Format
        $mainSection->addText('a. Format', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('Kesesuaian Format Rancangan Akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.F', "Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384)."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'leftIndent' => 200]
        );

        // Simpulan b. Substansi
        $mainSection->addText('b. Substansi', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        
        $mainSection->addText('1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S1', "Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} telah didukung dengan dokumen perencanaan yang memadai yaitu: a) RPJMN tahun 2025-2029; b) RPJMD Prov. Jateng tahun 2025-2029; c) Rancangan RKPD Prov. Jateng tahun {$tahunAnggaran}; d) RPJMD Kab. Rembang tahun 2025-2029; e) Renstra Perangkat Daerah tahun 2025-2029; f) Hasil Evaluasi RKPD tahun 2025; g) Berita Acara FKP RKPD; h) Berita Acara Musrenbang RKPD; i) SK Tim Penyusun RKPD."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('2) Pengujian atas Analisis Isi BAB Rancangan Akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S2', "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran}, masih terdapat kekurangsesuaian data yang disajikan."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('3) Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S3', "Rumusan Prioritas dan Sasaran Pembangunan dalam Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang Tahun {$tahunAnggaran} telah sesuai dengan Dokumen RPJMD Kabupaten Rembang tahun 2025-2029."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('4) Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rencana Pembangunan Jangka Menengah Daerah (RPJMD) dengan rancangan akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S4', "Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} belum sesuai dengan Rencana Pembangunan Jangka Menengah Daerah (RPJMD) Kabupaten Rembang tahun 2025-2029."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('5) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S5', "Proyeksi pendanaan pada Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} telah wajar."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('6) Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            'a) Kesesuaian urusan pendukung Asta Cita: ' . $getSimpulan('RKPD.S6.a', "Urusan yang mendukung pelaksanaan Asta Cita berdasarkan hasil Rakortekrenbang Nasional tahun 2026 pada Lampiran SIPD-RI belum selaras."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]
        );
        $mainSection->addText(
            'b) Kesesuaian target Indikator Makro RKP: ' . $getSimpulan('RKPD.S6.b', "Rencana Kerja Pemerintah (RKP) tahun {$tahunAnggaran} belum tersedia."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]
        );
        $mainSection->addText(
            'c) Kesesuaian pelaksanaan ProSN: ' . $getSimpulan('RKPD.S6.c', "Program Strategis Nasional (ProSN) belum seluruhnya terpetakan dalam Lampiran SIPD-RI Renja OPD."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]
        );
        $mainSection->addText(
            'd) Kesesuaian SPM: ' . $getSimpulan('RKPD.S6.d', "Subkegiatan Standar Pelayanan Minimal (SPM) belum seluruhnya terpetakan dalam Lampiran SIPD-RI."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]
        );
        $mainSection->addText(
            'e) Tematik Pembangunan: ' . $getSimpulan('RKPD.S6.e', "Subkegiatan Tematik Pembangunan belum seluruhnya terpetakan dalam Lampiran SIPD-RI Renja OPD."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 400]
        );

        $mainSection->addText('7) Kesesuaian Informasi dengan tata cara perencanaan lainnya (Pokir DPRD)', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S7', "Dokumen Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran} belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran Dewan Perwakilan Rakyat Daerah (DPRD) Kabupaten Rembang tahun {$tahunAnggaran}."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('8) Pengujian atas Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            $getSimpulan('RKPD.S8', "Target Kinerja Tujuan dan Sasaran dalam dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} belum konsisten dengan Dokumen RPJMD Kabupaten Rembang Tahun 2025-2029."),
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 80, 'leftIndent' => 200]
        );

        $mainSection->addPageBreak();

        // ── HALAMAN 8-11: 2. CATATAN REVIU ─────────────────────────────────────────
        $mainSection->addText('2. Catatan', ['bold' => true], ['spaceAfter' => 20]);
        $mainSection->addText("Berdasarkan reviu dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} terdapat catatan sebagai berikut:", null, ['spaceAfter' => 40]);

        $mainSection->addText('a. Format', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('Kesesuaian Format Rancangan Akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Format/sistematika dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} kurang sesuai dengan yang diatur dalam Lampiran Peraturan Menteri Dalam Negeri Nomor 86 tahun 2017 (halaman 384), sebagai berikut:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'leftIndent' => 200]
        );

        // Tabel Komparasi Format Permendagri 86 vs Rankir RKPD
        $this->addTableFormatPermendagri($mainSection, $tahunAnggaran);

        $mainSection->addPageBreak();

        // Catatan Substansi
        $mainSection->addText('b. Substansi', ['bold' => true], ['spaceAfter' => 30, 'leftIndent' => 200]);

        // 1) Kelengkapan Dokumen Pendukung
        $mainSection->addText('1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $catsS1 = $getCatatans('RKPD.S1');
        if ($catsS1->isNotEmpty()) {
            foreach ($catsS1 as $cat) {
                $this->appendHtmlOrTextToSection($mainSection, $cat->isi, 200);
            }
        } else {
            $mainSection->addText('Terdapat data/dokumen pendukung yang belum ada, yaitu:', null, ['spaceAfter' => 20, 'leftIndent' => 200]);
            $mainSection->addText('a. Hasil Evaluasi RKPD tahun 2026 (Triwulan 1);', null, ['spaceAfter' => 15, 'leftIndent' => 400]);
            $mainSection->addText('b. Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35 Pengendalian dan Evaluasi terhadap Kebijakan Perencanaan Pembangunan Tahunan Daerah (Cek List: 27 komponen);', null, ['spaceAfter' => 15, 'leftIndent' => 400]);
            $mainSection->addText("c. Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun {$tahunAnggaran}; dan", null, ['spaceAfter' => 15, 'leftIndent' => 400]);
            $mainSection->addText("d. Rekapitulasi Pokok-pokok Pikiran DPRD tahun {$tahunAnggaran}.", null, ['spaceAfter' => 40, 'leftIndent' => 400]);
        }

        // 2) Analisis Isi BAB
        $mainSection->addText('2) Pengujian atas Analisis Isi BAB RKPD', ['bold' => true], ['spaceAfter' => 30, 'leftIndent' => 200]);
        $catsS2 = $getCatatans('RKPD.S2');
        if ($catsS2->isNotEmpty()) {
            $tabelCatBab = $mainSection->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMargin' => 60,
                'alignment' => Jc::CENTER,
            ]);
            $tabelCatBab->addRow(300, ['tblHeader' => true, 'cantSplit' => true]);
            $tabelCatBab->addCell(800, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('NO', ['bold' => true, 'size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $tabelCatBab->addCell(8600, ['bgColor' => 'F2F2F2'])->addText('CATATAN', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);

            foreach ($catsS2 as $idx => $cat) {
                $tabelCatBab->addRow(null, ['cantSplit' => true]);
                $tabelCatBab->addCell(800)->addText((string)($idx + 1) . '.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
                $cCell = $tabelCatBab->addCell(8600);
                if ($cat->referensi_bagian) {
                    $cCell->addText($cat->referensi_bagian, ['bold' => true, 'size' => 9.5, 'name' => 'Arial'], ['spaceAfter' => 20]);
                }
                $this->appendHtmlOrText($cCell, $cat->isi);
            }
        } else {
            $this->addTableAnalisisIsiBabCatatan($mainSection, $tahunAnggaran);
        }

        $mainSection->addTextBreak(1);

        // 3) Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan
        $mainSection->addText('3) Pengujian atas Kesesuaian Rumusan Prioritas dan Sasaran Pembangunan dalam RKPD dan RPJMD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Rumusan prioritas pembangunan daerah pada Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} (hal IV-16) telah selaras dengan prioritas pembangunan daerah yang telah ditetapkan pada RPJMD Kabupaten Rembang tahun 2025-2029 (hal III-143) sebagaimana penyandingan berikut:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 200]
        );
        $this->addTablePrioritasPembangunan($mainSection, $tahunAnggaran);

        $mainSection->addTextBreak(1);

        // 4) Kesesuaian Program/Outcome, Indikator, Target beserta Pagu
        $mainSection->addText('4) Kesesuaian Program/Outcome, Indikator, Target beserta Pagu Indikatifnya pada RPJMD dengan RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $catsS4 = $getCatatans('RKPD.S4');
        if ($catsS4->isNotEmpty()) {
            foreach ($catsS4 as $cat) {
                $this->appendHtmlOrTextToSection($mainSection, $cat->isi, 200);
            }
        } else {
            $catatansS4List = [
                "Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029 (halaman IV-34) dengan Lampiran SIPD-RI Excel (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran};",
                "Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) belum dilengkapi outcome, indikator, satuan, capaian (T-2) dan target;",
                "Terdapat ketidaksesuaian Indikator Kinerja, Target beserta Pagu Indikatif (Program) yang dicantumkan pada tabel 5.1 dan tabel 5.3 Rancangan Akhir RKPD tahun {$tahunAnggaran} dengan Lampiran SIPD-RI PDF dan Excel;",
                "Terdapat beberapa Program yang termasuk Program Prioritas Nasional tetapi dalam dokumen Rancangan Akhir RKPD kurang mendapatkan proporsi anggaran;",
                'Terdapat penetapan target kinerja "Persentase terbangunnya sarana prasarana persampahan sesuai standar (%)" namun tidak disertai dengan penetapan anggaran;',
                "Terdapat kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program di dokumen Rancangan Akhir RKPD dibandingkan dengan proporsi anggaran pada dokumen RPJMD;",
                'Program Pengolahan dan Pemasaran Hasil Perikanan termasuk dalam program utama unggulan Kepala Daerah namun proporsi anggaran belum memadai; dan',
                'Terdapat beberapa program yang bukan termasuk program prioritas namun proporsi anggaran yang diberikan cukup besar.',
            ];
            foreach ($catatansS4List as $idx => $cs) {
                $letter = chr(97 + $idx);
                $mainSection->addText("{$letter}. {$cs}", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 25, 'leftIndent' => 400]);
            }
        }

        $mainSection->addTextBreak(1);

        // 5) Kewajaran proyeksi pendanaan
        $mainSection->addText('5) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $catsS5 = $getCatatans('RKPD.S5');
        if ($catsS5->isNotEmpty()) {
            foreach ($catsS5 as $cat) {
                $this->appendHtmlOrTextToSection($mainSection, $cat->isi, 200);
            }
        } else {
            $catatansS5List = [
                'Data APBD tahun berjalan (tahun anggaran 2026) pada tabel 3.5 dokumen Rancangan Akhir RKPD belum menggunakan APBD berdasarkan Peraturan Bupati Rembang Nomor 4 tahun 2026 tentang Perubahan atas Peraturan Bupati Rembang Nomor 110 tahun 2025;',
                'Data Realisasi Anggaran tahun 2025 unaudited pada tabel 3.2, 3.3, dan 3.4 belum sesuai dengan LRA tahun 2025 unaudited terupdate;',
                "Proyeksi APBD tahun anggaran {$tahunAnggaran} pada tabel 3.5 Rancangan Akhir RKPD tidak sesuai dengan data proyeksi APBD di SIPD-RI; dan",
                'Pada tabel 3.5 Rancangan Akhir RKPD belum memproyeksikan belanja tidak terduga dan belanja transfer.',
            ];
            foreach ($catatansS5List as $idx => $cs) {
                $letter = chr(97 + $idx);
                $mainSection->addText("{$letter}. {$cs}", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 25, 'leftIndent' => 400]);
            }
        }

        $mainSection->addTextBreak(1);

        // 6) Kesesuaian Informasi dengan Program Prioritas Nasional
        $mainSection->addText('6) Kesesuaian Informasi pada rancangan akhir RKPD dengan Program Prioritas Nasional', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText("a. Asta Cita: Terdapat ketidakselarasan pemetaan urusan pemerintah daerah terhadap Asta Cita berdasarkan Rakortekrenbang Nasional Tahun 2026;", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText("b. Target Indikator Makro RKP: Rencana Kerja Pemerintah (RKP) tahun {$tahunAnggaran} belum tersedia;", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText("c. Program Strategis Nasional (ProSN): Subkegiatan yang mendukung pelaksanaan ProSN belum seluruhnya terpetakan dalam Lampiran SIPD-RI;", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText("d. Standar Pelayanan Minimal (SPM): Terdapat subkegiatan SPM yang belum terpetakan dalam Lampiran SIPD-RI dan terdapat beberapa subkegiatan SPM yang tidak mendapatkan proporsi anggaran memadai;", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText("e. Tematik Pembangunan: Terdapat subkegiatan Tematik Pembangunan yang belum terpetakan dalam Lampiran SIPD-RI dan belum teralokasi anggaran.", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 400]);

        // 7) Pokir DPRD
        $mainSection->addText('7) Pengujian atas Pokok-Pokok Pikiran DPRD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun {$tahunAnggaran} telah menjelaskan pokok-pokok pikiran DPRD, namun belum dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 200]
        );

        // 8) Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran
        $mainSection->addText('8) Pengujian atas Konsistensi pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran dalam Rancangan Akhir RKPD dan RPJMD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Terdapat ketidakkonsistenan target Indikator Kinerja Tujuan dan Sasaran yang dicantumkan pada tabel 3.6 RPJMD Kabupaten Rembang tahun 2025-2029 dengan tabel 4.1 Rancangan Akhir RKPD tahun {$tahunAnggaran}, sebagaimana penyandingan sebagai berikut:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 200]
        );

        // Tabel Konsistensi Misi 1 - 5 RPJMD vs RKPD
        $this->addTableKonsistensiRpjmdRkpd($mainSection, $tahunAnggaran);

        $mainSection->addPageBreak();

        // ── HALAMAN 12-15: 3. REKOMENDASI PERBAIKAN ───────────────────────────────
        $mainSection->addText('3. Rekomendasi', ['bold' => true], ['spaceAfter' => 20]);
        $mainSection->addText(
            "Berdasarkan hal tersebut, demi tersusunnya dokumen RKPD Kabupaten Rembang tahun {$tahunAnggaran} yang andal, akurat dan sebagai pedoman dalam penyusunan dokumen KUA PPAS tahun {$tahunAnggaran} direkomendasikan beberapa hal sebagai berikut:",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40]
        );

        $mainSection->addText('a. Format', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('Kesesuaian Format Rancangan Akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Menyesuaikan judul: 1) Sub Bab 1.2, 2) Sub Bab 1.3, 3) Sub Bab 1.5, 4) Sub Bab 2.1.3, 5) Sub Bab 2.1.4, 6) Bab 4, 7) Sub Bab 4.3 Dokumen Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} dengan mempedomani Lampiran Peraturan Menteri Dalam Negeri nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah (halaman 384);",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 50, 'leftIndent' => 200]
        );

        $mainSection->addText('b. Substansi', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('1) Pengujian atas Kelengkapan Dokumen Pendukung RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText("Melengkapi data/dokumen pendukung sebagai berikut: a) Hasil Evaluasi RKPD tahun berjalan (Triwulan 1); b) Laporan hasil pengendalian kebijakan penyusunan RKPD Formulir E.35; c) Jadwal / Agenda Kerja Tim Penyusunan RKPD tahun {$tahunAnggaran}; d) Rekapitulasi Pokok-pokok Pikiran DPRD tahun {$tahunAnggaran}.", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 200]);

        $mainSection->addText('2) Pengujian atas Analisis Isi BAB RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $this->addTableRekomendasiAnalisisBab($mainSection, $catsS2, $tahunAnggaran);

        $mainSection->addTextBreak(1);
        $mainSection->addText('3) Kesesuaian Program/Outcome dan Indikator pada RPJMD dengan RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $rekomS3List = [
            "Menyesuaikan indikator kinerja, target beserta pagu indikatif (Program) yang dicantumkan tabel 5.1, tabel 5.3 Rancangan Akhir RKPD tahun {$tahunAnggaran} dan Lampiran SIPD-RI dengan tabel 4.1 RPJMD Kabupaten Rembang tahun 2025-2029;",
            "Seluruh Program pada Lampiran SIPD-RI PDF (Renja OPD Kabupaten Rembang tahun {$tahunAnggaran}) agar dilengkapi outcome, indikator, satuan, capaian (T-2), target, dan pagu indikatif;",
            'Memproporsikan anggaran program dalam dokumen Rancangan Akhir RKPD yang termasuk Program Prioritas Nasional;',
            'Memproporsikan anggaran pada indikator kinerja pembangunan fisik/infrastruktur persampahan;',
            'Mengkaji kembali kenaikan proporsi anggaran yang cukup signifikan pada beberapa Program non prioritas;',
            'Memproporsikan anggaran untuk Program Pengolahan dan Pemasaran Hasil Perikanan yang merupakan program utama unggulan Kepala Daerah; dan',
            'Mengkaji kembali proporsi anggaran pada program bukan prioritas agar tidak melebihi alokasi program prioritas pembangunan.',
        ];
        foreach ($rekomS3List as $idx => $r) {
            $letter = chr(97 + $idx);
            $mainSection->addText("{$letter}. {$r}", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        }

        $mainSection->addTextBreak(1);
        $mainSection->addText('4) Kewajaran proyeksi pendanaan pada rancangan akhir RKPD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $rekomS4List = [
            'Penyajian Data APBD tahun anggaran berjalan agar mempedomani Peraturan Bupati Rembang tentang Penjabaran APBD terupdate;',
            'Penyajian Data Realisasi Anggaran unaudited agar disesuaikan dengan LRA unaudited terupdate;',
            "Menyesuaikan penyajian Proyeksi APBD tahun anggaran {$tahunAnggaran} pada tabel 3.5 dengan data proyeksi APBD di SIPD-RI; dan",
            "Pada tabel 3.5 Rancangan Akhir RKPD tahun {$tahunAnggaran} agar memproyeksikan belanja tidak terduga dan belanja transfer.",
        ];
        foreach ($rekomS4List as $idx => $r) {
            $letter = chr(97 + $idx);
            $mainSection->addText("{$letter}. {$r}", null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        }

        $mainSection->addTextBreak(1);
        $mainSection->addText('5) Kesesuaian Informasi dengan Program Prioritas Nasional', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText('a. Asta Cita: Melakukan perbaikan pemetaan urusan pemerintah daerah terhadap dukungan Asta Cita dan mengidentifikasi pemetaan subkegiatan yang mendukung outcome prioritas pemerintah pusat;', null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText('b. ProSN: Penyusunan dokumen perencanaan Kabupaten Rembang agar diselaraskan dan dipetakan sesuai dengan Program Strategis Nasional;', null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText('c. SPM: Penetapan subkegiatan dalam dokumen perencanaan agar diselaraskan dan dipetakan dengan seluruh subkegiatan SPM serta memproporsikan anggaran untuk semua indikator kinerja SPM;', null, ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => 400]);
        $mainSection->addText('d. Tematik: Penetapan subkegiatan agar diselaraskan dengan subkegiatan Tematik Pembangunan / Asta Cita Nasional.', null, ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 400]);

        $mainSection->addText('6) Kesesuaian Pokok-Pokok Pikiran DPRD', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Penjelasan Pokok-Pokok Pikiran DPRD pada Bab IV Sasaran dan Prioritas Pembangunan Daerah Tahun {$tahunAnggaran} agar dilengkapi dengan rekapitulasi data pokok-pokok pikiran DPRD yang berisi: Program/Kegiatan/Sub Kegiatan/Aktivitas, Indikator, Volume, Kelompok Sasaran, Lokasi dan Perangkat Daerah terkait.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 40, 'leftIndent' => 200]
        );

        $mainSection->addText('7) Konsistensi Pencantuman Indikator serta Target Kinerja Tujuan dan Sasaran', ['bold' => true], ['spaceAfter' => 20, 'leftIndent' => 200]);
        $mainSection->addText(
            "Mengkaji kembali atas penetapan target indikator kinerja Tujuan dan Sasaran yang tercantum pada tabel 3.6 RPJMD tahun 2025-2029 dan tabel 3.9 Teknik Perumusan Program Prioritas Pembangunan Daerah RPJMD tahun 2025-2029 dengan Rancangan Akhir RKPD Kabupaten Rembang tahun {$tahunAnggaran} yang tidak konsisten.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 80, 'leftIndent' => 200]
        );

        $mainSection->addPageBreak();

        // ── HALAMAN 16: J. APRESIASI & PENGESAHAN TANDA TANGAN ─────────────────────
        $mainSection->addText('J. Apresiasi', ['bold' => true, 'size' => 11], ['spaceAfter' => 60]);
        $mainSection->addText(
            "Demikian disampaikan hasil reviu atas rancangan akhir Rencana Kerja Pemerintah Daerah (RKPD) Kabupaten Rembang tahun {$tahunAnggaran}. Kami menyampaikan terima kasih atas bantuan dan kerjasama dari seluruh pejabat/pegawai pada Badan Perencanaan Pembangunan Daerah Kabupaten Rembang atas kesediaannya memberikan data/dokumen yang diperlukan, sehingga dapat mendukung terlaksananya kegiatan reviu Rancangan Akhir Rencana Kerja Pemerintah Daerah (RKPD) tahun {$tahunAnggaran}. Kami mengharapkan agar Saudara beserta seluruh jajaran untuk menindaklanjuti rekomendasi yang telah kami sampaikan.",
            null,
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'lineHeight' => 1.25]
        );

        $mainSection->addTextBreak(2);

        // Kolom Tanda Tangan Inspektur Daerah
        $ttdTable = $mainSection->addTable(['width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        $ttdTable->addRow();
        $ttdTable->addCell(5000); // Spacer kolom kiri
        $ttdCell = $ttdTable->addCell(5000, ['alignment' => Jc::CENTER]);
        $ttdCell->addText('INSPEKTUR DAERAH', ['bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $ttdCell->addText('KABUPATEN REMBANG', ['bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);
        $ttdCell->addTextBreak(3);

        $signerName = $lhr->signer?->name ?? 'IMUNG TRI WIJAYANTI, S.P., M.T., M.A., CGCAE.';
        $signerNip = $lhr->signer?->nip ?? '19741128 199903 2 003';
        $ttdCell->addText($signerName, ['bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $ttdCell->addText('Pembina Utama Muda', null, ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $ttdCell->addText("NIP. {$signerNip}", null, ['alignment' => Jc::CENTER]);

        // ── LAMPIRAN: DAFTAR DOKUMEN PENDUKUNG (JIKA ADA) ─────────────────────────
        $attachments = $data['attachments'] ?? $lhr->attachments;
        if ($attachments && $attachments->isNotEmpty()) {
            $mainSection->addPageBreak();
            $mainSection->addText('LAMPIRAN: DAFTAR DOKUMEN PENDUKUNG REVIU', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
            $mainSection->addText(
                "Daftar berkas dan dokumen pendukung perencanaan yang dihimpun, diverifikasi, dan ditelaah selama pelaksanaan reviu oleh Tim Reviu Inspektorat Daerah Kabupaten Rembang:",
                null,
                ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'lineHeight' => 1.15]
            );

            $attTable = $mainSection->addTable([
                'borderSize' => 6,
                'borderColor' => 'CCCCCC',
                'cellMargin' => 60,
                'width' => 100 * 50,
                'unit' => TblWidth::PERCENT,
            ]);

            $attTable->addRow(300, ['tblHeader' => true]);
            $attTable->addCell(600, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('No', ['bold' => true, 'size' => 9]);
            $attTable->addCell(4000, ['bgColor' => 'F2F2F2'])->addText('Nama Berkas Dokumen', ['bold' => true, 'size' => 9]);
            $attTable->addCell(3000, ['bgColor' => 'F2F2F2'])->addText('Keterangan / Uraian', ['bold' => true, 'size' => 9]);
            $attTable->addCell(1200, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('Format', ['bold' => true, 'size' => 9]);
            $attTable->addCell(1200, ['bgColor' => 'F2F2F2', 'alignment' => Jc::RIGHT])->addText('Ukuran', ['bold' => true, 'size' => 9]);

            foreach ($attachments as $i => $att) {
                $ext = strtoupper(pathinfo($att->file_name, PATHINFO_EXTENSION) ?: 'BERKAS');
                $attTable->addRow();
                $attTable->addCell(600)->addText((string)($i + 1), null, ['alignment' => Jc::CENTER]);
                $attTable->addCell(4000)->addText($att->file_name, ['bold' => true]);
                $attTable->addCell(3000)->addText($att->keterangan ?: '-');
                $attTable->addCell(1200)->addText($ext, null, ['alignment' => Jc::CENTER]);
                $attTable->addCell(1200)->addText($att->file_size_formatted, null, ['alignment' => Jc::RIGHT]);
            }

            $mainSection->addTextBreak(1);
            $mainSection->addText(
                "* Seluruh berkas lampiran tersimpan secara aman pada repositori arsip digital SIAPTUNTAS Inspektorat Daerah Kabupaten Rembang.",
                ['italic' => true, 'size' => 8.5, 'color' => '666666']
            );
        }

        // ══════════════════════════════════════════════════════════════════════════
        // SIMPAN FILE DOCX
        // ══════════════════════════════════════════════════════════════════════════
        $tempPath = tempnam(sys_get_temp_dir(), 'lhr_word_') . '.docx';
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempPath);

        $binary = file_get_contents($tempPath);
        @unlink($tempPath);

        $storagePath = "private/lhr/{$lhr->uuid}/v{$lhr->versi}.docx";
        if ($saveToDisk) {
            Storage::put($storagePath, $binary);
        }

        return [
            'path' => $storagePath,
            'binary' => $binary,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER METHODS UNTUK FORMATTING & TABEL-TABEL REMBANG
    // ──────────────────────────────────────────────────────────────────────────

    protected function addHeaderKop(Section $section): void
    {
        $section->addText('PEMERINTAH KABUPATEN REMBANG', ['bold' => true, 'size' => 11], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $section->addText('INSPEKTORAT DAERAH', ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $section->addText('JL. RAYA REMBANG LASEM KM. 1.1 REMBANG TELP. (0295) 691320', ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $section->addText('Fax. (0295) 693525 Rembang - 59219 Email: Inspektorat@rembangkab.go.id', ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 40]);

        $tGaris = $section->addTable(['width' => 100 * 50, 'unit' => TblWidth::PERCENT]);
        $tGaris->addRow(15);
        $tGaris->addCell(9500, ['borderBottomSize' => 18, 'borderBottomColor' => '000000']);
        $section->addTextBreak(1);
    }

    protected function addTeamTable(Section $section, array $rows): void
    {
        $t = $section->addTable(['width' => 95 * 50, 'unit' => TblWidth::PERCENT, 'alignment' => Jc::LEFT]);
        foreach ($rows as $r) {
            $t->addRow();
            $t->addCell(2200)->addText($r[0], null, ['spaceAfter' => 10]);
            $t->addCell(7800)->addText($r[1], null, ['spaceAfter' => 10]);
        }
        $section->addTextBreak(1);
    }

    protected function addTableFormatPermendagri(Section $section, int $tahun): void
    {
        $t = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $t->addRow(300, ['tblHeader' => true]);
        $t->addCell(1000, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('BAB', ['bold' => true], ['alignment' => Jc::CENTER]);
        $t->addCell(4500, ['bgColor' => 'F2F2F2'])->addText('Lampiran Permendagri No. 86 tahun 2017', ['bold' => true]);
        $t->addCell(4500, ['bgColor' => 'F2F2F2'])->addText("Rankir RKPD tahun {$tahun}", ['bold' => true]);

        $rows = [
            ['1.', 'BAB I PENDAHULUAN', 'BAB I PENDAHULUAN', true],
            ['1.1.', 'Latar Belakang', 'Latar Belakang', false],
            ['1.2.', 'Dasar Hukum Penyusunan', 'Dasar Hukum', false],
            ['1.3.', 'Hubungan Antar Dokumen', 'Hubungan Antara Dokumen', false],
            ['1.4.', 'Maksud dan Tujuan', 'Maksud dan Tujuan', false],
            ['1.5.', 'Sistematika Dokumen RKPD', "Sistematika Dokumen RKPD Tahun {$tahun}", false],
            ['2.', 'BAB II GAMBARAN UMUM KONDISI DAERAH', 'BAB II GAMBARAN UMUM KONDISI DAERAH', true],
            ['2.1.', 'Kondisi Umum Daerah', 'Kondisi Umum Daerah', false],
            ['2.1.1.', 'Aspek Geografi dan Demografi', 'Aspek Geografi dan Demografi', false],
            ['2.1.2.', 'Aspek Kesejahteraan Masyarakat', 'Aspek Kesejahteraan Masyarakat', false],
            ['2.1.3.', 'Aspek Pelayanan Umum', 'Aspek Daya Saing Daerah', false],
            ['2.1.4.', 'Aspek Daya Saing Daerah', 'Aspek Pelayanan Umum', false],
            ['2.2.', 'Evaluasi Pelaksanaan Program dan Kegiatan RKPD sampai Tahun Berjalan dan Realisasi RPJMD', 'Evaluasi Pelaksanaan Kinerja RKPD sampai Tahun 2025 dan Realisasi RPJMD', false],
            ['2.2.1', '-', 'Hasil Evaluasi Indikator Makro Daerah Tahun 2021-2025', false],
            ['2.2.2', '-', 'Hasil Evaluasi Capaian Kinerja Tahun 2025', false],
            ['2.3.', 'Permasalahan Pembangunan Daerah', 'Permasalahan Pembangunan Daerah', false],
            ['2.3.1.', 'Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah', 'Permasalahan daerah yang berhubungan dengan prioritas dan sasaran pembangunan daerah', false],
            ['2.3.2.', 'Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah', 'Identifikasi Permasalahan penyelenggaraan urusan pemerintah daerah', false],
            ['3.', 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', 'BAB III KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', true],
            ['3.1.', 'Arah Kebijakan Ekonomi Daerah', 'Arah Kebijakan Ekonomi Daerah', false],
            ['3.2.', 'Arah Kebijakan Keuangan Daerah', 'Arah Kebijakan Keuangan Daerah', false],
            ['3.2.1.', '-', 'Arah Kebijakan Pendapatan Daerah', false],
            ['3.2.2.', '-', 'Arah Kebijakan Belanja Daerah', false],
            ['3.2.3.', '-', 'Arah Kebijakan Pembiayaan Daerah', false],
            ['3.2.4.', '-', 'Proyeksi Pendapatan dan Belanja', false],
            ['4.', 'BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH', "BAB IV SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", true],
            ['4.1.', 'Tujuan dan Sasaran Pembangunan', 'Tujuan dan Sasaran Pembangunan', false],
            ['4.2.', 'Arah Kebijakan Pembangunan', 'Arah Kebijakan Pembangunan', false],
            ['4.3.', "Prioritas dan Sasaran Pembangunan Tahun {$tahun}", 'Prioritas Pembangunan', false],
            ['4.3.1.', '-', "Prioritas Pembangunan Nasional Tahun {$tahun}", false],
            ['4.3.2.', '-', "Prioritas Pembangunan Provinsi Jawa Tengah Tahun {$tahun}", false],
            ['4.3.3.', '-', "Prioritas Pembangunan Kabupaten Rembang Tahun {$tahun}", false],
            ['4.3.4.', '-', "Keterkaitan Prioritas Nasional, Prioritas Jawa Tengah dan Prioritas Kabupaten Rembang Tahun {$tahun}", false],
            ['4.3.5.', '-', 'Penyelarasan Target Indikator Makro dan Tematik Berdasarkan Hasil Rakortekrenbang Provinsi', false],
            ['4.4.', 'Pokok-Pokok Pikiran DPRD', 'Pokok-Pokok Pikiran DPRD', false],
            ['4.5.', 'Inovasi Kebijakan Pembangunan', 'Inovasi Kebijakan Pembangunan', false],
            ['5.', 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH', 'BAB V RENCANA KERJA DAN PENDANAAN DAERAH', true],
            ['5.1.', '-', 'Program Prioritas Daerah yang mendukung Prioritas Nasional dan Provinsi Jawa Tengah', false],
            ['5.2.', '-', 'Program Perangkat Daerah', false],
            ['5.2.1.', '-', 'Urusan Wajib Pemerintah Daerah', false],
            ['5.2.2.', '-', 'Urusan Pemerintahan Wajib yang tidak berkaitan dengan Pelayanan Dasar', false],
            ['5.2.3.', '-', 'Urusan Pemerintahan Pilihan', false],
            ['5.2.4.', '-', 'Urusan Penunjang Pemerintah', false],
            ['5.3.', '-', "Kerangka Pendanaan Tahun {$tahun}", false],
            ['6.', 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH', 'BAB VI KINERJA PENYELENGGARAAN PEMERINTAHAN DAERAH', true],
            ['6.1.', 'Indikator Kinerja Utama Daerah', 'Indikator Kinerja Utama Daerah', false],
            ['6.2.', 'Indikator Kinerja Daerah', 'Indikator Kinerja Daerah', false],
            ['7.', 'BAB VII PENUTUP', 'BAB VII PENUTUP', true],
        ];

        foreach ($rows as $r) {
            $t->addRow();
            $t->addCell(1000)->addText($r[0], $r[3] ? ['bold' => true] : null);
            $t->addCell(4500)->addText($r[1], $r[3] ? ['bold' => true] : null);
            $t->addCell(4500)->addText($r[2], $r[3] ? ['bold' => true] : null);
        }
    }

    protected function addTableAnalisisIsiBabCatatan(Section $section, int $tahun): void
    {
        $t = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $t->addRow(300, ['tblHeader' => true]);
        $t->addCell(800, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('NO', ['bold' => true], ['alignment' => Jc::CENTER]);
        $t->addCell(9200, ['bgColor' => 'F2F2F2'])->addText('CATATAN', ['bold' => true]);

        // Bab II Header
        $t->addRow();
        $t->addCell(10000, ['gridSpan' => 2, 'bgColor' => 'F9FAFB'])->addText('BAB II GAMBARAN UMUM KONDISI DAERAH', ['bold' => true]);
        $t->addRow();
        $t->addCell(800)->addText('1.', null, ['alignment' => Jc::CENTER]);
        $c1 = $t->addCell(9200);
        $c1->addText('2.1.4 Aspek Pelayanan Umum', ['bold' => true]);
        $c1->addText('a. Belum menguraikan terkait Indikator Standar Pelayanan Minimum (SPM) sebagaimana tercantum dalam Peraturan Pemerintah Nomor 2 Tahun 2018; dan');
        $c1->addText('b. Masih mencantumkan terkait anggaran, Sumber Daya Manusia dan sarana prasarana sebagai faktor penghambat kegagalan/ketidaktercapaian atas indikator kinerja tahun 2025.');

        // Bab III Header
        $t->addRow();
        $t->addCell(10000, ['gridSpan' => 2, 'bgColor' => 'F9FAFB'])->addText('BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', ['bold' => true]);
        $t->addRow();
        $t->addCell(800)->addText('1.', null, ['alignment' => Jc::CENTER]);
        $c2 = $t->addCell(9200);
        $c2->addText('3.1 Arah Kebijakan Ekonomi Daerah', ['bold' => true]);
        $c2->addText('Penjelasan terkait pertumbuhan ekonomi belum didukung dengan data pendukung yang disajikan pada tabel secara lengkap: PDRB Konstan, ADHB, ADHK Pengeluaran, dan Laju Inflasi.');

        $t->addRow();
        $t->addCell(800)->addText('2.', null, ['alignment' => Jc::CENTER]);
        $c3 = $t->addCell(9200);
        $c3->addText('3.2 Arah Kebijakan Keuangan Daerah', ['bold' => true]);
        $c3->addText("a. Terdapat ketidaksesuaian angka proyeksi PAD dan belanja pada dokumen Rankir RKPD (tabel 3.5) dengan SIPD Tahun {$tahun};");
        $c3->addText('b. Kebijakan belanja belum memprioritaskan belanja pelayanan dasar;');
        $c3->addText('c. Belum memproyeksikan belanja tidak terduga dan belanja transfer;');
        $c3->addText('d. Proyeksi belanja belum dirinci berdasarkan kategori rekening belanja;');
        $c3->addText('e. Terdapat proyeksi pembayaran Pembiayaan Utang Daerah Rp 4.500.000.000,- yang tidak dijelaskan peruntukannya;');
        $c3->addText('f. Belum menginformasikan rasio ruang fiskal tahunan; dan');
        $c3->addText("g. Belum menginformasikan Kapasitas Riil Kemampuan Keuangan Daerah tahun {$tahun}.");

        // Bab IV Header
        $t->addRow();
        $t->addCell(10000, ['gridSpan' => 2, 'bgColor' => 'F9FAFB'])->addText("BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", ['bold' => true]);
        $t->addRow();
        $t->addCell(800)->addText('1.', null, ['alignment' => Jc::CENTER]);
        $c4 = $t->addCell(9200);
        $c4->addText('4.2 Arah Kebijakan Pembangunan', ['bold' => true]);
        $c4->addText("Arah kebijakan pembangunan RKPD dalam tabel 4.2 belum difokuskan dalam mewujudkan tema pembangunan tahun {$tahun}.");

        $t->addRow();
        $t->addCell(800)->addText('2.', null, ['alignment' => Jc::CENTER]);
        $c5 = $t->addCell(9200);
        $c5->addText('4.3 Prioritas Pembangunan (4.3.3 Prioritas Pembangunan Daerah)', ['bold' => true]);
        $c5->addText('Inventarisasi permasalahan di Bab II belum sepenuhnya menjadi prioritas yang ditangani.');
    }

    protected function addTablePrioritasPembangunan(Section $section, int $tahun): void
    {
        $t = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $t->addRow(300, ['tblHeader' => true]);
        $t->addCell(5000, ['bgColor' => 'F2F2F2'])->addText("Prioritas Pembangunan tahun {$tahun}\n(RPJMD 2025-2029)", ['bold' => true]);
        $t->addCell(5000, ['bgColor' => 'F2F2F2'])->addText("Prioritas Pembangunan tahun {$tahun}\n(Rankir RKPD {$tahun})", ['bold' => true]);

        $prioritasList = [
            '1. Peningkatan Pembangunan SDM',
            '2. Peningkatan Perekonomian Daerah',
            '3. Peningkatan Kualitas Pelayanan Publik',
            '4. Peningkatan Infrastruktur Mantap',
            '5. Peningkatan Daya Dukung Lingkungan Hidup',
        ];

        foreach ($prioritasList as $p) {
            $t->addRow();
            $t->addCell(5000)->addText($p);
            $t->addCell(5000)->addText($p);
        }
    }

    protected function addTableKonsistensiRpjmdRkpd(Section $section, int $tahun): void
    {
        $t = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);

        $t->addRow(250, ['tblHeader' => true]);
        $t->addCell(2200, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText("Misi/Tujuan/Sasaran", ['bold' => true, 'size' => 8.5]);
        $t->addCell(3900, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText("Rankir RPJMD 2025-2029 (Tabel 3.6)", ['bold' => true, 'size' => 8.5]);
        $t->addCell(3900, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText("Rankir RKPD {$tahun} (Tabel 3.9 & Teknik Perumusan)", ['bold' => true, 'size' => 8.5]);

        $t->addRow(250, ['tblHeader' => true]);
        $t->addCell(2200, ['bgColor' => 'EFEFEF'])->addText('');
        $t->addCell(3900, ['bgColor' => 'EFEFEF'])->addText("Indikator | Satuan | Target {$tahun}", ['bold' => true, 'size' => 8]);
        $t->addCell(3900, ['bgColor' => 'EFEFEF'])->addText("Indikator | Satuan | Target {$tahun}", ['bold' => true, 'size' => 8]);

        $misis = [
            [
                'misi' => 'Misi 1: Meningkatkan Kualitas Sumber Daya Manusia yang Unggul, Sehat dan Berdaya Saing',
                'rows' => [
                    ['Terwujudnya kualitas SDM produktif', 'Indeks Daya Saing Daerah | Angka | 3,78', 'Indeks Daya Saing Daerah | Angka | 3,78'],
                    ['Meningkatnya akses pendidikan & kesehatan', "Indeks Pembangunan Manusia | Tahun | 74,38\nIndeks Ketimpangan Gender | Tahun | 0,246 / 0,22", "Indeks Pembangunan Manusia | Tahun | 73,70\nIndeks Ketimpangan Gender | Tahun | 0,246 / 2,22"],
                ]
            ],
            [
                'misi' => 'Misi 2: Mewujudkan Pertumbuhan Ekonomi Daerah yang Berkelanjutan dan Inklusif',
                'rows' => [
                    ['Meningkatnya pertumbuhan ekonomi', 'Pertumbuhan Ekonomi | % | 5,90 - 7,75', 'Pertumbuhan Ekonomi | % | 5,40 - 6,20'],
                    ['Meningkatnya investasi & tenaga kerja', "PMTB dalam PDRB | % | 24,02\nPDRB Per Kapita | Juta Rp | 57,81\nPengangguran Terbuka | % | 2,45 / 2,15", "PMTB dalam PDRB | % | 24,02\nPDRB Per Kapita | Juta Rp | 57,81\nPengangguran Terbuka | % | 2,45 / 2,15"],
                    ['Penurunan kemiskinan & ketahanan pangan', "Tingkat Kemiskinan | % | 12,01 / 11,70\nIndeks Ketahanan Pangan | Angka | 85,34", "Tingkat Kemiskinan | % | 12,73 / 12,43\nIndeks Ketahanan Pangan | Angka | 85,34"],
                ]
            ],
            [
                'misi' => 'Misi 3: Mewujudkan Tata Kelola Pemerintahan dan Pelayanan Publik yang Profesional',
                'rows' => [
                    ['Meningkatnya tata kelola pemerintahan', "Indeks Reformasi Birokrasi | Angka | 89,66\n- RB General: 79,05 | Tematik: 10,61", "Indeks Reformasi Birokrasi | Angka | 89,66\n- RB General: 79,05 | Tematik: 10,61"],
                ]
            ],
            [
                'misi' => 'Misi 4: Mewujudkan Pembangunan Infrastruktur yang Berkualitas dan Berkelanjutan',
                'rows' => [
                    ['Meningkatnya pemerataan infrastruktur', "Indeks Infrastruktur | Angka | 70,31\nInfrastruktur Konektivitas | Angka | 68,18\nInfrastruktur Permukiman | % | 72,44", "Indeks Infrastruktur | Angka | 70,31\nInfrastruktur Konektivitas | Angka | 68,18\nInfrastruktur Permukiman | % | 72,44"],
                ]
            ],
            [
                'misi' => 'Misi 5: Meningkatkan Kualitas Lingkungan Hidup dan Pengelolaan SDA Berkelanjutan',
                'rows' => [
                    ['Kualitas lingkungan & ketahanan bencana', "IKLH | Angka | 76,23\nPenurunan Emisi GRK | TonCO2 eq | 1.815.353,68\nIndeks Risiko Bencana | Angka | 107,82 - 106,8", "IKLH | Angka | 76,23\nPenurunan Emisi GRK | TonCO2 eq | 1.815.353,68\nIndeks Risiko Bencana | Angka | 107,82 - 106,8"],
                ]
            ]
        ];

        foreach ($misis as $m) {
            $t->addRow();
            $t->addCell(10000, ['gridSpan' => 3, 'bgColor' => 'E6E6E6'])->addText($m['misi'], ['bold' => true, 'size' => 8.5]);
            foreach ($m['rows'] as $r) {
                $t->addRow();
                $t->addCell(2200)->addText($r[0], ['size' => 8]);
                $t->addCell(3900)->addText($r[1], ['size' => 8]);
                $t->addCell(3900)->addText($r[2], ['size' => 8]);
            }
        }
    }

    protected function addTableRekomendasiAnalisisBab(Section $section, Collection $catsS2, int $tahun): void
    {
        $t = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'alignment' => Jc::CENTER,
        ]);
        $t->addRow(300, ['tblHeader' => true, 'cantSplit' => true]);
        $t->addCell(700, ['bgColor' => 'F2F2F2', 'alignment' => Jc::CENTER])->addText('NO', ['bold' => true, 'size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
        $t->addCell(4350, ['bgColor' => 'F2F2F2'])->addText('CATATAN', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
        $t->addCell(4350, ['bgColor' => 'F2F2F2'])->addText('REKOMENDASI', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);

        if ($catsS2->isNotEmpty()) {
            foreach ($catsS2 as $idx => $cat) {
                $t->addRow(null, ['cantSplit' => true]);
                $t->addCell(700)->addText((string)($idx + 1) . '.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
                $cCat = $t->addCell(4350);
                if ($cat->referensi_bagian) {
                    $cCat->addText($cat->referensi_bagian, ['bold' => true, 'size' => 9.5, 'name' => 'Arial'], ['spaceAfter' => 20]);
                }
                $this->appendHtmlOrText($cCat, $cat->isi);

                $cRek = $t->addCell(4350);
                if ($cat->rekomendasis && $cat->rekomendasis->isNotEmpty()) {
                    foreach ($cat->rekomendasis as $r) {
                        $this->appendHtmlOrText($cRek, $r->uraian_rekom);
                    }
                } else {
                    $cRek->addText('Agar segera dilakukan perbaikan dokumen sesuai catatan.', ['size' => 9.5, 'name' => 'Arial']);
                }
            }
        } else {
            // Bab II
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(9400, ['gridSpan' => 3, 'bgColor' => 'F9FAFB'])->addText('BAB II GAMBARAN UMUM KONDISI DAERAH', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(700)->addText('1.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $c1 = $t->addCell(4350);
            $c1->addText('2.1.4 Aspek Pelayanan Umum', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $c1->addText('Belum menguraikan SPM per PP 2/2018 dan masih mencantumkan faktor anggaran/SDM sebagai penghambat.', ['size' => 9.5, 'name' => 'Arial']);
            $r1 = $t->addCell(4350);
            $r1->addText('Menambahkan uraian terkait Indikator SPM per PP 2/2018; dan menghapus faktor penghambat anggaran/SDM.', ['size' => 9.5, 'name' => 'Arial']);

            // Bab III
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(9400, ['gridSpan' => 3, 'bgColor' => 'F9FAFB'])->addText('BAB III. KERANGKA EKONOMI DAERAH DAN KEUANGAN DAERAH', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(700)->addText('1.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $c2 = $t->addCell(4350);
            $c2->addText('3.1 Arah Kebijakan Ekonomi Daerah', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $c2->addText('Data pendukung pertumbuhan ekonomi belum lengkap disajikan.', ['size' => 9.5, 'name' => 'Arial']);
            $r2 = $t->addCell(4350);
            $r2->addText('Melengkapi data pendukung pertumbuhan ekonomi (PDRB ADHK, ADHB, ADHK Pengeluaran, dan Inflasi).', ['size' => 9.5, 'name' => 'Arial']);

            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(700)->addText('2.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $c3 = $t->addCell(4350);
            $c3->addText('3.2 Arah Kebijakan Keuangan Daerah', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $c3->addText("Ketidaksesuaian angka proyeksi PAD & belanja dengan SIPD; belum memprioritaskan belanja pelayanan dasar; pembayaran pembiayaan utang Rp 4,5M tidak dijelaskan peruntukannya.", ['size' => 9.5, 'name' => 'Arial']);
            $r3 = $t->addCell(4350);
            $r3->addText("Menyelaraskan proyeksi Pendapatan, Belanja, dan Pembiayaan dengan SIPD; prioritaskan pelayanan dasar; proyeksikan BTT & transfer; jelaskan peruntukan utang Rp 4,5M.", ['size' => 9.5, 'name' => 'Arial']);

            // Bab IV
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(9400, ['gridSpan' => 3, 'bgColor' => 'F9FAFB'])->addText("BAB IV. SASARAN DAN PRIORITAS PEMBANGUNAN DAERAH TAHUN {$tahun}", ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(700)->addText('1.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $c4 = $t->addCell(4350);
            $c4->addText('4.2 Arah Kebijakan Pembangunan', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $c4->addText("Arah kebijakan pembangunan belum difokuskan mewujudkan tema pembangunan tahun {$tahun}.", ['size' => 9.5, 'name' => 'Arial']);
            $r4 = $t->addCell(4350);
            $r4->addText("Memfokuskan arah kebijakan pembangunan dalam mewujudkan tema pembangunan tahun {$tahun}.", ['size' => 9.5, 'name' => 'Arial']);

            $t->addRow(null, ['cantSplit' => true]);
            $t->addCell(700)->addText('2.', ['size' => 9.5, 'name' => 'Arial'], ['alignment' => Jc::CENTER]);
            $c5 = $t->addCell(4350);
            $c5->addText('4.3 Prioritas Pembangunan', ['bold' => true, 'size' => 9.5, 'name' => 'Arial']);
            $c5->addText('Inventarisasi permasalahan di Bab II belum sepenuhnya menjadi prioritas yang ditangani.', ['size' => 9.5, 'name' => 'Arial']);
            $r5 = $t->addCell(4350);
            $r5->addText('Menambahkan prioritas pembangunan sesuai permasalahan yang dituangkan dalam Bab II.', ['size' => 9.5, 'name' => 'Arial']);
        }
    }

    protected function appendHtmlOrTextToSection(Section $section, ?string $content, int $leftIndent = 0, float $fontSize = 10): void
    {
        if (empty($content)) return;
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $content);
        $sanitized = str_replace(['&nbsp;', '&#160;'], ' ', $sanitized);
        $sanitized = preg_replace('/<br\s*\/?>/i', "\n", $sanitized);
        $sanitized = preg_replace('/<\/li>/i', "\n", $sanitized);
        $sanitized = preg_replace('/<\/p>/i', "\n\n", $sanitized);
        $plain = strip_tags($sanitized);
        foreach (explode("\n", $plain) as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if ($line !== '') {
                $section->addText($line, ['size' => $fontSize, 'name' => 'Arial'], ['alignment' => Jc::BOTH, 'spaceAfter' => 20, 'leftIndent' => $leftIndent]);
            }
        }
    }

    protected function appendHtmlOrText($cell, ?string $content, float $fontSize = 9.5): void
    {
        if (empty($content)) {
            $cell->addText('-', ['size' => $fontSize, 'name' => 'Arial'], ['spaceAfter' => 20]);
            return;
        }

        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $content);
        $sanitized = str_replace(['&nbsp;', '&#160;'], ' ', $sanitized);

        // Normalize HTML breaks and block tags to newlines
        $sanitized = preg_replace('/<br\s*\/?>/i', "\n", $sanitized);
        $sanitized = preg_replace('/<\/li>/i', "\n", $sanitized);
        $sanitized = preg_replace('/<\/p>/i', "\n\n", $sanitized);
        $sanitized = preg_replace('/<\/div>/i', "\n", $sanitized);

        $plain = strip_tags($sanitized);
        $hasText = false;

        foreach (explode("\n", $plain) as $line) {
            $text = trim(preg_replace('/\s+/', ' ', $line));
            if ($text !== '') {
                $hasText = true;
                $cell->addText($text, ['size' => $fontSize, 'name' => 'Arial'], ['alignment' => Jc::BOTH, 'spaceAfter' => 20]);
            }
        }

        if (!$hasText) {
            $cell->addText('-', ['size' => $fontSize, 'name' => 'Arial'], ['spaceAfter' => 20]);
        }
    }
}
