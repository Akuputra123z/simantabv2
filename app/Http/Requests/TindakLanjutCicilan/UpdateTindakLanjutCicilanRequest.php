<?php

namespace App\Http\Requests\TindakLanjutCicilan;

use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTindakLanjutCicilanRequest extends FormRequest
{
    /**
     * Tentukan apakah user diperbolehkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        return [
            'nilai_bayar' => ['required', 'numeric', 'min:0.01'],
            'nilai_bayar_negara' => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_daerah' => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_desa' => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_bos_blud' => ['nullable', 'numeric', 'min:0'],
            'tanggal_bayar' => ['required', 'date'],
            'tanggal_jatuh_tempo_cicilan' => ['nullable', 'date'],
            'nomor_bukti' => ['nullable', 'string', 'max:100'],
            'jenis_bayar' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:menunggu_verifikasi,diterima,ditolak'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:15360'],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'nilai_bayar.required' => 'Nilai bayar wajib diisi.',
            'nilai_bayar.min' => 'Nilai bayar harus lebih dari 0.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];
    }

    /**
     * Validasi tambahan setelah aturan utama selesai.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $total = (float) ($this->nilai_bayar ?? 0);

            // Validasi sisa tindak lanjut
            $tl = $this->route('tindakLanjut');
            $cicilan = $this->route('cicilan');

            if ($tl instanceof TindakLanjut && $tl->recommendation?->isUang()) {
                $currentBayar = ($cicilan instanceof TindakLanjutCicilan && $cicilan->isDiterima())
                    ? (float) $cicilan->nilai_bayar
                    : 0;
                $maxAllowed = (float) ($tl->sisa_belum_bayar ?? 0) + $currentBayar;

                if ($total > $maxAllowed + 0.01) {
                    $validator->errors()->add(
                        'nilai_bayar',
                        'Nilai bayar melebihi sisa kewajiban yang dapat dibayar (Rp '
                            .number_format($maxAllowed, 0, ',', '.')
                            .').'
                    );
                }
            }

            // Menghitung total breakdown
            $breakdown = (float) ($this->nilai_bayar_negara ?? 0)
                       + (float) ($this->nilai_bayar_daerah ?? 0)
                       + (float) ($this->nilai_bayar_desa ?? 0)
                       + (float) ($this->nilai_bayar_bos_blud ?? 0);

            // Jika ada pengisian breakdown, maka totalnya harus cocok dengan nilai_bayar
            if ($breakdown > 0 && abs($breakdown - $total) > 0.01) {
                $validator->errors()->add(
                    'nilai_bayar_negara',
                    'Total rincian (negara + daerah + desa + BOS/BLUD) harus sama dengan total nilai bayar.'
                );
            }
        });
    }
}
