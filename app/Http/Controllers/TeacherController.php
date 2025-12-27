<?php

namespace App\Http\Controllers;

use App\Imports\TeacherImport;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request);
        $search = $request->input('search_teacher', '');
        $query = User::with(['teacher', 'teacher.subject', 'teacher.class'])
            ->select('users.*')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'users.id') // join ke tabel teachers
            ->whereHas('roles', function ($q) {
                $q->where('id', 2); // role guru
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Admin');
            })
            ->orderByRaw('teachers.subject_id IS NOT NULL')
            ->orderByRaw('NOT EXISTS (
        SELECT 1
        FROM teacher_classes
        WHERE teacher_classes.teacher_id = teachers.id
) DESC')

            ->orderBy('users.created_at', 'desc');

        $teachers = $query->paginate(5);

        $classes = Classes::all();
        $subjects = Subject::all();

        return view('Admins.Teachers.index', compact('teachers', 'subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' =>'required|string|min:8|regex:/^\S*$/',
            'NIP' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'classes_id' => 'required|array',
            'classes_id.*' => 'exists:classes,id',
        ], [
            'subject_id.required' => 'Mapel harus dipilih.',
            'classes_id.required' => 'Minimal pilih satu kelas.',
            'password.regex' => 'Password tidak boleh mengandung spasi',
        ]);

        $password = $request->password;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'plain_password' => $request->password,
            'status' => 'guru',
        ]);

        // Role Guru
        $user->assignRole('Guru');

        // SIMPAN KE TEACHER
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'NIP' => $request->NIP,
            'subject_id' => $request->subject_id,
        ]);

        // Assign ke kelas (pivot)
        $teacher->class()->sync($request->classes_id);

        return redirect()->back()->with('success', 'Guru berhasil ditambahkan & ditempatkan.');
    }

    public function import(Request $request)
    {
        Excel::import(new TeacherImport(), $request->file('file'));

        return back()->with('success', 'Import guru berhasil!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => "required|email|unique:users,email,$id",
            'password' => 'nullable|min:6',
            'NIP' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'classes_id' => 'required|array',
            'classes_id.*' => 'exists:classes,id',
        ]);

        // Ambil user guru berdasarkan ID
        $teacher = User::findOrFail($id);

        // Update data user
        $teacher->name = $request->name;
        $teacher->email = $request->email;
        $teacher->NIP = $request->NIP;
        $teacher->subject_id = $request->subject_id;

        // Jika password diisi → update
        if ($request->filled('password')) {
            $teacher->password = $request->password;  // TANPA HASH SESUAI PERMINTAAN
        }

        $teacher->save();

        // Update kelas di pivot
        $teacher->class()->sync($request->classes_id);

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);

        // Hanya hapus guru (role)
        if (!$teacher->hasRole('Guru')) {
            return back()->with('error', 'User ini bukan guru.');
        }

        // Hapus relasi kelas pivot
        $teacher->class()->detach();

        // Hapus role guru
        $teacher->removeRole('Guru');

        // Hapus user guru
        $teacher->delete();

        return back()->with('success', 'Guru berhasil dihapus.');
    }

}
