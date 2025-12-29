<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Student;
use App\Models\TeacherClass;
use App\Models\User;
use Illuminate\Http\Request;

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
        $teacherClasses = TeacherClass::with([
            'classes.studentList.user',
        ])->get();

        $students = [];
        $muridCounts = [];
        foreach ($teacherClasses as $teacherClass) {
            if (! $teacherClass->classes) {
                continue;
            }

            $classId = $teacherClass->classes->id;

            $students[$classId] = Student::where('class_id', $classId)
                ->whereHas('user.roles', fn ($q) => $q->where('name', 'Murid'))
                ->with('user')
                ->paginate(10);

            $muridCounts[$classId] = $students[$classId]->total();
        }

        return view('Guru.dashboardGuru', compact(
            'teacherClasses',
            'students',
            'muridCounts'
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
}
