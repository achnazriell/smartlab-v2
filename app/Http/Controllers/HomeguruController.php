<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\TeacherClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $teacherClasses = TeacherClass::where('teacher_id', auth()->id())->with('class.users.roles')->get();

        $students = [];
        foreach ($teacherClasses as $teacherClass) {
            $students[$teacherClass->class->id] = User::whereHas('class', function ($query) use ($teacherClass) {
                $query->where('classes.id', $teacherClass->class->id);
            })
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'Murid');
                })
                ->orderBy('name')
                ->paginate(10);
        }

        $muridCounts = $teacherClasses->mapWithKeys(function ($teacherClass) {
            $muridCount = User::whereHas('class', function ($query) use ($teacherClass) {
                $query->where('classes.id', $teacherClass->class->id); // Spesifik ke kelas tertentu
            })
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Murid'); // Filter berdasarkan role 'Murid'
            })
            ->count();

            return [$teacherClass->class->id => $muridCount]; // Key: ID kelas, Value: jumlah murid
        });

        return view('Guru.dashboardGuru', compact('teacherClasses', 'students', 'muridCounts'));
    }



    public function getClassDetails(Request $request, $classId)
    {
        $class = Classes::find($classId);

        if (!$class) {
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
}
