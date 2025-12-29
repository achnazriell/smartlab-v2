<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Builder\Class_;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search_student;

        $students = Student::with(['user', 'class'])
            ->whereIn('status', ['siswa', 'lulus'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })
                    ->orWhereHas('class', function ($q) use ($search) {
                        $q->where('name_class', 'like', "%$search%");
                    });
            })
            ->latest()
            ->paginate(5);
        $classes = Classes::all();

        return view('Admins.Students.index', compact('students', 'classes'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => 'required|string',
            'class_id' => 'required|exists:classes,id',
        ]);

        // 1. User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'plain_password' => $request->password,
        ]);

        // 2. Role
        $user->assignRole('Murid');

        // 3. Student
        Student::create([
            'user_id' => $user->id,
            'nis' => $request->nis,
            'class_id' => $request->class_id,
            'status' => 'siswa',
        ]);

        return redirect()->back()->with('success', 'Murid berhasil ditambahkan.');
    }

    public function import(Request $request)
    {
        Excel::import(new StudentImport, $request->file('file'));

        return back()->with('success', 'Import murid berhasil!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|min:6',
            'class_id' => 'required|exists:classes,id',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        Student::where('user_id', $id)->update([
            'class_id' => $request->class_id,
        ]);

        return redirect()->route('Students')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Student::where('user_id', $id)->delete();
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Murid berhasil dihapus.');
    }
}
