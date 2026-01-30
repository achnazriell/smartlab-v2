<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;
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
    private $rowNumber = 1;
    private $importErrors = [];

    public function model(array $row)
    {
        $currentRow = $this->rowNumber++;

        // Debug: Lihat struktur data yang masuk
        // \Log::info('Row data:', $row);

        // Karena heading bisa berbeda-beda, coba ambil dengan berbagai kemungkinan nama kolom
        $nama = $this->getValue($row, ['nama_lengkap', 'nama', 'name', 'nama siswa', 'nama_lengkap_siswa']);
        $email = $this->getValue($row, ['email', 'email_address', 'email_siswa']);
        $nis = $this->getValue($row, ['nis', 'nomor_induk', 'nomor_induk_siswa']);
        $kelas = $this->getValue($row, ['kelas', 'class', 'nama_kelas']);
        $password = $this->getValue($row, ['password', 'kata_sandi', 'sandi']);

        // Konversi NIS jika berupa float/double (karena Excel sering membaca angka sebagai float)
        if (is_numeric($nis)) {
            $nis = (string)(int)$nis; // Konversi ke integer lalu string
        }

        // Konversi password jika berupa float/double
        if (is_numeric($password)) {
            $password = (string)(int)$password;
        }

        // Skip baris kosong
        if (empty($nama) && empty($email) && empty($nis)) {
            $this->skippedCount++;
            return null;
        }

        // Validasi data minimal
        if (empty($nama) || empty($email) || empty($nis)) {
            $this->importErrors[] = "Baris {$currentRow}: Data tidak lengkap (Nama: '{$nama}', Email: '{$email}', NIS: '{$nis}')";
            $this->skippedCount++;
            return null;
        }

        // Normalize data
        $nama = trim($nama);
        $email = trim($email);
        $nis = trim($nis);
        $kelas = !empty($kelas) ? trim($kelas) : null;
        $password = !empty($password) ? trim($password) : null;

        // Cek apakah email sudah ada
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->importErrors[] = "Baris {$currentRow}: Email '{$email}' sudah terdaftar";
            $this->skippedCount++;
            return null;
        }

        // Cek apakah NIS sudah ada
        $existingNIS = Student::where('nis', $nis)->first();
        if ($existingNIS) {
            $this->importErrors[] = "Baris {$currentRow}: NIS '{$nis}' sudah terdaftar";
            $this->skippedCount++;
            return null;
        }

        try {
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

            // Cari kelas berdasarkan nama
            $classId = null;
            if (!empty($kelas)) {
                // Hapus spasi berlebih dan normalisasi
                $kelas = preg_replace('/\s+/', ' ', $kelas);
                $kelasModel = Classes::where('name_class', 'like', "%{$kelas}%")->first();

                if ($kelasModel) {
                    $classId = $kelasModel->id;
                } else {
                    // Coba tanpa angka romawi atau format khusus
                    $kelasSimple = preg_replace('/[^a-zA-Z0-9\s]/', '', $kelas);
                    $kelasModel = Classes::where('name_class', 'like', "%{$kelasSimple}%")->first();

                    if ($kelasModel) {
                        $classId = $kelasModel->id;
                    } else {
                        $this->importErrors[] = "Baris {$currentRow}: Kelas '{$kelas}' tidak ditemukan, murid akan dibuat tanpa kelas";
                    }
                }
            }

            // Buat record student
            Student::create([
                'user_id' => $user->id,
                'nis' => $nis,
                'class_id' => $classId,
                'status' => 'siswa',
            ]);

            $this->importedCount++;
            return null;

        } catch (\Exception $e) {
            $this->importErrors[] = "Baris {$currentRow}: " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * Helper function to get value from array with multiple possible keys
     */
    private function getValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Coba berbagai format key (case insensitive, dengan underscore, dll)
            $lowerKey = strtolower($key);
            $snakeKey = str_replace(' ', '_', $lowerKey);
            $camelKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $lowerKey)));
            $camelKey = lcfirst($camelKey);

            if (isset($row[$key])) {
                return $row[$key];
            } elseif (isset($row[$lowerKey])) {
                return $row[$lowerKey];
            } elseif (isset($row[$snakeKey])) {
                return $row[$snakeKey];
            } elseif (isset($row[$camelKey])) {
                return $row[$camelKey];
            }
        }
        return null;
    }

    public function rules(): array
    {
        // Aturan validasi lebih fleksibel untuk berbagai format kolom
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

    /**
     * Generate password default jika tidak diisi
     */
    private function generateDefaultPassword($nis)
    {
        return 'siswa' . substr($nis, -4);
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            '*.email.email' => 'Format email tidak valid',
            '*.nis.regex' => 'NIS harus terdiri dari 6-10 digit angka',
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
            'errors' => $this->importErrors,
            'hasFailures' => !empty($this->failures) || !empty($this->importErrors)
        ];
    }
}
