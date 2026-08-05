<?php

namespace App\Http\Requests\Konfigurasi;

use Illuminate\Foundation\Http\FormRequest;

class UsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user') ?? $this->get('id');

        $rules = [
            'username' => 'required|string|max:191|unique:sys_users,username,' . $id,
            'email' => 'required|email|max:191|unique:sys_users,email,' . $id,
            'roles' => 'required|array',
            'limit' => 'nullable|string',
        ];

        // Password required for new user or when changing
        if (!$id || $this->filled('password_lama')) {
            $rules['password'] = 'required|string|min:6|same:confirm_password';
            $rules['confirm_password'] = 'required|string|min:6';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'email' => 'E-Mail',
            'password' => 'Password Baru',
            'confirm_password' => 'Konfirmasi Password',
            'roles' => 'Hak Akses',
        ];
    }
}
