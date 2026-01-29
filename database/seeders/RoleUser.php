<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role sudah ada
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Cek user dulu, jika ada ambil, jika tidak buat baru
        $user = User::firstOrCreate(
            ['email' => 'admin123@gmail.com'], // kondisi unik
            [
                'name' => 'Admin',
                'password' => Hash::make('123456789')
            ]
        );

        // Assign role hanya jika belum ada
        if (!$user->hasRole('Admin')) {
            $user->assignRole($role);
        }
    }
}
