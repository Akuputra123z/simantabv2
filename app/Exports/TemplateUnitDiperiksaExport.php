<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateUnitDiperiksaExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'Dinas Pendidikan, Pemuda dan Olahraga',
                'OPD',
                '',
                'Jl. Pemuda No. 1 Rembang',
                '08123456789',
                'SKPD Kabupaten Rembang',
            ],
            [
                'Desa Ronggomulyo',
                'Desa',
                'Sumber',
                'Jl. Raya Sumber No. 12',
                '08198765432',
                'Pemerintah Desa Ronggomulyo',
            ],
            [
                'SMPN 1 Sulang',
                'Sekolah',
                'Sulang',
                'Jl. Raya Sulang No. 5',
                '',
                'Satuan Pendidikan SMP Negeri',
            ],
            [
                'RSUD dr. R. Soetrasno',
                'BLUD',
                'Rembang',
                'Jl. P. Sudirman No. 16 Rembang',
                '08567891234',
                'Rumah Sakit Umum Daerah',
            ],
            [
                'PT BPR BKK Rembang (Perseroda)',
                'BUMD',
                'Rembang',
                'Jl. Cokroaminoto No. 3 Rembang',
                '08134567890',
                'Perusahaan Daerah Air Minum / Bank',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_unit',
            'kategori',
            'nama_kecamatan',
            'alamat',
            'telepon',
            'keterangan',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 18,
            'C' => 22,
            'D' => 40,
            'E' => 20,
            'F' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style Heading Row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'], // Blue 800
            ],
        ]);

        $highestRow = $sheet->getHighestRow();

        // Apply Borders and Font formatting
        $sheet->getStyle('A1:F' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align category and kecamatan
        $sheet->getStyle('B2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
