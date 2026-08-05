<?php

namespace App\Http\Requests\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('POST')) {
            return [
                'kode_bank' => 'required|string|max:10',
                'no_rekening' => 'required|string|max:30',
                'atas_nama' => 'required|string|max:100',
                'nominal' => 'required|numeric|min:1',
                'admin' => 'required|numeric|min:0',
                'cabang_toko' => 'nullable|string|max:100',
                'keterangan' => 'required|string',
                'whatsapp' => 'nullable|string|max:20',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ];
        }

        return [
            'kode_bank' => 'nullable|string|max:10',
            'no_rekening' => 'nullable|string|max:30',
            'atas_nama' => 'nullable|string|max:100',
            'nominal' => 'nullable|numeric|min:1',
            'admin' => 'nullable|numeric|min:0',
            'cabang_toko' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'whatsapp' => 'nullable|string|max:20',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_bank' => 'Kode Bank',
            'no_rekening' => 'Nomor Rekening',
            'atas_nama' => 'Atas Nama',
            'nominal' => 'Nominal',
            'keterangan' => 'Keterangan',
        ];
    }
}
