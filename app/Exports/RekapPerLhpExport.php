<?php

namespace App\Exports;

use App\Models\Lhp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapPerLhpExport implements WithMultipleSheets
{
    public function __construct(private Lhp $lhp) {}

    public function sheets(): array
    {
        return [
            new InfoLhpSheet($this->lhp),
            new TemuanSheet($this->lhp),
            new RekomSheet($this->lhp),
        ];
    }
}

// ── Sheet 1: Info LHP ─────────────────────────────────────────────────────────

class InfoLhpSheet implements FromCollection, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(private Lhp $lhp) {}

    public function title(): string { return 'Info LHP'; }

    public function collection(): Collection
    {
        $lhp  = $this->lhp;
        $stat = $lhp->statistik;

        return collect([
            ['REKAP LHP', '', ''],
            [''],
            ['Nomor LHP',        $lhp->nomor_lhp,                                         ''],
            ['Tanggal LHP',      $lhp->tanggal_lhp?->format('d F Y') ?? '-',              ''],
            ['Semester',         'Semester ' . $lhp->semester,                             ''],
            ['IRBAN',            $lhp->irban,                                              ''],
            ['Jenis Pemeriksaan',$lhp->jenis_pemeriksaan ?? '-',                           ''],
            ['Program Audit',    $lhp->auditAssignment?->auditProgram?->nama_program ?? '-',''],
            ['Unit Diperiksa',   $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? '-', ''],
            ['Status',           ucfirst(str_replace('_', ' ', $lhp->status)),             ''],
            [''],
            ['STATISTIK', '', ''],
            ['Total Temuan',     $stat?->total_temuan ?? 0,     ''],
            ['Total Rekomendasi',$stat?->total_rekomendasi ?? 0,''],
            ['Rekom Selesai',    $stat?->rekom_selesai ?? 0,    ''],
            ['Rekom Proses',     $stat?->rekom_proses ?? 0,     ''],
            ['Rekom Belum TL',   $stat?->rekom_belum ?? 0,      ''],
            ['Total Kerugian',   (float) ($stat?->total_kerugian ?? 0), '(Rp)'],
            ['TL Selesai',       (float) ($stat?->total_nilai_tl_selesai ?? 0), '(Rp)'],
            ['Sisa Kerugian',    (float) ($stat?->total_sisa_kerugian ?? 0), '(Rp)'],
            ['Progress TL',      round($stat?->persen_selesai_gabungan ?? 0, 1) . '%', ''],
        ]);
    }

    public function columnWidths(): array
    {
        return ['A' => 22, 'B' => 45, 'C' => 8];
    }

    public function styles(Worksheet $sheet): array
    {
        // Judul
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Sub-header statistik
        $sheet->mergeCells('A12:C12');
        $sheet->getStyle('A12')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
        ]);

        // Label info (kolom A)
        $sheet->getStyle('A3:A10')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
        ]);
        $sheet->getStyle('A13:A21')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
        ]);

        // Format rupiah
        foreach ([18, 19, 20] as $row) {
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}

// ── Sheet 2: Daftar Temuan ────────────────────────────────────────────────────

class TemuanSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(private Lhp $lhp) {}

    public function title(): string { return 'Temuan'; }

    public function collection(): Collection
    {
        return $this->lhp->temuans->map(function ($t, $i) {
            return [
                'no'                => $i + 1,
                'kode_temuan'       => $t->kodeTemuan?->kode ?? '-',
                'kondisi'           => $t->kondisi ?? '-',
                'sebab'             => $t->sebab ?? '-',
                'akibat'            => $t->akibat ?? '-',
                'kerugian_negara'   => (float) ($t->nilai_kerugian_negara ?? 0),
                'kerugian_daerah'   => (float) ($t->nilai_kerugian_daerah ?? 0),
                'kerugian_desa'     => (float) ($t->nilai_kerugian_desa ?? 0),
                'kerugian_bos_blud' => (float) ($t->nilai_kerugian_bos_blud ?? 0),
                'total_kerugian'    => (float) $t->total_nilai_temuan,
                'jumlah_rekom'      => $t->recommendations->count(),
                'status_tl'         => match ($t->status_tl) {
                    'selesai'               => 'Selesai',
                    'dalam_proses'          => 'Dalam Proses',
                    'belum_ditindaklanjuti' => 'Belum TL',
                    default                 => $t->status_tl,
                },
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Temuan', 'Kondisi', 'Sebab', 'Akibat',
            'Kerugian Negara', 'Kerugian Daerah', 'Kerugian Desa', 'Kerugian BOS/BLUD',
            'Total Kerugian', 'Jml Rekom', 'Status TL',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 14, 'C' => 40, 'D' => 30, 'E' => 30,
            'F' => 18, 'G' => 18, 'H' => 16, 'I' => 18,
            'J' => 18, 'K' => 10, 'L' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->lhp->temuans->count() + 1;

        $sheet->getStyle("A1:L1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        for ($row = 2; $row <= $lastRow; $row++) {
            $bg = ($row % 2 === 0) ? 'FFFBEB' : 'FFFFFF';
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
        }

        $sheet->getStyle("F2:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->freezePane('A2');

        return [];
    }
}

// ── Sheet 3: Rekomendasi & TL ─────────────────────────────────────────────────

class RekomSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(private Lhp $lhp) {}

    public function title(): string { return 'Rekomendasi & TL'; }

    public function collection(): Collection
    {
        $rows = collect();
        $no   = 1;

        foreach ($this->lhp->temuans as $temuan) {
            foreach ($temuan->recommendations as $r) {
                $tls       = $r->tindakLanjuts;
                $totalTl   = (float) ($r->nilai_tl_selesai ?? 0);
                $sisaNilai = (float) ($r->nilai_sisa ?? 0);

                $rows->push([
                    'no'             => $no++,
                    'kode_temuan'    => $temuan->kodeTemuan?->kode ?? '-',
                    'kode_rekom'     => $r->kodeRekomendasi?->kode ?? '-',
                    'uraian_rekom'   => $r->uraian_rekom ?? '-',
                    'jenis_rekom'    => ucfirst($r->jenis_rekomendasi),
                    'nilai_rekom'    => (float) ($r->nilai_rekom ?? 0),
                    'nilai_tl'       => $totalTl,
                    'sisa_nilai'     => $sisaNilai,
                    'jumlah_tl'      => $tls->count(),
                    'status'         => match ($r->status) {
                        'selesai'               => 'Selesai',
                        'proses'                => 'Proses',
                        'belum_ditindaklanjuti' => 'Belum TL',
                        default                 => $r->status,
                    },
                    'progress'       => $r->progress() . '%',
                    'batas_waktu'    => $r->batas_waktu?->format('d/m/Y') ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Temuan', 'Kode Rekom', 'Uraian Rekomendasi',
            'Jenis', 'Nilai Rekom (Rp)', 'TL Selesai (Rp)', 'Sisa (Rp)',
            'Jml TL', 'Status', 'Progress', 'Batas Waktu',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 14, 'C' => 13, 'D' => 45,
            'E' => 14, 'F' => 20, 'G' => 18, 'H' => 18,
            'I' => 8,  'J' => 12, 'K' => 10, 'L' => 13,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = max(2, $this->lhp->temuans->flatMap->recommendations->count() + 1);

        $sheet->getStyle("A1:L1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        for ($row = 2; $row <= $lastRow; $row++) {
            $bg = ($row % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
        }

        $sheet->getStyle("F2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->freezePane('A2');

        return [];
    }
}