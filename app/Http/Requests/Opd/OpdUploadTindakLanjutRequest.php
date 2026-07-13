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
            'keterangan_pendukung' => ['nullable', 'string', 'max:5000'],
            'attachments'          => ['nullable', 'array', 'max:5'],
            'attachments.*'        => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.max'          => 'Maksimal 5 file lampiran.',
            'attachments.*.mimes'      => 'Lampiran harus berupa: pdf, jpg, png, doc, xls.',
            'attachments.*.max'        => 'Ukuran file maksimal 10MB.',
            'keterangan_pendukung.max' => 'Keterangan pendukung maksimal 5000 karakter.',
        ];
    }
}
