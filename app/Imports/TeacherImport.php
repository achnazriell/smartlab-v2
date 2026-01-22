<?php

namespace App\Imports;

use App\Models\TeacherClass;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Subject;
use App\Rules\ValidNIPGuru;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class TeacherImport implements ToModel, WithHeadingRow, WithValidation
{
    private $errors = [];

    public function model(array $row)
    {
        // Validasi NIP sebelum proses
        $nipValidator = new ValidNIPGuru();
        if ($row['nip'] && !$nipValidator->passes('NIP', $row['nip'])) {
            throw new \Exception($nipValidator->message());
        }

        // 1. Validasi email unik
        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("Email {$row['email']} sudah terdaftar");
        }

        // 2. Validasi NIP unik
        if ($row['nip'] && Teacher::where('NIP', $row['nip'])->exists()) {
            throw new \Exception("NIP {$row['nip']} sudah digunakan");
        }

        // 3. Buat user
        $user = User::create([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password']),
            'plain_password' => $row['password'], // simpan password plain
            'status'   => 'guru',
        ]);

        // 4. Beri role guru
        $user->assignRole('Guru');

        // 5. Insert ke table teachers
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'NIP'     => $row['nip'] ?? null,
        ]);

        // 6. Cari kelas & mapel berdasarkan nama
        $kelas = Classes::where('name', $row['kelas'])->first();
        $mapel = Subject::where('name', $row['mapel'])->first();

        // 7. Insert ke tabel relasi teacher_class jika ada kelas
        if ($kelas) {
            $teacherClass = TeacherClass::create([
                'teacher_id' => $teacher->id,
                'classes_id' => $kelas->id,
            ]);

            // Attach mapel jika ada
            if ($mapel) {
                $teacherClass->subjects()->attach($mapel->id);
            }
        }

        return $teacher;
    }

    /**
     * Validasi data dari Excel
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'nip' => [
                'nullable',
                'string',
                'size:18', // harus 18 digit
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $validator = new ValidNIPGuru();
                        if (!$validator->passes($attribute, $value)) {
                            $fail($validator->message());
                        }
                    }
                },
            ],
            'kelas' => ['required', 'string'],
            'mapel' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Nama guru wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'nip.size' => 'NIP harus 18 digit',
            'kelas.required' => 'Nama kelas wajib diisi',
            'kelas.exists' => 'Kelas tidak ditemukan di database',
            'mapel.exists' => 'Mata pelajaran tidak ditemukan di database',
        ];
    }

    /**
     * Prepare data sebelum validasi
     */
    public function prepareForValidation($data)
    {
        // Bersihkan spasi di NIP
        if (isset($data['nip'])) {
            $data['nip'] = preg_replace('/\s+/', '', $data['nip']);
        }

        return $data;
    }

    /**
     * Handle kegagalan
     */
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
