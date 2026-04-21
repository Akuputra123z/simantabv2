<?php

namespace App\Http\Requests\TindakLanjutCicilan;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiCicilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'             => ['required', 'in:diterima,ditolak'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status verifikasi wajib dipilih.',
            'status.in'       => 'Status harus diterima atau ditolak.',
        ];
    }
}