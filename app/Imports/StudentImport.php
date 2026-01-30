<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class StudentImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Cek apakah email sudah ada
        $existingUser = User::where('email', $row['email'])->first();
        if ($existingUser) {
            return null; // Skip jika sudah ada
        }

        // Buat user baru
        $user = User::create([
            'name' => $row['nama'] ?? $row['name'],
            'email' => $row['email'],
            'password' => bcrypt($row['password']),
            'plain_password' => $row['password'],
        ]);

        // Assign role
        $user->assignRole('Murid');

        // Cari kelas berdasarkan nama
        $kelas = Classes::where('name_class', $row['kelas'])->first();

        // Buat record student
        Student::create([
            'user_id' => $user->id,
            'nis' => $row['nis'],
            'class_id' => $kelas ? $kelas->id : null,
            'status' => 'siswa',
        ]);

        return null;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => 'required|regex:/^[0-9]{6,10}$/',
            'kelas' => 'required',
        ];
    }
}
