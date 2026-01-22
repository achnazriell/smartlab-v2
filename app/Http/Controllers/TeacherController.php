<?php

namespace App\Http\Controllers;

use App\Imports\TeacherImport;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherClass;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use App\Rules\ValidNIPGuru;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request);
        $search = $request->input('search_teacher', '');
        $query = User::with(['teacher', 'teacher.teacherClasses.classes', 'teacher.teacherClasses.subjects'])
            ->select('users.*')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'teachers.id') // join ke tabel teachers
            ->whereHas('roles', fn($q) => $q->where('name', 'Guru'))
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Admin');
            })
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
            'password' => 'required|string|min:8|regex:/^\S*$/',
            'NIP' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                'unique:teachers,NIP',
            ],

            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',

            'subjects' => 'required|array',
            'subjects.*' => 'required|array',
            'subjects.*.*' => 'exists:subjects,id',
        ], [
            'class_id.required' => 'Minimal pilih satu kelas',
            'subjects.required' => 'Mapel per kelas wajib diisi',
            'NIP.unique' => 'NIP sudah digunakan',
            'password.regex' => 'Password tidak boleh mengandung spasi',
        ]);

        // 1️⃣ BUAT USER
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'plain_password' => $request->password,
            'status' => 'guru',
        ]);

        $user->assignRole('Guru');

        // 2️⃣ BUAT TEACHER (TANPA subject_id)
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'NIP' => $request->NIP,
        ]);

        foreach ($request->class_id as $classId) {

            // 1️⃣ buat relasi guru - kelas
            $teacherClass = TeacherClass::create([
                'teacher_id' => $teacher->id,
                'classes_id' => $classId,
            ]);

            // 2️⃣ isi mapel per kelas
            if (isset($request->subjects[$classId])) {
                $teacherClass->subjects()->sync(
                    $request->subjects[$classId]
                );
            }
        }

        return back()->with('success', 'Guru berhasil ditambahkan & ditempatkan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv',
            'file.max' => 'Ukuran file maksimal 2MB',
        ]);

        try {
            $import = new TeacherImport();

            // Import data
            Excel::import($import, $request->file('file'));

            // Cek jika ada error
            if (!empty($import->getErrors())) {
                return back()
                    ->with('error', 'Import selesai dengan beberapa error:')
                    ->with('errors', $import->getErrors());
            }

            return back()->with('success', 'Import guru berhasil!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()
                ->with('error', 'Terdapat data yang tidak valid:')
                ->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $data = Excel::toArray(new TeacherImport(), $request->file('file'));
        $headers = array_keys($data[0][0] ?? []);

        return view('Admins.Teachers.preview-import', [
            'data' => $data[0],
            'headers' => $headers,
        ]);
    }

    public function detail(Teacher $teacher)
    {
        $data = $teacher->teacherClasses()
            ->with(['classes', 'subjects'])
            ->get()
            ->map(function ($tc) {
                return [
                    'class' => $tc->classes->name,
                    'subjects' => $tc->subjects->pluck('name')->toArray(),
                ];
            });

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'NIP' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                Rule::unique('teachers', 'NIP')->ignore($user->teacher->id ?? null),
            ],
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|regex:/^\S*$/';
        }

        $request->validate($rules, [
            'password.regex' => 'Password tidak boleh mengandung spasi',
        ]);

        // Update user
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = $request->password; // tanpa hash (sesuai permintaanmu)
            $user->plain_password = $request->password;
        }

        $user->save();

        // Update teacher
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $teacher->NIP = $request->NIP;
        $teacher->save();

        $teacher->subjects()->sync($request->subject_id);
        $teacher->classes()->sync($request->classes_id);

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);

        // Hanya hapus guru (role)
        if (! $teacher->hasRole('Guru')) {
            return back()->with('error', 'User ini bukan guru.');
        }

        // Hapus relasi kelas pivot
        $teacher->classes()->detach();

        // Hapus role guru
        $teacher->removeRole('Guru');

        // Hapus user guru
        $teacher->delete();

        return back()->with('success', 'Guru berhasil dihapus.');
    }
}
