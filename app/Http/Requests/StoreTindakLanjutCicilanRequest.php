<?php

namespace App\Http\Requests\TindakLanjutCicilan;

use Illuminate\Foundation\Http\FormRequest;

class StoreTindakLanjutCicilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan ini true jika Anda belum mengimplementasikan logika permission khusus
        return true;
    }

    public function rules(): array
    {
        return [
            'nilai_bayar'                 => ['required', 'numeric', 'min:0.01'],
            'nilai_bayar_negara'          => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_daerah'          => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_desa'            => ['nullable', 'numeric', 'min:0'],
            'nilai_bayar_bos_blud'        => ['nullable', 'numeric', 'min:0'],
            'tanggal_bayar'               => ['required', 'date'],
            'tanggal_jatuh_tempo_cicilan' => ['nullable', 'date'],
            'nomor_bukti'                 => ['nullable', 'string', 'max:100'],
            'jenis_bayar'                 => ['nullable', 'string', 'max:100'],
            'keterangan'                  => ['nullable', 'string', 'max:1000'],
            'status'                      => ['required', 'in:menunggu_verifikasi,diterima,ditolak'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai_bayar.required' => 'Nilai bayar wajib diisi.',
            'nilai_bayar.min'      => 'Nilai bayar harus lebih dari 0.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'status.in'            => 'Status tidak valid.',
        ];
    }

    /**
     * Validasi tambahan: jumlah breakdown harus sama dengan nilai_bayar.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $breakdown = (float) ($this->nilai_bayar_negara   ?? 0)
                       + (float) ($this->nilai_bayar_daerah   ?? 0)
                       + (float) ($this->nilai_bayar_desa     ?? 0)
                       + (float) ($this->nilai_bayar_bos_blud ?? 0);

            $total = (float) ($this->nilai_bayar ?? 0);

            // Jika ada nilai breakdown yang diisi, jumlahnya harus cocok dengan total nilai_bayar
            if ($breakdown > 0 && abs($breakdown - $total) > 0.01) {
                $validator->errors()->add(
                    'nilai_bayar_negara',
                    'Total breakdown (negara + daerah + desa + BOS/BLUD) harus sama dengan nilai bayar.'
                );
            }
        });
    }
}