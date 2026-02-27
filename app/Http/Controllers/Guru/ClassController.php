<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Menampilkan semua kelas yang diajar oleh guru (tahun ajaran aktif)
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tahun ajaran aktif belum ditentukan.');
        }

        // Ambil assignment guru di tahun aktif, kelompokkan per kelas
        $assignments = $teacher->assignments()
            ->where('academic_year_id', $activeYear->id)
            ->with(['class', 'subject'])
            ->get();

        $kelasData = collect();
        foreach ($assignments as $assignment) {
            if (!$assignment->class) continue;
            $classId = $assignment->class_id;
            $className = $assignment->class->name_class;

            if (!$kelasData->has($classId)) {
                // Hitung jumlah siswa di kelas ini (tahun aktif)
                $jumlahSiswa = $assignment->class->currentStudents()->count();

                $kelasData->put($classId, [
                    'kelas'        => $className,
                    'nama_kelas'   => $className,
                    'jumlah_siswa' => $jumlahSiswa,
                    'mapel'        => [],
                    'class_id'     => $classId,
                ]);
            }

            // Tambahkan mapel jika belum ada
            $mapel = $assignment->subject->name_subject;
            if (!in_array($mapel, $kelasData[$classId]['mapel'])) {
                $kelasData[$classId]['mapel'][] = $mapel;
            }
        }

        $kelasData = $kelasData->values();

        return view('Guru.Classes.index', compact('kelasData'));
    }

    /**
     * Menampilkan semua siswa dalam sebuah kelas (tahun ajaran aktif)
     */
    public function showStudents($kelas)
    {
        session(['previous_page' => url()->previous()]);

        $teacher = Auth::user()->teacher;
        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            abort(403, 'Tahun ajaran aktif belum ditentukan.');
        }

        // Cari kelas berdasarkan nama
        $kelasModel = Classes::where('name_class', $kelas)->firstOrFail();

        // Validasi apakah guru mengajar kelas ini di tahun aktif
        $isTeaching = $teacher->assignments()
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $kelasModel->id)
            ->exists();

        if (!$isTeaching) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        // Ambil siswa di kelas ini (tahun aktif)
        $siswaList = $kelasModel->currentStudents()
            ->with('user')
            ->get()
            ->map(function ($siswa) {
                return [
                    'id'     => $siswa->id,
                    'nama'   => $siswa->user->name ?? 'Nama tidak tersedia',
                    'nis'    => $siswa->nis ?? '-',
                    'email'  => $siswa->user->email ?? '-',
                    'status' => $siswa->status ?? 'tidak aktif',
                ];
            });

        $kelasInfo = [
            'nama'        => $kelas,
            'total_siswa' => $siswaList->count(),
        ];

        return view('Guru.Classes.student', compact('kelasInfo', 'siswaList'));
    }

    /**
     * Menampilkan detail siswa
     */
    public function showStudentDetail($kelas, $siswaId)
    {
        $teacher = Auth::user()->teacher;
        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            abort(403, 'Tahun ajaran aktif belum ditentukan.');
        }

        $kelasModel = Classes::where('name_class', $kelas)->firstOrFail();

        // Validasi akses guru ke kelas ini
        $isTeaching = $teacher->assignments()
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $kelasModel->id)
            ->exists();

        if (!$isTeaching) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        // Ambil data siswa
        $siswa = Student::with('user')->findOrFail($siswaId);

        // Validasi apakah siswa berada di kelas yang benar pada tahun aktif
        $isInClass = $siswa->classAssignments()
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $kelasModel->id)
            ->exists();

        if (!$isInClass) {
            abort(404, 'Siswa tidak ditemukan di kelas ini.');
        }

        // Ambil mata pelajaran yang diajar guru di kelas ini (tahun aktif)
        $mapelGuru = $teacher->assignments()
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $kelasModel->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();

        return view('Guru.Classes.student-detail', compact('siswa', 'kelas', 'mapelGuru'));
    }
}
