<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profile
     */
    public function index()
    {
        $user = Auth::user();

        // Data tambahan berdasarkan role
        $additionalData = [];

        if ($user->hasRole('Guru')) {
            $teacher = $user->teacher;
            $additionalData = [
                'role' => 'Guru',
                'nip' => $teacher->NIP ?? '-',
                'sapaan' => $teacher->sapaan ?? '-',
                'kelas_diajar' => $teacher->classes->count() ?? 0,
                'mata_pelajaran' => $teacher->subjects->pluck('name_subject')->toArray(),
            ];
        } elseif ($user->hasRole('Siswa') || $user->hasRole('Murid')) {
            $student = $user->student;
            $additionalData = [
                'role' => 'Siswa',
                'nis' => $student->nis ?? '-',
                'kelas' => $student->class->name_class ?? '-',
                'status' => $student->status ?? '-',
            ];
        }

        return view('Users.profile', compact('user', 'additionalData'));
    }

    /**
     * Update foto profil
     */
    public function updatePhoto(Request $request)
    {
        try {
            $request->validate([
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $user = Auth::user();

            // Periksa apakah user memiliki akses
            if (!$user->hasRole(['Guru', 'Siswa', 'Murid'])) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengubah foto profil.');
            }

            // Buat folder uploads jika belum ada
            $uploadsPath = public_path('uploads');
            if (!is_dir($uploadsPath)) {
                mkdir($uploadsPath, 0755, true);
            }

            // Buat folder profile-photos jika belum ada
            $profilePhotosPath = public_path('uploads/profile-photos');
            if (!is_dir($profilePhotosPath)) {
                mkdir($profilePhotosPath, 0755, true);
            }

            // Hapus foto lama jika ada
            if ($user->profile_photo) {
                $oldPhotoPath = public_path('uploads/profile-photos/' . $user->profile_photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Generate nama file unik
            $extension = $request->profile_photo->getClientOriginalExtension();
            $photoName = time() . '_' . $user->id . '_' . uniqid() . '.' . $extension;

            // Simpan foto
            $path = $request->profile_photo->move($profilePhotosPath, $photoName);

            if (!$path) {
                throw new \Exception('Gagal menyimpan file foto');
            }

            // Update database
            $user->profile_photo = $photoName;
            $user->save();

            return back()->with('success', 'Foto profil berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

    /**
     * Hapus foto profil
     */
    public function deletePhoto()
    {
        try {
            $user = Auth::user();

            // Periksa apakah user memiliki akses
            if (!$user->hasRole(['Guru', 'Siswa', 'Murid'])) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menghapus foto profil.');
            }

            if ($user->profile_photo) {
                $photoPath = public_path('uploads/profile-photos/' . $user->profile_photo);
                if (file_exists($photoPath)) {
                    if (!unlink($photoPath)) {
                        throw new \Exception('Gagal menghapus file foto');
                    }
                }
                $user->profile_photo = null;
                $user->save();
            }

            return back()->with('success', 'Foto profil berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
    }
}
