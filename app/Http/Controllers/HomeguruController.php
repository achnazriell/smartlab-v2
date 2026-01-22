<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Student;
use App\Models\Task;
use App\Models\Teacher;
use App\Models\TeacherClass;
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

        $teacherClasses = TeacherClass::with([
            'classes.studentList.user',
            'subjects'
        ])
            ->where('teacher_id', $teacher->id)
            ->limit(3)
            ->get();

        $totalKelas = $teacherClasses->count();
        $totalSiswa = 0;
        $kelasData = [];

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


        foreach ($teacherClasses as $tc) {
            if (!$tc->classes) continue;

            $class = $tc->classes;
            $jumlahSiswa = $class->studentList->count();
            $totalSiswa += $jumlahSiswa;

            $kelasData[] = [
                'kelas' => $class->name_class,
                'mapel' => $tc->subjects->pluck('name_subject')->toArray(),
                'jumlah_siswa' => $jumlahSiswa,
            ];
        }

        return view('Guru.dashboardGuru', compact(
            'totalKelas',
            'totalSiswa',
            'kelasData',
            'tugasBerjalan',
            'tugasDinilai',
            'tugasLewat'
        ));
    }

    public function getClassDetails(Request $request, $classId)
    {
        $class = Classes::find($classId);

        if (! $class) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        $students = User::whereHas('class', function ($query) use ($classId) {
            $query->where('classes.id', $classId);
        })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Murid');
            })
            ->get();

        if ($request->ajax()) {
            return view('partials.studentList', compact('students'))->render();
        }

        return response()->json(['message' => 'Permintaan tidak valid'], 400);
    }

    public function kelasSaya()
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        $teacherClasses = TeacherClass::with([
            'classes.studentList.user',
            'subjects'
        ])
            ->where('teacher_id', $teacher->id)
            ->paginate(9); // 👈 pagination biar rapi

        return view('Guru.kelas.index', compact('teacherClasses'));
    }
}
