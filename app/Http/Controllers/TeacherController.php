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
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search_teacher');
        $class_filter = $request->input('class_filter');
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_order = $request->input('sort_order', 'desc');

        $query = User::with([
                'teacher',
                'teacher.teacherClasses.classes',
                'teacher.teacherClasses.subjects'
            ])
            ->select('users.*', 'teachers.nip', 'teachers.created_at as teacher_created_at')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'users.id')
            ->whereHas('roles', fn($q) => $q->where('name', 'Guru'))
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'Admin'));

        // 🔍 SEARCH
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('teachers.nip', 'like', "%{$search}%");
            });
        }

        // 🎯 FILTER BY CLASS
        if ($class_filter) {
            $query->whereHas('teacher.teacherClasses.classes', function ($q) use ($class_filter) {
                $q->where('classes.id', $class_filter);
            });
        }

        // 📊 SORTING
        switch ($sort_by) {
            case 'name':
                $query->orderBy('users.name', $sort_order);
                break;
            case 'email':
                $query->orderBy('users.email', $sort_order);
                break;
            case 'nip':
                $query->orderBy('teachers.nip', $sort_order);
                break;
            case 'class':
                $query->leftJoin('teacher_classes', 'teacher_classes.teacher_id', '=', 'teachers.id')
                    ->leftJoin('classes', 'classes.id', '=', 'teacher_classes.classes_id')
                    ->orderBy('classes.name_class', $sort_order)
                    ->groupBy('users.id');
                break;
            default:
                $query->orderBy('users.created_at', $sort_order);
                break;
        }

        // Tambahkan sorting tambahan untuk konsistensi
        if ($sort_by != 'name') {
            $query->orderBy('users.name', 'asc');
        }

        // 🔄 Urutkan guru yang belum punya kelas di atas
        $query->orderByRaw('NOT EXISTS (
            SELECT 1
            FROM teacher_classes
            WHERE teacher_classes.teacher_id = teachers.id
        ) DESC');

        $teachers = $query->paginate(10)->withQueryString();

        $classes = Classes::all();
        $subjects = Subject::all();

        return view('Admins.Teachers.index', compact('teachers', 'subjects', 'classes', 'class_filter', 'sort_by', 'sort_order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^\S*$/',
            'nip' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                'unique:teachers,nip',
            ],

            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',

            'subjects' => 'required|array',
            'subjects.*' => 'required|array',
            'subjects.*.*' => 'exists:subjects,id',
        ], [
            'class_id.required' => 'Minimal pilih satu kelas',
            'subjects.required' => 'Mapel per kelas wajib diisi',
            'nip.unique' => 'nip sudah digunakan',
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
            'nip' => $request->nip,
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
            'file' => 'required|mimes:xlsx,xls,csv|max:5120' // 5MB
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new TeacherImport();

            // Import data dengan progress
            Excel::import($import, $request->file('file'));

            // Ambil statistik import
            $stats = $import->getImportStats();

            // Siapkan pesan notifikasi
            $message = "Import selesai!<br>";

            if ($stats['success'] > 0) {
                $message .= "✅ <strong>{$stats['success']}</strong> data guru berhasil diimport.<br>";
            }

            if ($stats['skipped'] > 0) {
                $message .= "⚠️ <strong>{$stats['skipped']}</strong> data dilewati (sudah ada/duplikat).<br>";
            }

            if ($stats['errors'] > 0) {
                $message .= "❌ <strong>{$stats['errors']}</strong> data error.<br>";

                // Jika ada kelas baru yang dibuat
                if ($stats['new_classes'] > 0) {
                    $message .= "📚 <strong>{$stats['new_classes']}</strong> kelas baru dibuat.<br>";
                }

                // Jika ada mata pelajaran baru yang dibuat
                if ($stats['new_subjects'] > 0) {
                    $message .= "📖 <strong>{$stats['new_subjects']}</strong> mata pelajaran baru dibuat.<br>";
                }

                return back()
                    ->with('info', $message)
                    ->with('errors', $import->getErrors());
            }

            // Jika semua sukses
            $message .= "📚 <strong>{$stats['new_classes']}</strong> kelas baru dibuat.<br>";
            $message .= "📖 <strong>{$stats['new_subjects']}</strong> mata pelajaran baru dibuat.";

            return back()->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $row = $failure->row();
                $errors[] = "Baris {$row}: " . implode(', ', $failure->errors());
            }

            return back()
                ->with('error', 'Terdapat data yang tidak valid:')
                ->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_import_guru.xlsx');

        if (!file_exists($path)) {
            // Buat template jika belum ada
            $this->createTemplate();
        }

        return response()->download($path, 'Template_Import_Guru.xlsx');
    }

    private function createTemplate()
    {
        // Implementasi pembuatan template Excel
        // Bisa menggunakan PHPExcel atau library lainnya
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
                    'class' => $tc->classes->name_class ?? '-',
                    'subjects' => $tc->subjects->pluck('name_subject')->toArray(),
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
            'nip' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                Rule::unique('teachers', 'nip')->ignore($user->teacher->id ?? null),
            ],
            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',
            'subjects' => 'required|array',
            'subjects.*' => 'required|array',
            'subjects.*.*' => 'exists:subjects,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|regex:/^\S*$/';
        }

        $request->validate($rules, [
            'password.regex' => 'Password tidak boleh mengandung spasi',
            'class_id.required' => 'Minimal pilih satu kelas',
            'subjects.required' => 'Mapel per kelas wajib diisi',
        ]);

        // Update user
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->plain_password = $request->password;
        }

        $user->save();

        // Update teacher
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $teacher->nip = $request->nip;
        $teacher->save();

        // Hapus semua relasi sebelumnya
        $teacher->teacherClasses()->delete();

        // Buat relasi baru
        foreach ($request->class_id as $classId) {
            $teacherClass = TeacherClass::create([
                'teacher_id' => $teacher->id,
                'classes_id' => $classId,
            ]);

            if (isset($request->subjects[$classId])) {
                $teacherClass->subjects()->sync($request->subjects[$classId]);
            }
        }

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);

        // Hanya hapus guru (role)
        if (! $teacher->hasRole('Guru')) {
            return back()->with('error', 'User ini bukan guru.');
        }

        // Hapus relasi teacher_classes terlebih dahulu
        if ($teacher->teacher) {
            $teacher->teacher->teacherClasses()->delete();
            $teacher->teacher->delete();
        }

        // Hapus role guru
        $teacher->removeRole('Guru');

        // Hapus user
        $teacher->delete();

        return back()->with('success', 'Guru berhasil dihapus.');
    }

    public function exportFiltered(Request $request)
    {
        // Implementasi export data dengan filter yang sama
        // Bisa menggunakan Maatwebsite/Excel
    }
}

