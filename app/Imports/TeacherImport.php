<?php

namespace App\Imports;

use App\Models\TeacherClass;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Subject;
use App\Rules\ValidNIPGuru;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class TeacherImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $errors = [];
    private $importStats = [
        'success' => 0,
        'skipped' => 0,
        'errors' => 0,
        'new_classes' => 0,
        'new_subjects' => 0,
        'processed_rows' => 0
    ];
    private $successData = [];
    private $skippedData = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $this->importStats['processed_rows']++;

            try {
                // Validasi row
                $validator = Validator::make($row->toArray(), $this->rules());

                if ($validator->fails()) {
                    $errorMsg = "Baris " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                    $this->errors[] = $errorMsg;
                    $this->importStats['errors']++;
                    continue;
                }

                // Cek duplikasi email
                if (User::where('email', $row['email'])->exists()) {
                    $warningMsg = "Baris " . ($index + 2) . ": Email '{$row['email']}' sudah terdaftar";
                    $this->skippedData[] = [
                        'nama' => $row['nama'] ?? '',
                        'email' => $row['email'] ?? '',
                        'nip' => $row['nip'] ?? null,
                        'reason' => 'Email sudah terdaftar',
                        'row' => $index + 2
                    ];
                    $this->errors[] = $warningMsg;
                    $this->importStats['skipped']++;
                    continue;
                }

                // Cek duplikasi NIP jika ada
                if (!empty($row['nip']) && Teacher::where('nip', $row['nip'])->exists()) {
                    $warningMsg = "Baris " . ($index + 2) . ": NIP '{$row['nip']}' sudah digunakan";
                    $this->skippedData[] = [
                        'nama' => $row['nama'] ?? '',
                        'email' => $row['email'] ?? '',
                        'nip' => $row['nip'] ?? '',
                        'reason' => 'NIP sudah digunakan',
                        'row' => $index + 2
                    ];
                    $this->errors[] = $warningMsg;
                    $this->importStats['skipped']++;
                    continue;
                }

                // Generate password jika kosong
                $password = !empty($row['password']) ? $row['password'] : Str::random(8);

                // 1. Buat user
                $user = User::create([
                    'name'     => $row['nama'],
                    'email'    => $row['email'],
                    'password' => Hash::make($password),
                    'plain_password' => $password,
                ]);

                $user->assignRole('Guru');

                // 2. Buat teacher
                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'nip'     => $row['nip'] ?? null,
                ]);

                // 3. Proses kelas dan mata pelajaran
                $kelasArray = $this->processClassesAndSubjects($teacher, $row);

                // Simpan data yang berhasil
                $this->successData[] = [
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'nip' => $row['nip'] ?? null,
                    'kelas' => !empty($row['kelas']) ? $row['kelas'] : null,
                    'mapel' => !empty($row['mapel']) ? $row['mapel'] : null,
                    'password' => $password,
                    'row' => $index + 2
                ];

                $this->importStats['success']++;
            } catch (\Exception $e) {
                $errorMsg = "Baris " . ($index + 2) . ": " . $e->getMessage();
                $this->errors[] = $errorMsg;
                $this->importStats['errors']++;
            }
        }
    }

    private function processClassesAndSubjects(Teacher $teacher, $row)
    {
        // Proses kelas (bisa single atau multiple dengan pemisah koma)
        $kelasArray = [];
        if (!empty($row['kelas'])) {
            // Pisah dengan koma, titik koma, atau baris baru
            $kelasArray = preg_split('/[,;\n]/', $row['kelas']);
            $kelasArray = array_map('trim', $kelasArray);
            $kelasArray = array_filter($kelasArray);
        }

        // Proses mata pelajaran (bisa single atau multiple dengan pemisah koma)
        $mapelArray = [];
        if (!empty($row['mapel'])) {
            $mapelArray = preg_split('/[,;\n]/', $row['mapel']);
            $mapelArray = array_map('trim', $mapelArray);
            $mapelArray = array_filter($mapelArray);
        }

        // Jika ada kelas
        if (!empty($kelasArray)) {
            foreach ($kelasArray as $kelasName) {
                // Cari atau buat kelas baru
                $kelas = Classes::where('name_class', $kelasName)->first();

                if (!$kelas) {
                    $kelas = Classes::create([
                        'name_class' => $kelasName,
                        'description' => 'Dibuat via import guru'
                    ]);
                    $this->importStats['new_classes']++;
                }

                // Buat relasi teacher-class
                $teacherClass = TeacherClass::create([
                    'teacher_id' => $teacher->id,
                    'classes_id' => $kelas->id,
                ]);

                // Jika ada mata pelajaran untuk kelas ini
                if (!empty($mapelArray)) {
                    $subjectIds = [];

                    foreach ($mapelArray as $mapelName) {
                        // Cari atau buat mata pelajaran baru
                        $subject = Subject::where('name_subject', $mapelName)->first();

                        if (!$subject) {
                            $subject = Subject::create([
                                'name_subject' => $mapelName,
                                'description' => 'Dibuat via import guru'
                            ]);
                            $this->importStats['new_subjects']++;
                        }

                        $subjectIds[] = $subject->id;
                    }

                    // Attach mata pelajaran ke teacher_class
                    if (!empty($subjectIds)) {
                        $teacherClass->subjects()->sync($subjectIds);
                    }
                }
            }
        }

        return $kelasArray;
    }

    /**
     * Validasi data dari Excel
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'nip' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $validator = new ValidNIPGuru();
                        if (!$validator->passes($attribute, $value)) {
                            $fail($validator->message());
                        }
                    }
                },
            ],
            'kelas' => ['nullable', 'string'],
            'mapel' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama guru wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.min' => 'Password minimal 6 karakter',
            'kelas.string' => 'Format kelas tidak valid',
            'mapel.string' => 'Format mata pelajaran tidak valid',
        ];
    }

    /**
     * Prepare data sebelum validasi
     */
    /**
     * Prepare data sebelum validasi
     */
    public function prepareForValidation($data)
    {
        // Bersihkan spasi
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
            // Jika password adalah angka, konversi ke string
            if ($key === 'password' && is_numeric($value)) {
                $data[$key] = (string) $value;
            }
        }

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
            $this->importStats['errors']++;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get import statistics
     */
    public function getImportStats()
    {
        return [
            'success' => $this->importStats['success'],
            'skipped' => $this->importStats['skipped'],
            'errors' => $this->importStats['errors'],
            'new_classes' => $this->importStats['new_classes'],
            'new_subjects' => $this->importStats['new_subjects'],
            'processed_rows' => $this->importStats['processed_rows'],
            'success_data' => $this->successData,
            'skipped_data' => $this->skippedData,
            'error_list' => $this->errors
        ];
    }
}
