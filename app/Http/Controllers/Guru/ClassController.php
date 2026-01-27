<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Menampilkan semua kelas yang diajar oleh guru
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;

        // Ambil semua kelas yang diajar oleh guru ini melalui TeacherClass
        $teacherClasses = TeacherClass::where('teacher_id', $teacher->id)
            ->with([
                'classes',
                'classes.studentList', // Menggunakan studentList sesuai relasi di model Classes
                'subjects'
            ])
            ->get();

        // Kelompokkan data per kelas
        $kelasData = collect();

        foreach ($teacherClasses as $tc) {
            if (!$tc->classes) continue;

            $kelasName = $tc->classes->name_class;

            // Jika kelas sudah ada di koleksi, tambahkan mapel saja
            if ($kelasData->has($kelasName)) {
                $kelas = $kelasData[$kelasName];
                foreach ($tc->subjects as $subject) {
                    if (!in_array($subject->name_subject, $kelas['mapel'])) {
                        $kelas['mapel'][] = $subject->name_subject;
                    }
                }
                $kelasData[$kelasName] = $kelas;
            } else {
                // Jika kelas baru, buat entry baru
                $mapel = $tc->subjects->pluck('name_subject')->toArray();

                // Hitung jumlah siswa di kelas ini
                $jumlahSiswa = $tc->classes->studentList()->count();

                $kelasData[$kelasName] = [
                    'kelas' => $kelasName,
                    'nama_kelas' => $kelasName,
                    'jumlah_siswa' => $jumlahSiswa,
                    'mapel' => $mapel,
                    'class_id' => $tc->classes->id // Tambahkan ID untuk routing
                ];
            }
        }

        // Konversi ke array values untuk view
        $kelasData = $kelasData->values();

        return view('Guru.Classes.index', compact('kelasData'));
    }

    /**
     * Menampilkan semua siswa dalam sebuah kelas
     */
    public function showStudents($kelas)
    {
        session(['previous_page' => url()->previous()]);

        $teacher = Auth::user()->teacher;

        // Validasi bahwa guru mengajar kelas ini
        $isTeaching = TeacherClass::where('teacher_id', $teacher->id)
            ->whereHas('classes', function ($query) use ($kelas) {
                $query->where('name_class', $kelas);
            })
            ->exists();

        if (!$isTeaching) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        // Ambil kelas berdasarkan nama
        $kelasModel = Classes::where('name_class', $kelas)->firstOrFail();

        // Debug: Cek apakah ada siswa di kelas ini
        $totalSiswa = $kelasModel->studentList()->count();

        // Ambil siswa di kelas ini dengan eager loading
        $siswaList = $kelasModel->studentList()
            ->with('user')
            ->get();

        // Debug: Cek data siswa yang diambil
        $debugData = [];
        foreach ($siswaList as $siswa) {
            $debugData[] = [
                'siswa_id' => $siswa->id,
                'user_id' => $siswa->user_id,
                'has_user' => !is_null($siswa->user),
                'user_name' => $siswa->user ? $siswa->user->name : 'NULL',
                'nis' => $siswa->nis,
                'status' => $siswa->status
            ];
        }

        // Transform data untuk view
        $siswaList = $siswaList->map(function ($siswa) {
            return [
                'id' => $siswa->id,
                'nama' => $siswa->user ? $siswa->user->name : 'Nama tidak tersedia',
                'nis' => $siswa->nis ?? '-',
                'email' => $siswa->user ? $siswa->user->email : '-',
                'status' => $siswa->status ?? 'tidak aktif'
            ];
        });

        // Debug: Log data untuk memeriksa
        \Log::info('Data siswa di kelas ' . $kelas, [
            'total_siswa' => $totalSiswa,
            'siswa_list_count' => $siswaList->count(),
            'debug_data' => $debugData
        ]);

        // Ambil informasi kelas
        $kelasInfo = [
            'nama' => $kelas,
            'total_siswa' => $siswaList->count(),
            'jumlah_mapel' => 0 // Tidak diperlukan lagi
        ];

        return view('Guru.Classes.student', compact('kelasInfo', 'siswaList'));
    }


    /**
     * Menampilkan detail siswa
     */
    public function showStudentDetail($kelas, $siswaId)
    {
        $teacher = Auth::user()->teacher;

        // Validasi akses - cek apakah guru mengajar kelas ini
        $isTeaching = TeacherClass::where('teacher_id', $teacher->id)
            ->whereHas('classes', function ($query) use ($kelas) {
                $query->where('name_class', $kelas);
            })
            ->exists();

        if (!$isTeaching) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        // Ambil data siswa
        $siswa = Student::with(['user', 'class'])
            ->findOrFail($siswaId);

        // Validasi apakah siswa berada di kelas yang benar
        if (!$siswa->class || $siswa->class->name_class != $kelas) {
            abort(404, 'Siswa tidak ditemukan di kelas ini.');
        }

        // Ambil mata pelajaran yang diajar guru di kelas ini
        $mapelGuru = TeacherClass::where('teacher_id', $teacher->id)
            ->where('classes_id', $siswa->class->id)
            ->with('subjects')
            ->get()
            ->flatMap(function ($tc) {
                return $tc->subjects;
            })
            ->unique('id')
            ->values();

        return view('Guru.Classes.student-detail', compact('siswa', 'kelas', 'mapelGuru'));
    }
}
