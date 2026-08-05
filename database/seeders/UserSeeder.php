<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@airalink.co.id',
                'password' => 'password',
                'status' => 1,
                'last_login' => Carbon::now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $user = User::firstOrCreate(
            ['username' => 'user'],
            [
                'email' => 'user@airalink.co.id',
                'password' => 'user123',
                'status' => 1,
                'last_login' => Carbon::now(),
            ]
        );
        $user->assignRole('user');
    }
}
