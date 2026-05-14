<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TemplateAuditDetailExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * Sesuaikan urutan data dengan $fillable di Model (Tanpa audit_program_id)
     */
    public function array(): array
    {
        return [
            [
                'Audit Ketaatan Pengelolaan Keuangan Desa', // nama_detail_program
                'Audit Ketaatan',                          // jenis_kegiatan
                'Pemerintah Desa',                         // objek_pengawasan
                'Pengelolaan APBDes Semester I',           // ruang_lingkup
                10,                                        // personil
                'Memastikan akuntabilitas keuangan desa',  // tujuan
                5000000,                                   // anggaran
                'Tinggi',                                  // tingkat_resiko
                1,                                         // laporan_akhir
                'Januari 2026',                            // jadwal
                'Irban III',                               // tim
                'aktif'                                    // status
            ],
            [
                'Reviu Laporan Keuangan Daerah',
                'Reviu',
                'BPKAD',
                'Laporan Realisasi Anggaran',
                12,
                'Memberikan keyakinan terbatas atas laporan',
                3500000,
                'Sedang',
                1,
                'Februari 2026',
                'Irban I',
                'rencana'
            ],
        ];
    }

    /**
     * Judul kolom yang human-readable namun urutannya sama dengan array()
     */
    public function headings(): array
    {
        return [
            'Nama Detail Program',
            'Jenis Kegiatan',
            'Objek Pengawasan',
            'Ruang Lingkup',
            'Jumlah Personil',
            'Tujuan Pengawasan',
            'Anggaran (Angka)',
            'Tingkat Risiko',
            'Target Laporan',
            'Jadwal Pelaksanaan',
            'Tim Pelaksana',
            'Status (aktif/rencana)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40, 'B' => 20, 'C' => 25, 'D' => 30, 
            'E' => 15, 'F' => 45, 'G' => 20, 'H' => 15, 
            'I' => 15, 'J' => 20, 'K' => 15, 'L' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header Baris Pertama
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e293b'] // Slate 800
            ],
        ]);

        // Border dan Alignment untuk data
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:L' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'cbd5e1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        return [
            1 => ['font' => ['size' => 11]],
        ];
    }
}