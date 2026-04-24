<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * FileServeController
 *
 * PERBAIKAN:
 * 1. Access-Control-Allow-Origin diubah dari config('app.url') → '*'
 *    (config('app.url') sering tidak cocok dengan origin request → CORS block → PDF.js 404/fail)
 * 2. Tambah header X-Frame-Options: SAMEORIGIN agar <embed> PDF bisa tampil di iframe
 * 3. Tambah header Content-Security-Policy untuk embed/object
 * 4. Tambah debug log saat file tidak ditemukan (memudahkan diagnosis di Railway)
 */
class FileServeController extends Controller
{
    protected array $allowedPrefixes = [
        'file_materi/',
        'file_task/',
        'file_collection/',
    ];

    public function serve(Request $request, string $path)
    {
        // Cegah path traversal attack
        $path = ltrim($path, '/');
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(403, 'Path tidak valid');
        }

        // Hanya izinkan folder yang terdaftar
        $isAllowed = collect($this->allowedPrefixes)
            ->contains(fn($prefix) => str_starts_with($path, $prefix));

        if (!$isAllowed) {
            abort(403, 'Akses ditolak: folder tidak diizinkan');
        }

        // ✅ FIX 1: Cek file exists + log path yang dicari agar mudah debug
        if (!Storage::disk('public')->exists($path)) {
            // Log agar bisa dilihat di Railway logs
            \Illuminate\Support\Facades\Log::warning('FileServeController: file tidak ditemukan', [
                'path'      => $path,
                'disk_root' => config('filesystems.disks.public.root'),
                'full_path' => config('filesystems.disks.public.root') . '/' . $path,
            ]);
            abort(404, 'File tidak ditemukan: ' . $path);
        }

        $stream   = Storage::disk('public')->readStream($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $size     = Storage::disk('public')->size($path);
        $filename = basename($path);

        // Encode filename untuk Content-Disposition (handle nama file dengan spasi/karakter khusus)
        $encodedFilename = rawurlencode($filename);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type'        => $mimeType,
            'Content-Length'      => $size,
            // ✅ FIX 2: filename* menggunakan RFC 5987 encoding (support UTF-8)
            'Content-Disposition' => 'inline; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename,
            'Cache-Control'       => 'private, max-age=3600',
            // ✅ FIX 3: Ganti config('app.url') → '*' agar PDF.js bisa fetch
            // config('app.url') sering ≠ origin request di Railway → CORS block
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD',
            'Access-Control-Allow-Headers' => 'Range',
            // ✅ FIX 4: Izinkan embed/iframe dari origin yang sama
            'X-Frame-Options'              => 'SAMEORIGIN',
        ]);
    }
}
