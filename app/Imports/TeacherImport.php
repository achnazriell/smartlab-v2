<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\TeacherSubjectAssignment;
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

    private $activeAcademicYear;

    public function __construct()
    {
        $this->activeAcademicYear = AcademicYear::active()->first();
    }

    public function collection(Collection $rows)
    {
        // Cek tahun ajaran aktif
        if (!$this->activeAcademicYear) {
            $this->errors[] = "Tahun ajaran aktif belum ditentukan. Import dibatalkan.";
            return;
        }

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
                    $this->skippedData[] = [
                        'nama' => $row['nama'] ?? '',
                        'email' => $row['email'] ?? '',
                        'nip' => $row['nip'] ?? null,
                        'reason' => 'Email sudah terdaftar',
                        'row' => $index + 2
                    ];
                    $this->importStats['skipped']++;
                    continue;
                }

                // Cek duplikasi NIP jika ada
                if (!empty($row['nip']) && Teacher::where('nip', $row['nip'])->exists()) {
                    $this->skippedData[] = [
                        'nama' => $row['nama'] ?? '',
                        'email' => $row['email'] ?? '',
                        'nip' => $row['nip'] ?? '',
                        'reason' => 'NIP sudah digunakan',
                        'row' => $index + 2
                    ];
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

                // 3. Proses kelas dan mata pelajaran (buat assignment)
                $this->processAssignments($teacher, $row, $index + 2);

                // Simpan data yang berhasil
                $this->successData[] = [
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'nip' => $row['nip'] ?? null,
                    'kelas' => $row['kelas'] ?? null,
                    'mapel' => $row['mapel'] ?? null,
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

    /**
     * Proses assignment guru ke kelas dan mapel
     */
    private function processAssignments(Teacher $teacher, $row, $rowNumber)
    {
        // Parsing kelas (bisa multiple dengan pemisah koma, titik koma, atau baris baru)
        $kelasList = [];
        if (!empty($row['kelas'])) {
            $kelasList = preg_split('/[,;\n]/', $row['kelas']);
            $kelasList = array_map('trim', $kelasList);
            $kelasList = array_filter($kelasList);
        }

        // Parsing mapel (bisa multiple dengan pemisah koma, titik koma, atau baris baru)
        $mapelList = [];
        if (!empty($row['mapel'])) {
            $mapelList = preg_split('/[,;\n]/', $row['mapel']);
            $mapelList = array_map('trim', $mapelList);
            $mapelList = array_filter($mapelList);
        }

        // Jika tidak ada kelas, tidak perlu buat assignment
        if (empty($kelasList)) {
            return;
        }

        // Untuk setiap kelas, cari atau buat kelas, lalu assign mapel
        foreach ($kelasList as $kelasName) {
            // Cari atau buat kelas
            $kelas = Classes::where('name_class', $kelasName)->first();
            if (!$kelas) {
                $kelas = Classes::create([
                    'name_class' => $kelasName,
                    'grade' => $this->extractGradeLevel($kelasName),
                    'status' => 'active',
                ]);
                $this->importStats['new_classes']++;
            }

            // Jika ada mapel, proses assignment
            if (!empty($mapelList)) {
                foreach ($mapelList as $mapelName) {
                    // Cari atau buat mapel
                    $subject = Subject::where('name_subject', $mapelName)->first();
                    if (!$subject) {
                        $subject = Subject::create([
                            'name_subject' => $mapelName,
                            'description' => 'Dibuat via import guru'
                        ]);
                        $this->importStats['new_subjects']++;
                    }

                    // Buat assignment di tahun ajaran aktif
                    TeacherSubjectAssignment::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'class_id' => $kelas->id,
                        'academic_year_id' => $this->activeAcademicYear->id,
                    ]);
                }
            }
        }
    }

    private function extractGradeLevel($kelasName)
    {
        $kelasName = strtolower($kelasName);
        if (preg_match('/\b(x|xi|xii|10|11|12)\b/i', $kelasName, $matches)) {
            $grade = strtoupper($matches[1]);
            if (is_numeric($grade)) {
                $gradeMap = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
                return $gradeMap[$grade] ?? $grade;
            }
            return $grade;
        }
        return 'X';
    }

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

    public function prepareForValidation($data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
            if ($key === 'password' && is_numeric($value)) {
                $data[$key] = (string) $value;
            }
        }

        if (isset($data['nip'])) {
            $data['nip'] = preg_replace('/\s+/', '', $data['nip']);
        }

        return $data;
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            $this->importStats['errors']++;
        }
    }

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
