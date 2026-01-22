<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidNIPGuru implements Rule
{
    private $message;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        $this->message = '';
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        // Jika NIP kosong/nullable, skip validasi
        if (empty($value)) {
            return true;
        }

        // 1. Harus 18 digit angka
        if (!preg_match('/^[0-9]{18}$/', $value)) {
            $this->message = 'NIP harus terdiri dari 18 digit angka';
            return false;
        }

        // 2. Digit 1-8: Tanggal Lahir (YYYYMMDD)
        $tglLahir = substr($value, 0, 8);
        $tahunLahir = substr($tglLahir, 0, 4);
        $bulanLahir = substr($tglLahir, 4, 2);
        $hariLahir = substr($tglLahir, 6, 2);

        // Validasi tanggal lahir
        if (!checkdate($bulanLahir, $hariLahir, $tahunLahir)) {
            $this->message = 'Format tanggal lahir pada NIP tidak valid (YYYYMMDD)';
            return false;
        }

        // 3. Digit 9-14: TMT CPNS (YYYYMM)
        $tmtCpns = substr($value, 8, 6);
        $tahunCpns = substr($tmtCpns, 0, 4);
        $bulanCpns = substr($tmtCpns, 4, 2);

        // Validasi TMT CPNS
        if ($bulanCpns < 1 || $bulanCpns > 12) {
            $this->message = 'Bulan TMT CPNS tidak valid (MM harus 01-12)';
            return false;
        }

        // 4. Digit 15: Jenis Kelamin (G)
        $jenisKelamin = substr($value, 14, 1);
        if (!in_array($jenisKelamin, ['1', '2'])) {
            $this->message = 'Digit ke-15 NIP harus 1 (laki-laki) atau 2 (perempuan)';
            return false;
        }

        // 5. Digit 16-18: Nomor Urut (XXX)
        $nomorUrut = substr($value, 15, 3);
        if ($nomorUrut == '000') {
            $this->message = 'Nomor urut tidak valid (tidak boleh 000)';
            return false;
        }

        // 6. Validasi logika tambahan
        $tahunSekarang = date('Y');

        // Usia minimal 18 tahun saat jadi CPNS
        if ($tahunCpns - $tahunLahir < 18) {
            $this->message = 'Usia tidak memenuhi syarat (minimal 18 tahun saat menjadi CPNS)';
            return false;
        }

        // Usia maksimal 35 tahun saat jadi CPNS (aturan umum)
        if ($tahunCpns - $tahunLahir > 35) {
            $this->message = 'Usia tidak memenuhi syarat (maksimal 35 tahun saat menjadi CPNS)';
            return false;
        }

        // TMT CPNS tidak boleh di masa depan
        if ($tahunCpns > $tahunSekarang) {
            $this->message = 'TMT CPNS tidak boleh di masa depan';
            return false;
        }

        // TMT CPNS minimal 1965 (asumsi PNS pertama)
        if ($tahunCpns < 1965) {
            $this->message = 'Tahun TMT CPNS tidak valid (minimal 1965)';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return $this->message ?: 'Format NIP tidak valid';
    }

    /**
     * Mendapatkan informasi detail dari NIP (untuk keperluan debugging)
     */
    public static function parseNIP($nip)
    {
        if (!preg_match('/^[0-9]{18}$/', $nip)) {
            return null;
        }

        return [
            'tanggal_lahir' => substr($nip, 0, 8),
            'tmt_cpns' => substr($nip, 8, 6),
            'jenis_kelamin' => substr($nip, 14, 1),
            'jenis_kelamin_text' => substr($nip, 14, 1) == '1' ? 'Laki-laki' : 'Perempuan',
            'nomor_urut' => substr($nip, 15, 3),
            'format_tanggal_lahir' => substr($nip, 6, 2) . '/' . substr($nip, 4, 2) . '/' . substr($nip, 0, 4),
            'format_tmt_cpns' => substr($nip, 12, 2) . '/' . substr($nip, 8, 4),
            'usia_saat_cpns' => substr($nip, 8, 4) - substr($nip, 0, 4),
        ];
    }
}
