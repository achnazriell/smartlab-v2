<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

/**
 * FileHelper — VERSI DIPERBAIKI
 *
 * PERBAIKAN UTAMA:
 * 1. Normalisasi path sebelum cek exists dan buat URL
 *    → path yang disimpan di DB kadang: "file_materi/abc.pdf" atau "/file_materi/abc.pdf"
 *      atau bahkan "public/file_materi/abc.pdf" — semua ditangani
 * 2. Tambah method debug() untuk membantu diagnosis di Railway
 * 3. Tambah $routeAvailable = null reset agar tidak stuck saat testing
 */
class FileHelper
{
    protected static array $allowedFolders = [
        'file_materi',
        'file_task',
        'file_collection',
    ];

    /**
     * Hasilkan URL yang benar untuk file di storage.
     *
     * @param  string|null $path  Path relatif seperti 'file_materi/abc.pdf'
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        // Sudah URL penuh (link materi eksternal)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // ✅ FIX: Normalisasi path — hapus prefix 'public/' jika ada
        // (kadang path disimpan sebagai "public/file_materi/abc.pdf" di DB)
        // FileHelper.php - method url()
        $cleanPath = str_replace('\\', '/', ltrim($path, '/'));
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }
        // Gunakan route jika ada, jika tidak fallback ke Storage::url
        if (static::isRouteAvailable() && static::isAllowedPath($cleanPath)) {
            return route('file.serve', ['path' => $cleanPath]);
        }
        return Storage::url($cleanPath);
    }

    /**
     * Cek apakah file benar-benar ada di disk storage.
     * ✅ FIX: Normalisasi path sama seperti url()
     */
    public static function exists(?string $path): bool
    {
        if (blank($path)) return false;
        if (filter_var($path, FILTER_VALIDATE_URL)) return true;

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        return Storage::disk('public')->exists($cleanPath);
    }

    /**
     * ✅ BARU: Debug info — gunakan di tinker atau route debug untuk diagnosis
     *
     * Contoh di routes/web.php (sementara, hapus setelah debug):
     *   Route::get('/debug-file/{path}', function($path) {
     *       return response()->json(\App\Helpers\FileHelper::debug($path));
     *   })->where('path','.*')->middleware('auth');
     *
     * @param  string|null $path
     * @return array
     */
    public static function debug(?string $path): array
    {
        $cleanPath = ltrim($path ?? '', '/');
        if (str_starts_with($cleanPath, 'public/')) {
            $cleanPath = substr($cleanPath, 7);
        }

        $diskRoot = config('filesystems.disks.public.root');

        return [
            'original_path'    => $path,
            'clean_path'       => $cleanPath,
            'disk_root'        => $diskRoot,
            'full_fs_path'     => $diskRoot . '/' . $cleanPath,
            'file_exists'      => Storage::disk('public')->exists($cleanPath),
            'is_url'           => filter_var($path, FILTER_VALIDATE_URL) !== false,
            'route_available'  => static::isRouteAvailable(),
            'is_allowed_path'  => static::isAllowedPath($cleanPath),
            'generated_url'    => static::url($path),
            'storage_url'      => Storage::url($cleanPath),
            'all_files_in_dir' => static::listDir($cleanPath),
        ];
    }

    /**
     * List semua file di direktori yang sama dengan path, untuk diagnosis
     */
    private static function listDir(string $path): array
    {
        $dir = dirname($path);
        try {
            return Storage::disk('public')->files($dir);
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Ambil ekstensi file (lowercase) dari path.
     */
    public static function extension(?string $path): ?string
    {
        if (blank($path)) return null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext ?: null;
    }

    public static function isPdf(?string $path): bool
    {
        return static::extension($path) === 'pdf';
    }

    public static function isImage(?string $path): bool
    {
        return in_array(static::extension($path), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    public static function isVideo(?string $path): bool
    {
        return in_array(static::extension($path), ['mp4', 'webm', 'mov', 'avi']);
    }

    // ─────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────

    private static ?bool $routeAvailable = null;

    protected static function isRouteAvailable(): bool
    {
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
