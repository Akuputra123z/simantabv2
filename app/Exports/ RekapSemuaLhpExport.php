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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapSemuaLhpExport implements WithMultipleSheets
{
    public function __construct(private Collection $lhps) {}

    public function sheets(): array
    {
        return [
            new RekapSemuaLhpSheet($this->lhps),
            new RekapStatistikSheet($this->lhps),
        ];
    }
}

// ── Sheet 1: Rekap Semua LHP ──────────────────────────────────────────────────

class RekapSemuaLhpSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(private Collection $lhps) {}

    public function title(): string { return 'Rekap LHP'; }

    public function collection(): Collection
    {
        return $this->lhps->map(function ($lhp, $i) {
            $stat = $lhp->statistik;
            return [
                'no'               => $i + 1,
                'nomor_lhp'        => $lhp->nomor_lhp,
                'program_audit'    => $lhp->auditAssignment?->auditProgram?->nama_program ?? '-',
                'unit_diperiksa'   => $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? '-',
                'tanggal_lhp'      => $lhp->tanggal_lhp?->format('d/m/Y') ?? '-',
                'semester'         => 'Semester ' . $lhp->semester,
                'irban'            => $lhp->irban,
                'status'           => ucfirst(str_replace('_', ' ', $lhp->status)),
                'total_temuan'     => $stat?->total_temuan ?? 0,
                'total_rekom'      => $stat?->total_rekomendasi ?? 0,
                'rekom_selesai'    => $stat?->rekom_selesai ?? 0,
                'rekom_proses'     => $stat?->rekom_proses ?? 0,
                'rekom_belum'      => $stat?->rekom_belum ?? 0,
                'total_kerugian'   => (float) ($stat?->total_kerugian ?? 0),
                'tl_selesai'       => (float) ($stat?->total_nilai_tl_selesai ?? 0),
                'sisa_kerugian'    => (float) ($stat?->total_sisa_kerugian ?? 0),
                'persen_selesai'   => round($stat?->persen_selesai_gabungan ?? 0, 1) . '%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No', 'Nomor LHP', 'Program Audit', 'Unit Diperiksa',
            'Tanggal LHP', 'Semester', 'IRBAN', 'Status',
            'Total Temuan', 'Total Rekom', 'Rekom Selesai', 'Rekom Proses', 'Rekom Belum',
            'Total Kerugian (Rp)', 'TL Selesai (Rp)', 'Sisa Kerugian (Rp)',
            'Progress TL (%)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 28, 'C' => 35, 'D' => 30,
            'E' => 13, 'F' => 12, 'G' => 12, 'H' => 14,
            'I' => 13, 'J' => 12, 'K' => 14, 'L' => 13, 'M' => 13,
            'N' => 22, 'O' => 20, 'P' => 20, 'Q' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->lhps->count() + 1;

        // Header baris
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows — stripe
        for ($row = 2; $row <= $lastRow; $row++) {
            $bgColor = ($row % 2 === 0) ? 'F0F9FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
        }

        // Kolom numerik — right align
        $numCols = ['I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];
        foreach ($numCols as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Format rupiah
        $sheet->getStyle("N2:P{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Freeze header
        $sheet->freezePane('A2');

        return [];
    }
}

// ── Sheet 2: Statistik Ringkasan ──────────────────────────────────────────────

class RekapStatistikSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(private Collection $lhps) {}

    public function title(): string { return 'Ringkasan Statistik'; }

    public function collection(): Collection
    {
        $total = [
            'total_lhp'      => $this->lhps->count(),
            'total_temuan'   => $this->lhps->sum(fn ($l) => $l->statistik?->total_temuan ?? 0),
            'total_rekom'    => $this->lhps->sum(fn ($l) => $l->statistik?->total_rekomendasi ?? 0),
            'rekom_selesai'  => $this->lhps->sum(fn ($l) => $l->statistik?->rekom_selesai ?? 0),
            'rekom_proses'   => $this->lhps->sum(fn ($l) => $l->statistik?->rekom_proses ?? 0),
            'rekom_belum'    => $this->lhps->sum(fn ($l) => $l->statistik?->rekom_belum ?? 0),
            'total_kerugian' => $this->lhps->sum(fn ($l) => $l->statistik?->total_kerugian ?? 0),
            'tl_selesai'     => $this->lhps->sum(fn ($l) => $l->statistik?->total_nilai_tl_selesai ?? 0),
            'sisa_kerugian'  => $this->lhps->sum(fn ($l) => $l->statistik?->total_sisa_kerugian ?? 0),
            'avg_persen'     => round($this->lhps->avg(fn ($l) => $l->statistik?->persen_selesai_gabungan ?? 0), 1),
        ];

        return collect([
            ['Total LHP',            $total['total_lhp'],      '-'],
            ['Total Temuan',         $total['total_temuan'],    '-'],
            ['Total Rekomendasi',    $total['total_rekom'],     '-'],
            ['Rekom Selesai',        $total['rekom_selesai'],   round($total['rekom_selesai'] / max($total['total_rekom'], 1) * 100, 1) . '%'],
            ['Rekom Proses',         $total['rekom_proses'],    round($total['rekom_proses'] / max($total['total_rekom'], 1) * 100, 1) . '%'],
            ['Rekom Belum TL',       $total['rekom_belum'],     round($total['rekom_belum'] / max($total['total_rekom'], 1) * 100, 1) . '%'],
            ['Total Kerugian (Rp)',  $total['total_kerugian'],  '-'],
            ['TL Selesai (Rp)',      $total['tl_selesai'],      round($total['tl_selesai'] / max($total['total_kerugian'], 1) * 100, 1) . '%'],
            ['Sisa Kerugian (Rp)',   $total['sisa_kerugian'],   round($total['sisa_kerugian'] / max($total['total_kerugian'], 1) * 100, 1) . '%'],
            ['Avg Progress TL',      $total['avg_persen'] . '%','-'],
        ]);
    }

    public function headings(): array
    {
        return ['Indikator', 'Nilai', 'Persentase'];
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 25, 'C' => 15];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:C11')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        ]);
        // Format rupiah untuk baris kerugian
        foreach ([7, 8, 9] as $row) {
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }
        return [];
    }
}