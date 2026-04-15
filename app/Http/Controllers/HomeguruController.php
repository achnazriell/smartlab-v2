<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Collection;
use App\Models\Student;
use App\Models\Task;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Carbon\Carbon;

class HomeguruController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* ---------------------------------------------------------------
     |  Helper: bangun $kelasData (Collection) dari assignments guru
     | ------------------------------------------------------------- */
    private function buildKelasData($assignments): SupportCollection
    {
        $classesData = [];

        foreach ($assignments as $assignment) {
            if (! $assignment->class) continue;

            $classId = $assignment->class_id;

            if (! isset($classesData[$classId])) {
                $classesData[$classId] = [
                    'kelas'        => $assignment->class->name_class,
                    'mapel'        => [],
                    'jumlah_siswa' => $assignment->class->currentStudents->count(),
                ];
            }

            // Hindari mapel duplikat dalam satu kelas
            $subjectName = $assignment->subject->name_subject ?? $assignment->subject->name ?? '';
            if ($subjectName && ! in_array($subjectName, $classesData[$classId]['mapel'])) {
                $classesData[$classId]['mapel'][] = $subjectName;
            }
        }

        // Kembalikan sebagai Collection (bukan array) agar bisa ->count(), ->sum(), dst.
        return collect(array_values($classesData));
    }

    /* ---------------------------------------------------------------
     |  Dashboard utama guru
     | ------------------------------------------------------------- */
    public function index()
    {
        $user    = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Tahun ajaran aktif
        $activeAcademicYear = AcademicYear::active()->first();

        // Semua assignment guru di tahun ajaran aktif (dengan eager load)
        $assignments = $teacher->assignments()
            ->with(['class.currentStudents', 'subject'])
            ->when($activeAcademicYear, fn($q) => $q->where('academic_year_id', $activeAcademicYear->id))
            ->get();

        // ── kelasData sebagai Collection ──────────────────────────
        $kelasData = $this->buildKelasData($assignments);

        // ── Total siswa unik ──────────────────────────────────────
        $totalSiswa = $assignments
            ->filter(fn($a) => $a->class)
            ->flatMap(fn($a) => $a->class->currentStudents->pluck('id'))
            ->unique()
            ->count();

        // ── Statistik tugas ───────────────────────────────────────
        $tugasBerjalan = Task::where('user_id', $user->id)
            ->where('date_collection', '>=', now())
            ->count();

        $tugasLewat = Task::where('user_id', $user->id)
            ->where('date_collection', '<', now())
            ->count();

        // Tugas yang sudah melewati deadline DAN semua koleksinya sudah dinilai
        $tugasDinilai = Task::where('user_id', $user->id)
            ->where('date_collection', '<', now())
            ->whereDoesntHave('collections.assessment', fn($q) => $q->whereNull('mark_task'))
            ->count();

        // ── Pengumpulan hari ini ──────────────────────────────────
        // Hanya dihitung jika model Collection tersedia
        $pengumpulanHariIni = 0;
        $belumDinilai       = 0;

        if (class_exists(\App\Models\Collection::class)) {
            $taskIds = Task::where('user_id', $user->id)->pluck('id');

            $pengumpulanHariIni = \App\Models\Collection::whereIn('task_id', $taskIds)
                ->whereDate('created_at', today())
                ->count();

            // Pengumpulan yang belum punya assessment / mark_task null
            $belumDinilai = \App\Models\Collection::whereIn('task_id', $taskIds)
                ->whereDoesntHave('assessment', fn($q) => $q->whereNotNull('mark_task'))
                ->count();
        }

        // ── Tugas terbaru (5 tugas terakhir) ─────────────────────
        $tugasTerbaru = Task::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        // ── Kuis aktif ────────────────────────────────────────────
        $kuisAktif = 0;
        if (class_exists(\App\Models\Quiz::class)) {
            $kuisAktif = \App\Models\Quiz::where('user_id', $user->id)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->count();
        }

        return view('Guru.dashboardGuru', compact(
            'teacher',
            'kelasData',
            'totalSiswa',
            'tugasBerjalan',
            'tugasDinilai',
            'tugasLewat',
            'tugasTerbaru',
            'pengumpulanHariIni',
            'belumDinilai',
            'kuisAktif',
        ));
    }

    /* ---------------------------------------------------------------
     |  Kelas saya (index kelas guru)
     | ------------------------------------------------------------- */
    public function kelasSaya(Request $request)
    {
        $user    = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        $activeAcademicYear = AcademicYear::active()->first();

        // Semua assignment, eager load
        $assignments = $teacher->assignments()
            ->with(['class.currentStudents', 'subject'])
            ->when($activeAcademicYear, fn($q) => $q->where('academic_year_id', $activeAcademicYear->id))
            ->get();

        // Bangun kelasData sebagai Collection
        $kelasData = $this->buildKelasData($assignments);

        // ── Filter ───────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $kelasData = $kelasData->filter(
                fn($k) => str_contains(strtolower($k['kelas']), $search)
            );
        }

        if ($request->filled('tingkat')) {
            $tingkat = strtoupper($request->tingkat);
            $kelasData = $kelasData->filter(
                fn($k) => str_starts_with(strtoupper($k['kelas']), $tingkat)
            );
        }

        // ── Sort ─────────────────────────────────────────────────
        $kelasData = match ($request->sort) {
            'nama_asc'   => $kelasData->sortBy('kelas'),
            'nama_desc'  => $kelasData->sortByDesc('kelas'),
            'siswa_desc' => $kelasData->sortByDesc('jumlah_siswa'),
            'siswa_asc'  => $kelasData->sortBy('jumlah_siswa'),
            default      => $kelasData->sortBy('kelas'),
        };

        // Reset keys setelah sort/filter
        $kelasData = $kelasData->values();

        return view('Guru.classes.index', compact('kelasData'));
    }

    /* ---------------------------------------------------------------
     |  Detail siswa per kelas (AJAX & normal)
     | ------------------------------------------------------------- */
    public function getClassDetails(Request $request, $classId)
    {
        $class = Classes::find($classId);

        if (! $class) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        $students = $class->currentStudents()->with('user')->get();

        if ($request->ajax()) {
            return view('partials.studentList', compact('students'))->render();
        }

        return response()->json(['message' => 'Permintaan tidak valid'], 400);
    }
}
