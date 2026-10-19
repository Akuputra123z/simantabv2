<?php

namespace App\Imports;

use App\Helpers\DateHelper;
use App\Models\AuditProgramDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AuditProgramDetailImport implements ToModel, WithHeadingRow
{
    private $auditProgramId;
    private int $importedCount = 0;

    private $timMapping = [
        'IRBAN I' => 'Irban I',
        'IRBAN II' => 'Irban II',
        'IRBAN III' => 'Irban III',
        'IRBAN IV' => 'Irban IV',
        'IRBAN V' => 'Irbansus',
        'IRBAN VI' => 'Irbansus',
        'IRBANSUS' => 'Irbansus',
    ];

    public function __construct($auditProgramId)
    {
        $this->auditProgramId = $auditProgramId;
    }

    public function model(array $row)
    {
        // 1. Ambil Nama Program (Dukung berbagai variasi heading: template maupun export)
        $nama = $row['nama_detail_program']
            ?? $row['nama_sub_program']
            ?? $row['nama_program']
            ?? $row['sub_program']
            ?? $row['nama']
            ?? null;

        // Jika nama kosong, baris diabaikan (baris kosong Excel)
        if (! $nama || trim((string) $nama) === '') {
            return null;
        }

        // 2. Jenis Kegiatan
        $jenis = $row['jenis_kegiatan']
            ?? $row['jenis']
            ?? $row['kegiatan']
            ?? 'Audit Ketaatan';

        // 3. Objek Pengawasan
        $objek = $row['objek_pengawasan']
            ?? $row['objek']
            ?? $row['unit_diperiksa']
            ?? $row['unit']
            ?? $row['auditee']
            ?? null;

        // 4. Ruang Lingkup
        $ruangLingkup = $row['ruang_lingkup']
            ?? $row['lingkup']
            ?? $row['lingkup_pengawasan']
            ?? null;

        // 5. Personil
        $personilRaw = $row['jumlah_personil']
            ?? $row['personil']
            ?? $row['jml_personil']
            ?? null;
        $personil = $personilRaw !== null ? trim((string) $personilRaw) : null;

        // 6. Tujuan
        $tujuan = $row['tujuan_pengawasan']
            ?? $row['tujuan']
            ?? $row['sasaran']
            ?? 'Pelaksanaan pengawasan dan penjaminan mutu sesuai program kerja.';

        // 7. Anggaran (Anti-desimal multiplication bug)
        $anggaranRaw = $row['anggaran_angka']
            ?? $row['anggaran_rp']
            ?? $row['anggaran']
            ?? $row['biaya']
            ?? 0;
        $anggaran = $this->parseAnggaran($anggaranRaw);

        // 8. Tingkat Risiko
        $risikoRaw = $row['tingkat_risiko']
            ?? $row['tingkat_resiko']
            ?? $row['risiko']
            ?? $row['resiko']
            ?? 'Sedang';
        $risiko = $this->parseRisiko($risikoRaw);

        // 9. Target Laporan / Laporan Akhir
        $targetLaporan = $row['target_laporan']
            ?? $row['laporan_akhir']
            ?? $row['target']
            ?? '1';

        // 10. Jadwal Pelaksanaan
        $jadwalRaw = $row['jadwal_pelaksanaan']
            ?? $row['jadwal']
            ?? $row['waktu']
            ?? null;
        $jadwal = $this->parseJadwal($jadwalRaw);

        // 11. Tim Pelaksana
        $timRaw = $row['tim_pelaksana']
            ?? $row['tim']
            ?? $row['irban']
            ?? null;
        $tim = $this->parseTim($timRaw);

        // 12. Status
        $statusRaw = $row['status_aktif_rencana']
            ?? $row['status_aktifrencana']
            ?? $row['status']
            ?? 'rencana';
        $status = $this->parseStatus($statusRaw);

        $this->importedCount++;

        return new AuditProgramDetail([
            'audit_program_id' => $this->auditProgramId,
            'nama_detail_program' => trim((string) $nama),
            'jenis_kegiatan' => trim((string) $jenis),
            'objek_pengawasan' => $objek ? trim((string) $objek) : null,
            'ruang_lingkup' => $ruangLingkup ? trim((string) $ruangLingkup) : null,
            'personil' => $personil,
            'tujuan' => trim((string) $tujuan),
            'anggaran' => $anggaran,
            'tingkat_resiko' => $risiko,
            'laporan_akhir' => trim((string) $targetLaporan),
            'jadwal' => $jadwal,
            'tim' => $tim,
            'status' => $status,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    private function parseAnggaran($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (empty($value)) {
            return 0.0;
        }

        $str = trim((string) $value);
        $str = preg_replace('/^(Rp|IDR)\s*/i', '', $str);

        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, '.') !== false && substr_count($str, '.') > 1) {
            $str = str_replace('.', '', $str);
        } elseif (strpos($str, ',') !== false && substr_count($str, ',') > 1) {
            $str = str_replace(',', '', $str);
        } elseif (strpos($str, ',') !== false) {
            $str = str_replace(',', '.', $str);
        }

        $str = preg_replace('/[^0-9.]/', '', $str);

        return is_numeric($str) ? (float) $str : 0.0;
    }

    private function parseRisiko($value): string
    {
        $val = strtolower(trim((string) $value));

        return match ($val) {
            'tinggi', 'high' => 'Tinggi',
            'rendah', 'low' => 'Rendah',
            default => 'Sedang',
        };
    }

    private function parseStatus($value): string
    {
        $status = strtolower(trim((string) $value));

        return in_array($status, ['aktif', 'rencana']) ? $status : 'rencana';
    }

    private function parseJadwal($value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((int) $value)->format('d/m/Y');
            } catch (\Throwable $e) {
                // Lanjut ke parser string
            }
        }

        $str = trim((string) $value);
        if ($str === '' || strcasecmp($str, 'tentatif') === 0 || $str === '-') {
            return null;
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $str)) {
            return $str;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $str)->format('d/m/Y');
            } catch (\Throwable $e) {
                // Abaikan
            }
        }

        $indoMonths = [
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march',
            'april' => 'april', 'mei' => 'may', 'juni' => 'june',
            'juli' => 'july', 'agustus' => 'august', 'september' => 'september',
            'oktober' => 'october', 'november' => 'november', 'desember' => 'december',
        ];
        $normalized = str_ireplace(array_keys($indoMonths), array_values($indoMonths), $str);

        try {
            return Carbon::parse($normalized)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $str;
        }
    }

    private function parseTim($value): ?string
    {
        if (! $value || trim((string) $value) === '' || trim((string) $value) === '-') {
            return null;
        }

        $trimmed = trim((string) $value);
        $upper = strtoupper($trimmed);

        if (isset($this->timMapping[$upper])) {
            return $this->timMapping[$upper];
        }

        $known = ['Irban I', 'Irban II', 'Irban III', 'Irban IV', 'Irbansus', 'Semua Irban', 'Sekretariat', 'Tim'];
        foreach ($known as $k) {
            if (strcasecmp($trimmed, $k) === 0) {
                return $k;
            }
        }

        return $trimmed;
    }
}
