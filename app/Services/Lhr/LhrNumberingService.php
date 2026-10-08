<?php

namespace App\Services\Lhr;

use App\Models\NomorSequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LhrNumberingService
{
    public const JENIS_LHR = 'lhr';

    public const JENIS_LHTL = 'lhtl';

    public const JENIS_SPT_REVIU = 'spt_reviu';

    public const JENIS_SPT_TL = 'spt_tl';

    public const DEFAULT_FORMATS = [
        self::JENIS_LHR => '700/{urut:3}{.sufiks}/REVIU/INSP/{tahun}',
        self::JENIS_LHTL => '700/{urut:3}/REVIU/INSP/{tahun}',
        self::JENIS_SPT_REVIU => '000.1.2.3/{urut:3}/REVIU/INSP/{tahun}',
        self::JENIS_SPT_TL => '000.1.2.3/{urut:3}/REVIU/INSP/{tahun}',
    ];

    /**
     * Alokasikan nomor surat berikutnya secara atomik dalam transaksi database.
     *
     * @param  string  $jenis  Jenis nomor (lhr, lhtl, spt_reviu, spt_tl)
     * @param  int|null  $tahun  Tahun penomoran (default tahun berjalan)
     * @param  string  $sufiks  Sufiks opsional (mis. 'a' untuk '093.a')
     * @return array{nomor: string, nomor_urut: int, tahun: int, sufiks: string}
     */
    public function allocate(string $jenis, ?int $tahun = null, string $sufiks = ''): array
    {
        $tahun = $tahun ?? (int) date('Y');

        return DB::transaction(function () use ($jenis, $tahun, $sufiks) {
            $format = self::DEFAULT_FORMATS[$jenis] ?? null;

            if (! $format) {
                throw new InvalidArgumentException("Format penomoran untuk jenis '{$jenis}' tidak ditemukan.");
            }

            // Lock sequence record untuk tahun dan jenis ini
            $sequence = NomorSequence::where('jenis', $jenis)
                ->where('tahun', $tahun)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = NomorSequence::create([
                    'jenis' => $jenis,
                    'tahun' => $tahun,
                    'nomor_terakhir' => 0,
                ]);
                // Re-select with lock
                $sequence = NomorSequence::where('id', $sequence->id)
                    ->lockForUpdate()
                    ->first();
            }

            $nomorUrut = $sequence->nomor_terakhir + 1;
            $sequence->update(['nomor_terakhir' => $nomorUrut]);

            $nomorFormatted = $this->formatNomor($format, $nomorUrut, $tahun, $sufiks);

            return [
                'nomor' => $nomorFormatted,
                'nomor_urut' => $nomorUrut,
                'tahun' => $tahun,
                'sufiks' => $sufiks,
            ];
        });
    }

    /**
     * Render string nomor dari pola format.
     */
    public function formatNomor(string $pola, int $nomorUrut, int $tahun, string $sufiks = ''): string
    {
        $dotSufiks = ($sufiks !== '') ? ('.'.ltrim($sufiks, '.')) : '';

        $replacements = [
            '{urut:3}' => str_pad((string) $nomorUrut, 3, '0', STR_PAD_LEFT),
            '{urut:4}' => str_pad((string) $nomorUrut, 4, '0', STR_PAD_LEFT),
            '{urut}' => (string) $nomorUrut,
            '{tahun}' => (string) $tahun,
            '{.sufiks}' => $dotSufiks,
            '{sufiks}' => $sufiks,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $pola);
    }

    /**
     * Dapatkan saran nomor LHR berikutnya yang dijamin unik dan belum terpakai di database.
     */
    public function getNextSuggestedNomor(string $jenis = self::JENIS_LHR, ?int $tahun = null, string $sufiks = ''): string
    {
        $tahun = $tahun ?? (int) date('Y');
        $format = self::DEFAULT_FORMATS[$jenis] ?? '700/{urut:3}{.sufiks}/REVIU/INSP/{tahun}';

        $seq = NomorSequence::where('jenis', $jenis)->where('tahun', $tahun)->value('nomor_terakhir') ?? 0;

        $existingNomors = DB::table('lhrs')->whereNotNull('nomor_lhr')->pluck('nomor_lhr');
        $maxFromDb = 0;
        foreach ($existingNomors as $nomor) {
            if (preg_match('/(?:700|INSP|REVIU)\/(\d{1,4})/i', $nomor, $matches)) {
                $maxFromDb = max($maxFromDb, (int) $matches[1]);
            } elseif (preg_match('/\/(\d{1,4})\//', $nomor, $matches)) {
                $maxFromDb = max($maxFromDb, (int) $matches[1]);
            }
        }

        $candidateUrut = max($seq, $maxFromDb) + 1;

        while (true) {
            $formatted = $this->formatNomor($format, $candidateUrut, $tahun, $sufiks);
            if (! DB::table('lhrs')->where('nomor_lhr', $formatted)->exists()) {
                return $formatted;
            }
            $candidateUrut++;
        }
    }
}
