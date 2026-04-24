<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

/**
 * FileHelper
 *
 * Helper terpusat untuk menghasilkan URL file yang benar di semua environment,
 * termasuk Railway dan hosting tanpa symlink /public/storage.
 *
 * Cara pakai di Blade:
 *   {!! file_url($materi->file_materi) !!}
 *   {!! file_url($task->file_task) !!}
 *   {!! file_url($collection->file_collection) !!}
 *
 * Daftarkan di config/app.php → aliases:
 *   'FileHelper' => App\Helpers\FileHelper::class,
 *
 * ATAU daftarkan helper function global via composer.json autoload.files
 * dengan membuat file app/helpers.php dan require di sana.
 */
class FileHelper
{
    /**
     * Folder yang diizinkan diakses via route file.serve.
     * Harus sesuai dengan $allowedPrefixes di FileServeController.
     */
    protected static array $allowedFolders = [
        'file_materi',
        'file_task',
        'file_collection',
    ];

    /**
     * Menghasilkan URL yang benar untuk file di storage.
     *
     * Prioritas:
     *  1. Jika kosong / null → return null
     *  2. Jika sudah URL penuh (http/https) → kembalikan langsung (link eksternal)
     *  3. Jika route 'file.serve' terdaftar DAN path ada di folder yang diizinkan
     *     → gunakan route file.serve (bekerja di Railway tanpa symlink)
     *  4. Fallback → Storage::url() (bekerja jika symlink ada)
     *
     * @param  string|null $path  Path relatif seperti 'file_materi/abc.pdf'
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        // Sudah URL penuh (link materi eksternal, dsb.)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Bersihkan path dari slash di awal
        $cleanPath = ltrim($path, '/');

        // Gunakan route file.serve jika tersedia dan path diizinkan
        if (static::isRouteAvailable() && static::isAllowedPath($cleanPath)) {
            return route('file.serve', ['path' => $cleanPath]);
        }

        // Fallback: Storage::url() (butuh symlink)
        return Storage::url($cleanPath);
    }

    /**
     * Cek apakah file benar-benar ada di disk storage.
     *
     * @param  string|null $path
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        if (blank($path)) return false;
        if (filter_var($path, FILTER_VALIDATE_URL)) return true; // asumsikan URL eksternal valid
        return Storage::disk('public')->exists(ltrim($path, '/'));
    }

    /**
     * Ambil ekstensi file (lowercase) dari path.
     *
     * @param  string|null $path
     * @return string|null
     */
    public static function extension(?string $path): ?string
    {
        if (blank($path)) return null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext ?: null;
    }

    /**
     * Apakah file berformat PDF?
     */
    public static function isPdf(?string $path): bool
    {
        return static::extension($path) === 'pdf';
    }

    /**
     * Apakah file berformat gambar?
     */
    public static function isImage(?string $path): bool
    {
        return in_array(static::extension($path), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Apakah file berformat video?
     */
    public static function isVideo(?string $path): bool
    {
        return in_array(static::extension($path), ['mp4', 'webm', 'mov', 'avi']);
    }

    // ──────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────

    private static ?bool $routeAvailable = null;

    protected static function isRouteAvailable(): bool
    {
        // Cache hasil cek agar tidak memanggil app('router') berkali-kali
        if (static::$routeAvailable === null) {
            static::$routeAvailable = \Illuminate\Support\Facades\Route::has('file.serve');
        }
        return static::$routeAvailable;
    }

    protected static function isAllowedPath(string $path): bool
    {
        foreach (static::$allowedFolders as $folder) {
            if (str_starts_with($path, $folder . '/') || str_starts_with($path, $folder . '\\')) {
                return true;
            }
        }
        return false;
    }
}
