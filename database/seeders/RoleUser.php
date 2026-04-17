<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUser extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin123@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456789'),
                'role' => 'Admin', 
            ]
        );

        // Assign role Spatie jika diperlukan
        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }
    }
}
