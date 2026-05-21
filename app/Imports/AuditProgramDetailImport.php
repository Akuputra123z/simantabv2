<?php

namespace App\Imports;

use App\Helpers\DateHelper;
use App\Models\AuditProgramDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AuditProgramDetailImport implements ToModel, WithHeadingRow
{
    private $auditProgramId;

    private $timMapping = [
        'IRBAN I'    => 'Irban I',
        'IRBAN II'   => 'Irban II',
        'IRBAN III'  => 'Irban III',
        'IRBAN IV'   => 'Irban IV',
        'IRBAN V'    => 'Irbansus',
        'IRBAN VI'   => 'Irbansus',
        'IRBANSUS'   => 'Irbansus',
    ];

    public function __construct($auditProgramId)
    {
        $this->auditProgramId = $auditProgramId;
    }

    public function model(array $row)
    {
        if (!isset($row['nama_detail_program']) || trim((string)$row['nama_detail_program']) === '') {
            return null;
        }

        $anggaranRaw = $row['anggaran_angka'] ?? 0;
        $anggaranClean = preg_replace('/[^0-9]/', '', (string)$anggaranRaw);

        $jadwal = $this->parseJadwal($row['jadwal_pelaksanaan'] ?? null);
        $tim = $this->parseTim($row['tim_pelaksana'] ?? null);

        return new AuditProgramDetail([
            'audit_program_id'    => $this->auditProgramId,
            'nama_detail_program' => $row['nama_detail_program'],
            'jenis_kegiatan'      => $row['jenis_kegiatan'],
            'objek_pengawasan'    => $row['objek_pengawasan'],
            'ruang_lingkup'       => $row['ruang_lingkup'],
            'personil'            => (int) ($row['jumlah_personil'] ?? 0),
            'tujuan'              => $row['tujuan_pengawasan'],
            'anggaran'            => (float) ($anggaranClean ?: 0),
            'tingkat_resiko'      => ucfirst(strtolower($row['tingkat_risiko'] ?? 'Sedang')),
            'laporan_akhir'       => (int) ($row['target_laporan'] ?? 1),
            'jadwal'              => $jadwal,
            'tim'                 => $tim,
            'status'              => strtolower($row['status_aktifrencana'] ?? 'rencana'),
        ]);
    }

    private function parseJadwal($value): ?string
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((int) $value)->format('d/m/Y');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return DateHelper::toStorage($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTim($value): ?string
    {
        if (!$value) return null;

        $upper = strtoupper(trim($value));
        if (isset($this->timMapping[$upper])) {
            return $this->timMapping[$upper];
        }

        $known = ['Irban I', 'Irban II', 'Irban III', 'Irban IV', 'Irbansus', 'Semua Irban', 'Sekretariat', 'Tim'];
        foreach ($known as $k) {
            if (strcasecmp(trim($value), $k) === 0) {
                return $k;
            }
        }

        return trim($value);
    }
}
