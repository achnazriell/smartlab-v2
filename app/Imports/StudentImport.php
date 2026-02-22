<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\StudentClassAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class StudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $duplicateCount = 0;
    private $rowNumber = 1;
    private $importErrors = [];
    private $importWarnings = [];
    private $createdClasses = [];
    private $successData = [];
    private $duplicateData = [];

    // Ambil tahun ajaran aktif sekali di konstruktor
    private $activeAcademicYear;

    public function __construct()
    {
        $this->activeAcademicYear = AcademicYear::active()->first();
    }

    public function model(array $row)
    {
        $currentRow = $this->rowNumber++;

        // Cek tahun ajaran aktif
        if (!$this->activeAcademicYear) {
            $this->importErrors[] = "Tahun ajaran aktif belum ditentukan. Import dibatalkan.";
            return null;
        }

        // Ambil data dari berbagai kemungkinan nama kolom
        $nama = $this->getValue($row, ['nama_lengkap', 'nama', 'name', 'nama siswa', 'nama_lengkap_siswa', 'nama_siswa']);
        $email = $this->getValue($row, ['email', 'email_address', 'email_siswa', 'email_siswa', 'e_mail']);
        $nis = $this->getValue($row, ['nis', 'nomor_induk', 'nomor_induk_siswa', 'no_induk', 'nomor_induk']);
        $kelas = $this->getValue($row, ['kelas', 'class', 'nama_kelas', 'kelas_siswa', 'tingkat_kelas']);
        $password = $this->getValue($row, ['password', 'kata_sandi', 'sandi', 'pass']);

        // Konversi tipe data
        if (is_numeric($nis)) {
            $nis = (string)(int)$nis;
        }
        if (is_numeric($password)) {
            $password = (string)(int)$password;
        }
        if (is_numeric($kelas)) {
            $kelas = (string)$kelas;
        }

        // Skip baris kosong
        if (empty($nama) && empty($email) && empty($nis)) {
            $this->skippedCount++;
            return null;
        }

        // Validasi data minimal
        if (empty($nama) || empty($email) || empty($nis)) {
            $missing = [];
            if (empty($nama)) $missing[] = 'nama';
            if (empty($email)) $missing[] = 'email';
            if (empty($nis)) $missing[] = 'nis';
            $errorMsg = "Baris {$currentRow}: Data tidak lengkap (" . implode(', ', $missing) . ")";
            $this->importErrors[] = $errorMsg;
            $this->skippedCount++;
            return null;
        }

        // Normalize data
        $nama = trim($nama);
        $email = trim(strtolower($email));
        $nis = trim($nis);
        $kelas = !empty($kelas) ? trim($kelas) : null;
        $password = !empty($password) ? trim($password) : null;

        // Cek duplikat email
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->duplicateCount++;
            $this->duplicateData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'kelas' => $kelas,
                'reason' => 'Email sudah terdaftar'
            ];
            $this->skippedCount++;
            return null;
        }

        // Cek duplikat NIS
        $existingNIS = Student::where('nis', $nis)->first();
        if ($existingNIS) {
            $this->duplicateCount++;
            $this->duplicateData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'kelas' => $kelas,
                'reason' => 'NIS sudah terdaftar'
            ];
            $this->skippedCount++;
            return null;
        }

        DB::beginTransaction();
        try {
            // Generate password jika kosong
            if (empty($password)) {
                $password = $this->generateDefaultPassword($nis);
            }

            // 1. Buat user
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
                'plain_password' => $password,
            ]);
            $user->assignRole('Murid');

            // 2. Buat student
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $nis,
                'status' => 'siswa',
            ]);

            // 3. Proses kelas (jika ada)
            $classId = null;
            $className = null;
            if (!empty($kelas)) {
                $classId = $this->processClass($kelas, $currentRow);
                $className = $kelas;

                if ($classId) {
                    // Generate student_code jika kelas dipilih
                    $studentCode = Student::generateStudentCode($classId, $this->activeAcademicYear->id);
                    $student->student_code = $studentCode;
                    $student->save();

                    // Buat assignment kelas
                    StudentClassAssignment::create([
                        'student_id' => $student->id,
                        'class_id' => $classId,
                        'academic_year_id' => $this->activeAcademicYear->id,
                    ]);
                }
            }

            DB::commit();

            // Catat data berhasil
            $this->successData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'kelas' => $className,
                'row' => $currentRow
            ];
            $this->importedCount++;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->importErrors[] = "Baris {$currentRow}: " . $e->getMessage();
            $this->skippedCount++;
        }

        return null; // karena kita handle sendiri, tidak perlu return model
    }

    /**
     * Proses kelas - cari atau buat baru
     */
    private function processClass($kelasName, $rowNumber)
    {
        $kelasName = trim($kelasName);
        $kelasName = preg_replace('/\s+/', ' ', $kelasName);

        // Cek apakah kelas sudah ada
        $kelasModel = Classes::where('name_class', $kelasName)->first();
        if ($kelasModel) {
            return $kelasModel->id;
        }

        // Coba case-insensitive
        $kelasModel = Classes::whereRaw('LOWER(name_class) = ?', [strtolower($kelasName)])->first();
        if ($kelasModel) {
            // Update nama ke format yang benar jika perlu
            $kelasModel->update(['name_class' => $kelasName]);
            return $kelasModel->id;
        }

        // Buat kelas baru
        try {
            $newClass = Classes::create([
                'name_class' => $kelasName,
                'grade' => $this->extractGradeLevel($kelasName),
                'status' => 'active'
            ]);

            $this->createdClasses[$kelasName] = ($this->createdClasses[$kelasName] ?? 0) + 1;
            $this->importWarnings[] = "Baris {$rowNumber}: Kelas '{$kelasName}' dibuat otomatis";

            return $newClass->id;
        } catch (\Exception $e) {
            $this->importWarnings[] = "Baris {$rowNumber}: Gagal membuat kelas '{$kelasName}' - " . $e->getMessage();
            return null;
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

    private function getValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            $variations = [
                $key,
                strtolower($key),
                strtolower(str_replace('_', ' ', $key)),
                strtolower(str_replace(' ', '_', $key)),
                ucwords(strtolower(str_replace('_', ' ', $key))),
                str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $key))))
            ];
            foreach ($variations as $variation) {
                if (isset($row[$variation]) && !empty($row[$variation])) {
                    return $row[$variation];
                }
            }
        }
        return null;
    }

    public function rules(): array
    {
        return [
            '*.nama_lengkap' => 'nullable|string|max:255',
            '*.nama' => 'nullable|string|max:255',
            '*.name' => 'nullable|string|max:255',
            '*.email' => 'nullable|email',
            '*.nis' => 'nullable',
            '*.password' => 'nullable',
            '*.kelas' => 'nullable|string',
        ];
    }

    private function generateDefaultPassword($nis)
    {
        return 'siswa' . substr($nis, -4);
    }

    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'duplicate' => $this->duplicateCount,
            'errors' => count($this->importErrors),
            'warnings' => $this->importWarnings,
            'created_classes' => $this->createdClasses,
            'total_processed' => $this->rowNumber - 1,
            'success_data' => $this->successData,
            'duplicate_data' => $this->duplicateData,
            'error_list' => $this->importErrors,
        ];
    }
}
