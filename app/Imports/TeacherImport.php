<?php

namespace App\Imports;

use App\Models\TeacherClass;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeacherImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Buat user
        $user = User::create([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password']),
        ]);

        // 2. Beri role guru
        $user->assignRole('Guru');

        // 3. Cari kelas & mapel berdasarkan nama
        $kelas = Classes::where('name_class', $row['kelas'])->first();
        $mapel = Subject::where('name_subject', $row['mapel'])->first();

        // 4. Insert ke table teachers
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'NIP'     => $row['nip'],
        ]);

        // 5. Insert ke tabel relasi teacher_class
        if ($kelas || $mapel) {
            TeacherClass::create([
                'user_id'   => $user->id,
                'kelas_id'  => $kelas ? $kelas->id : null,
                'subject_id'=> $mapel ? $mapel->id : null,
            ]);
        }

        return $teacher;
    }
}
