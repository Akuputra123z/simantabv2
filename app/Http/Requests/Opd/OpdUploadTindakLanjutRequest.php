<?php

namespace App\Http\Requests\Opd;

use Illuminate\Foundation\Http\FormRequest;

class OpdUploadTindakLanjutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('opd') ?? false;
    }

    public function rules(): array
    {
        return [
            'jenis_penyelesaian'   => ['nullable', 'string', 'in:setor_kas,cicilan,pengembalian_barang,perbaikan_administrasi'],
            'nilai_tindak_lanjut'  => ['nullable', 'numeric', 'min:0'],
            'nomor_bukti'          => ['nullable', 'string', 'max:150'],
            'tanggal_bayar'        => ['nullable', 'date'],
            'keterangan_pendukung' => ['nullable', 'string', 'max:5000'],
            'attachments'          => ['nullable', 'array', 'max:5'],
            'attachments.*'        => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_penyelesaian.in'    => 'Jenis penyelesaian tidak valid.',
            'nilai_tindak_lanjut.min'  => 'Nilai tindak lanjut minimal Rp 0.',
            'nilai_tindak_lanjut.numeric' => 'Nilai tindak lanjut harus berupa angka.',
            'nomor_bukti.max'          => 'Nomor bukti maksimal 150 karakter.',
            'tanggal_bayar.date'       => 'Format tanggal pembayaran tidak valid.',
            'attachments.max'          => 'Maksimal 5 file lampiran.',
            'attachments.*.mimes'      => 'Lampiran harus berupa: pdf, jpg, png, doc, xls.',
            'attachments.*.max'        => 'Ukuran file maksimal 10MB.',
            'keterangan_pendukung.max' => 'Keterangan pendukung maksimal 5000 karakter.',
        ];
    }
}
