<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Student;
use App\Models\Task;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeguruController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Ambil tahun ajaran aktif
        $activeAcademicYear = AcademicYear::active()->first();

        // Ambil semua assignment guru di tahun ajaran aktif
        $assignments = $teacher->assignments()
            ->with(['class', 'subject'])
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->get();

        // Kelompokkan berdasarkan kelas
        $classesData = [];
        foreach ($assignments as $assignment) {
            $classId = $assignment->class_id;
            if (!isset($classesData[$classId])) {
                $classesData[$classId] = [
                    'class' => $assignment->class,
                    'subjects' => [],
                ];
            }
            $classesData[$classId]['subjects'][] = $assignment->subject->name_subject;
        }

        // Batasi untuk tampilan data kelas (hanya 3 kelas)
        $limitedClasses = array_slice($classesData, 0, 3);

        $totalKelas = count($classesData);

        // Hitung total siswa UNIK yang diajar oleh guru ini di tahun ajaran aktif
        $totalSiswa = 0;
        $studentIds = [];

        foreach ($assignments as $assignment) {
            if (!$assignment->class) continue;

            // Ambil siswa dari kelas tersebut melalui currentStudents (relasi di model Classes)
            $students = $assignment->class->currentStudents; // sudah difilter tahun ajaran aktif
            foreach ($students as $student) {
                if (!in_array($student->id, $studentIds)) {
                    $studentIds[] = $student->id;
                    $totalSiswa++;
                }
            }
        }

        $kelasData = [];

        foreach ($limitedClasses as $item) {
            $kelasData[] = [
                'kelas' => $item['class']->name_class,
                'mapel' => $item['subjects'],
                'jumlah_siswa' => $item['class']->currentStudents->count(),
            ];
        }

        $tugasBerjalan = Task::where('user_id', $user->id)
            ->where('date_collection', '>=', now())
            ->count();

        $tugasLewat = Task::where('user_id', $user->id)
            ->where('date_collection', '<', now())
            ->count();

        $tugasDinilai = Task::where('user_id', $user->id)
            ->where('date_collection', '<', now())
            ->whereDoesntHave('collections.assessment', function ($q) {
                $q->whereNull('mark_task');
            })
            ->count();

        return view('Guru.dashboardGuru', compact(
            'totalKelas',
            'totalSiswa',
            'kelasData',
            'tugasBerjalan',
            'tugasDinilai',
            'tugasLewat',
            'teacher'
        ));
    }

    public function getClassDetails(Request $request, $classId)
    {
        $class = Classes::find($classId);

        if (! $class) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        // Ambil siswa di kelas ini (tahun ajaran aktif)
        $students = $class->currentStudents()->with('user')->get();

        if ($request->ajax()) {
            return view('partials.studentList', compact('students'))->render();
        }

        return response()->json(['message' => 'Permintaan tidak valid'], 400);
    }

    public function kelasSaya()
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        $activeAcademicYear = AcademicYear::active()->first();

        // Ambil assignment dengan pagination
        $assignments = $teacher->assignments()
            ->with(['class', 'subject'])
            ->when($activeAcademicYear, function ($q) use ($activeAcademicYear) {
                $q->where('academic_year_id', $activeAcademicYear->id);
            })
            ->paginate(9);

        // Kelompokkan per kelas untuk view
        $teacherClasses = [];
        foreach ($assignments as $assignment) {
            $classId = $assignment->class_id;
            if (!isset($teacherClasses[$classId])) {
                $teacherClasses[$classId] = (object)[
                    'classes' => $assignment->class,
                    'subjects' => [],
                ];
            }
            $teacherClasses[$classId]->subjects[] = $assignment->subject;
        }

        // Ubah ke collection untuk pagination (agak tricky, lebih baik query sendiri)
        // Alternatif: buat pagination manual atau query distinct kelas dulu.
        // Untuk sementara, kita gunakan pagination dari assignments, lalu kelompokkan.
        // Namun karena kita ingin per kelas, lebih baik query distinct kelas dari assignments.
        // Tapi kita akan tetap menggunakan pagination dari assignments, lalu di view kita loop assignments dan group.
        // Di view sebelumnya mungkin menggunakan $teacherClasses->each, kita sesuaikan.

        // Kita kirim $assignments saja, dan di view kita kelompokkan.
        return view('Guru.kelas.index', compact('assignments'));
    }
}
