<?php

namespace App\Exports;

use App\Helpers\DateHelper;
use App\Models\AuditProgram;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailProgramExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles, WithTitle
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
                'no' => $i + 1,
                'nama' => $d->nama_detail_program,
                'jenis' => $d->jenis_kegiatan ?? '-',
                'objek' => $d->objek_pengawasan ?? '-',
                'ruang_lingkup' => $d->ruang_lingkup ?? '-',
                'personil' => $d->personil ?? '-',
                'tujuan' => $d->tujuan ?? '-',
                'anggaran' => (float) $d->anggaran,
                'risiko' => $d->tingkat_resiko ?? 'Sedang',
                'target_laporan' => $d->laporan_akhir ?? '1',
                'jadwal' => $d->jadwal ? DateHelper::formatJadwal($d->jadwal) : '-',
                'tim' => $d->tim ?? '-',
                'status' => strtoupper($d->status ?? 'rencana'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Detail Program',
            'Jenis Kegiatan',
            'Objek Pengawasan',
            'Ruang Lingkup',
            'Jumlah Personil',
            'Tujuan Pengawasan',
            'Anggaran (Rp)',
            'Tingkat Risiko',
            'Target Laporan',
            'Jadwal Pelaksanaan',
            'Tim Pelaksana',
            'Status',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 38,
            'C' => 18,
            'D' => 24,
            'E' => 30,
            'F' => 15,
            'G' => 45,
            'H' => 18,
            'I' => 15,
            'J' => 15,
            'K' => 20,
            'L' => 16,
            'M' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $count = $this->details->count();
        $lastRow = $count + 1;

        // Header row
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'], // Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);

        if ($count > 0) {
            // Border seluruh data
            $sheet->getStyle("A1:M{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCBD5E1'],
                    ],
                ],
            ]);

            // Alternating row colors
            for ($i = 2; $i <= $lastRow; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle("A{$i}:M{$i}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8FAFC'],
                        ],
                    ]);
                }
            }

            // Anggaran column (H) — right align + number format
            $sheet->getStyle("H2:H{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Center specific columns
            foreach (['A', 'C', 'F', 'I', 'J', 'K', 'L', 'M'] as $col) {
                $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            for ($i = 2; $i <= $lastRow; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }
        }

        // Freeze header
        $sheet->freezePane('A2');

        return [];
    }

    public function title(): string
    {
        return 'Sub-Program PKPT';
    }
}
