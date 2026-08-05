<?php

namespace App\Http\Requests\Konfigurasi;

use Illuminate\Foundation\Http\FormRequest;

class RolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('role') ?? $this->get('id');

        return [
            'name' => 'required|string|max:191|unique:roles,name,' . $id,
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Role',
        ];
    }
}
