<?php

namespace App\Exports;

use App\Models\AuditProgram;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DetailProgramExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $auditProgram;
    protected $details;

    public function __construct(AuditProgram $auditProgram, Collection $details)
    {
        $this->auditProgram = $auditProgram;
        $this->details = $details;
    }

    public function collection(): Collection
    {
        return $this->details->map(function ($d, $i) {
            return [
                'no'       => $i + 1,
                'nama'     => $d->nama_detail_program,
                'jenis'    => $d->jenis_kegiatan ?? '-',
                'objek'    => $d->objek_pengawasan ?? '-',
                'personil' => $d->personil ?? '-',
                'anggaran' => (float) $d->anggaran,
                'risiko'   => $d->tingkat_resiko ?? '-',
                'jadwal'   => \App\Helpers\DateHelper::formatJadwal($d->jadwal),
                'tim'      => $d->tim ?? '-',
                'status'   => strtoupper($d->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Sub-Program',
            'Jenis Kegiatan',
            'Objek Pengawasan',
            'Personil',
            'Anggaran (Rp)',
            'Tingkat Risiko',
            'Jadwal',
            'Tim',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $count = $this->details->count();
        $lastRow = $count + 1;

        // Header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold'   => true,
                'color'  => ['argb' => 'FFFFFFFF'],
                'size'   => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Border seluruh data
        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);

        // Alternating row colors
        for ($i = 2; $i <= $lastRow; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:J{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF8FAFC'],
                    ],
                ]);
            }
        }

        // Anggaran column (F) — right align + number format
        $sheet->getStyle("F2:F{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Center some columns
        foreach (['A', 'B', 'J'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Row height
        $sheet->getRowDimension(1)->setRowHeight(22);
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(18);
        }

        // Freeze header
        $sheet->freezePane('A2');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 10,
            'C' => 10,
            'D' => 24,
            'E' => 10,
            'F' => 24,
            'G' => 14,
            'H' => 16,
            'I' => 14,
            'J' => 10,
        ];
    }

    public function title(): string
    {
        return 'Sub-Program';
    }
}
