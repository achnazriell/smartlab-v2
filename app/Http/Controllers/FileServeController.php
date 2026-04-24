<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * FileServeController
 *
 * Melayani file dari Storage::disk('public') langsung via HTTP response.
 * Solusi untuk Railway dan hosting yang tidak support symlink /public/storage.
 *
 * Daftarkan di routes/web.php (dalam middleware auth):
 *   Route::get('/serve-file/{path}', [FileServeController::class, 'serve'])
 *        ->where('path', '.*')
 *        ->name('file.serve');
 */
class FileServeController extends Controller
{
    /**
     * Folder yang diizinkan diakses.
     * Sesuaikan dengan $allowedFolders di FileHelper.
     */
    protected array $allowedPrefixes = [
        'file_materi/',
        'file_task/',
        'file_collection/',  // ✅ tambahan untuk file jawaban siswa
    ];

    public function serve(Request $request, string $path)
    {
        // ✅ Cegah path traversal attack
        $path = ltrim($path, '/');
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(403, 'Path tidak valid');
        }

        // ✅ Hanya izinkan folder yang terdaftar
        $isAllowed = collect($this->allowedPrefixes)
            ->contains(fn($prefix) => str_starts_with($path, $prefix));

        if (!$isAllowed) {
            abort(403, 'Akses ditolak');
        }

        // ✅ Cek file exists
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        // ✅ Stream file langsung
        $stream   = Storage::disk('public')->readStream($path);
        $mimeType = Storage::disk('public')->mimeType($path);
        $size     = Storage::disk('public')->size($path);
        $filename = basename($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'private, max-age=3600',
            // ✅ Header CORS untuk PDF.js (penting agar PDF.js bisa fetch PDF)
            'Access-Control-Allow-Origin'  => config('app.url'),
            'Access-Control-Allow-Methods' => 'GET',
        ]);
    }
}
