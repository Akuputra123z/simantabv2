<?php

namespace App\Imports;

use App\Models\AuditProgramDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AuditProgramDetailImport implements ToModel, WithHeadingRow
{
    private $auditProgramId;

    public function __construct($auditProgramId)
    {
        $this->auditProgramId = $auditProgramId;
    }

    public function model(array $row)
    {
        $anggaranRaw = $row['anggaran_angka'] ?? 0;
        $anggaranClean = preg_replace('/[^0-9]/', '', (string)$anggaranRaw);

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
            'jadwal'              => $row['jadwal_pelaksanaan'],
            'tim'                 => $row['tim_pelaksana'],
            'status'              => strtolower($row['status_aktifrencana'] ?? 'rencana'),
        ]);
    }
}