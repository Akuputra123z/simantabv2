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

class TemplateAuditDetailExport implements FromArray, WithColumnWidths, WithHeadings, WithStyles
{
    /**
     * Contoh data template sub-program
     */
    public function array(): array
    {
        return [
            [
                'Audit Ketaatan Pengelolaan Keuangan Desa', // Nama Detail Program
                'Audit Ketaatan',                          // Jenis Kegiatan
                'Pemerintah Desa',                         // Objek Pengawasan
                'Pengelolaan APBDes Semester I',           // Ruang Lingkup
                '10',                                      // Jumlah Personil
                'Memastikan akuntabilitas dan kepatuhan pengelolaan APBDes', // Tujuan Pengawasan
                5000000,                                   // Anggaran (Rp)
                'Tinggi',                                  // Tingkat Risiko
                '1 LHP',                                   // Target Laporan
                '01/01/2026',                              // Jadwal Pelaksanaan
                'Irban III',                               // Tim Pelaksana
                'aktif',                                   // Status (aktif/rencana)
            ],
            [
                'Reviu Laporan Keuangan Pemerintah Daerah',
                'Reviu',
                'BPKAD Kab. Rembang',
                'Laporan Realisasi Anggaran dan Neraca',
                '12',
                'Memberikan keyakinan terbatas atas penyusunan LKPD',
                3500000,
                'Sedang',
                '1 LHR',
                '01/02/2026',
                'Irban I',
                'rencana',
            ],
        ];
    }

    public function headings(): array
    {
        return [
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
            'Status (aktif/rencana)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 38,
            'B' => 18,
            'C' => 24,
            'D' => 30,
            'E' => 15,
            'F' => 45,
            'G' => 18,
            'H' => 15,
            'I' => 15,
            'J' => 20,
            'K' => 16,
            'L' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header Baris Pertama
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'], // Slate 800
            ],
        ]);

        $highestRow = $sheet->getHighestRow();

        // Border seluruh data
        $sheet->getStyle('A1:L'.$highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ]);

        // Format Anggaran
        $sheet->getStyle('G2:G'.$highestRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G2:G'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Center alignments
        foreach (['B', 'E', 'H', 'I', 'J', 'K', 'L'] as $col) {
            $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getRowDimension(1)->setRowHeight(24);
        for ($i = 2; $i <= $highestRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        return [];
    }
}
