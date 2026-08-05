<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('pegawai') ?? $this->get('id');

        return [
            'nama' => 'required|string|max:191|unique:ref_pegawais,nama,' . $id,
            'telepon' => 'nullable|string|max:15',
            'status' => 'nullable|integer|in:0,1',
            'keterangan' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama Pegawai',
            'telepon' => 'Telepon',
            'status' => 'Status',
        ];
    }
}
