<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $user = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => bcrypt($row['password']),
            'NIS' => $row['nis'],
            'status' => 'siswa'
        ]);

        $user->assignRole('Murid');


        // Cari ID kelas
        $kelas = Classes::where('name_class', $row['kelas'])->first();

        // Tambahkan siswa ke kelas (teacher_classes = tabel pivot)
        if ($kelas) {
            \DB::table('teacher_classes')->insert([
                'user_id' => $user->id,
                'classes_id' => $row['classes_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }

    }
}
