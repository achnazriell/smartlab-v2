<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
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
use Illuminate\Validation\Rule;

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
    private $successData = []; // ✅ TAMBAHKAN INI
    private $duplicateData = []; // ✅ TAMBAHKAN INI

    // 🔄 UPDATE METHOD model() PADA StudentImport.php

    public function model(array $row)
    {
        $currentRow = $this->rowNumber++;

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
            $warningMsg = "Baris {$currentRow}: Email '{$email}' sudah terdaftar";
            $this->duplicateData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'reason' => 'Email sudah terdaftar'
            ];
            $this->importWarnings[] = $warningMsg;
            return null;
        }

        // Cek duplikat NIS
        $existingNIS = Student::where('nis', $nis)->first();
        if ($existingNIS) {
            $this->duplicateCount++;
            $warningMsg = "Baris {$currentRow}: NIS '{$nis}' sudah terdaftar";
            $this->duplicateData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'reason' => 'NIS sudah terdaftar'
            ];
            $this->importWarnings[] = $warningMsg;
            return null;
        }

        try {
            DB::beginTransaction();

            // Generate password jika kosong
            if (empty($password)) {
                $password = $this->generateDefaultPassword($nis);
            }

            // Buat user baru
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => bcrypt($password),
                'plain_password' => $password,
            ]);

            // Assign role
            $user->assignRole('Murid');

            // Proses kelas
            $classId = null;
            $className = null;
            if (!empty($kelas)) {
                $classId = $this->processClass($kelas, $currentRow);
                $className = $kelas;
            }

            // Buat record student
            Student::create([
                'user_id' => $user->id,
                'nis' => $nis,
                'class_id' => $classId,
                'status' => 'siswa',
            ]);

            DB::commit();

            // ✅ SIMPAN DATA YANG BERHASIL
            $this->successData[] = [
                'nama' => $nama,
                'nis' => $nis,
                'email' => $email,
                'kelas' => $className,
                'row' => $currentRow
            ];

            $this->importedCount++;

            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = "Baris {$currentRow}: " . $e->getMessage();
            $this->importErrors[] = $errorMsg;
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * Proses kelas - jika tidak ada, buat baru
     */
    private function processClass($kelasName, $rowNumber)
    {
        // Normalize nama kelas
        $kelasName = trim($kelasName);
        $kelasName = preg_replace('/\s+/', ' ', $kelasName); // Hapus spasi berlebih

        // Cek apakah kelas sudah ada
        $kelasModel = Classes::where('name_class', $kelasName)->first();

        if ($kelasModel) {
            return $kelasModel->id;
        }

        // Coba dengan pencarian case-insensitive
        $kelasModel = Classes::whereRaw('LOWER(name_class) = ?', [strtolower($kelasName)])->first();

        if ($kelasModel) {
            // Update nama kelas ke format yang benar
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

            // Catat kelas yang dibuat
            if (!isset($this->createdClasses[$kelasName])) {
                $this->createdClasses[$kelasName] = 0;
            }
            $this->createdClasses[$kelasName]++;

            $this->importWarnings[] = "Baris {$rowNumber}: Kelas '{$kelasName}' dibuat otomatis";

            return $newClass->id;
        } catch (\Exception $e) {
            $this->importWarnings[] = "Baris {$rowNumber}: Gagal membuat kelas '{$kelasName}' - " . $e->getMessage();
            return null;
        }
    }

    /**
     * Extract grade level dari nama kelas
     */
    private function extractGradeLevel($kelasName)
    {
        $kelasName = strtolower($kelasName);

        if (preg_match('/\b(x|xi|xii|10|11|12)\b/i', $kelasName, $matches)) {
            $grade = strtoupper($matches[1]);

            // Konversi angka ke romawi jika perlu
            if (is_numeric($grade)) {
                $gradeMap = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
                return $gradeMap[$grade] ?? $grade;
            }
            return $grade;
        }

        return 'X'; // Default grade X
    }

    private function getValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Cek berbagai variasi penulisan
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

    public function customValidationMessages()
    {
        return [
            '*.email.email' => 'Format email tidak valid',
        ];
    }

    /**
     * Get import statistics
     */
    public function getImportStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'duplicate' => $this->duplicateCount,
            'errors' => $this->importErrors,
            'warnings' => $this->importWarnings,
            'created_classes' => $this->createdClasses,
            'total_processed' => $this->rowNumber - 1,
            'success_data' => $this->successData, // ✅ TAMBAHKAN INI
            'duplicate_data' => $this->duplicateData, // ✅ TAMBAHKAN INI
        ];
    }
}
