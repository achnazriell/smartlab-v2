<?php
// ============================================================
// app/Helpers/FileHelper.php
// ============================================================
// Tambahkan di composer.json → autoload → files:
//   "app/Helpers/FileHelper.php"
// Lalu jalankan: composer dump-autoload
// ============================================================

if (!function_exists('file_url')) {
    /**
     * Generate URL untuk file yang disimpan di disk 'public'.
     *
     * Mengapa tidak pakai Storage::url() atau asset('storage/...')?
     * Di Railway (dan beberapa hosting), symlink /public/storage tidak ada
     * sehingga kedua cara di atas menghasilkan 404.
     *
     * Fungsi ini fallback ke route 'file.serve' yang melayani file
     * langsung dari controller (stream dari disk), sehingga selalu bekerja
     * di environment apapun termasuk Railway.
     *
     * Cara pakai di Blade:
     *   {{ file_url($materi->file_materi) }}
     *   {{ file_url($task->file_task) }}
     *
     * @param  string|null $path  Path relatif dari disk 'public' (contoh: 'file_materi/abc.pdf')
     * @return string|null        URL lengkap atau null jika path kosong
     */
    function file_url(?string $path): ?string
    {
        if (!$path) return null;

        // Jika route 'file.serve' sudah didaftarkan di routes/web.php, gunakan itu
        // karena pasti bekerja di Railway.
        if (\Illuminate\Support\Facades\Route::has('file.serve')) {
            return route('file.serve', ['path' => $path]);
        }

        // Fallback: gunakan Storage::url() (bekerja di lokal dengan symlink)
        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
