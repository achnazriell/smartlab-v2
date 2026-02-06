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
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search_teacher');
        $class_filter = $request->input('class_filter');
        $sort = $request->input('sort', 'newest');
        $per_page = $request->input('per_page', 10);

        // Query dasar untuk guru
        $query = User::with([
            'teacher',
            'teacher.teacherClasses.classes',
            'teacher.teacherClasses.subjects'
        ])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Guru');
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Admin');
            });

        // 🔍 SEARCH
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($q2) use ($search) {
                        $q2->where('nip', 'like', "%{$search}%");
                    });
            });
        }

        // 🎯 FILTER BY CLASS
        if ($class_filter) {
            $query->whereHas('teacher.teacherClasses.classes', function ($q) use ($class_filter) {
                $q->where('classes.id', $class_filter);
            });
        }

        // 📊 SORTING
        switch ($sort) {
            case 'oldest':
                $query->orderBy('users.created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('users.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('users.name', 'desc');
                break;
            case 'nip_asc':
                $query->leftJoin('teachers', 'teachers.user_id', '=', 'users.id')
                    ->orderBy('teachers.nip', 'asc');
                break;
            case 'nip_desc':
                $query->leftJoin('teachers', 'teachers.user_id', '=', 'users.id')
                    ->orderBy('teachers.nip', 'desc');
                break;
            default: // newest
                $query->orderBy('users.created_at', 'desc');
                break;
        }

        // Hitung statistik sebelum pagination
        $totalTeachers = $query->clone()->count();

        // Guru aktif = punya teacherClasses
        $activeTeachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Guru');
        })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Admin');
            })
            ->whereHas('teacher.teacherClasses')
            ->count();

        $inactiveTeachers = $totalTeachers - $activeTeachers;

        // Pagination
        $teachers = $query->paginate($per_page)->withQueryString();

        $classes = Classes::all();
        $subjects = Subject::all(); // Semua mapel yang sudah dibuat

        return view('Admins.Teachers.index', compact(
            'teachers',
            'subjects',
            'classes',
            'totalTeachers',
            'activeTeachers',
            'inactiveTeachers',
            'class_filter',
            'sort',
            'per_page'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^\S*$/',
            'nip' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                'unique:teachers,nip',
            ],
            'sapaan' => 'nullable|string|max:50',
            'selected_classes' => 'required|json',
        ], [
            'password.regex' => 'Password tidak boleh mengandung spasi',
            'nip.unique' => 'NIP sudah digunakan',
            'selected_classes.required' => 'Minimal pilih satu kelas',
        ]);

        // Parse selected classes JSON
        $selectedClasses = json_decode($request->selected_classes, true);
        if (!is_array($selectedClasses) || empty($selectedClasses)) {
            return back()->with('error', 'Harap pilih minimal satu kelas')->withInput();
        }

        DB::beginTransaction();
        try {
            // 1️⃣ BUAT USER
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'plain_password' => $request->password,
            ]);

            $user->assignRole('Guru');

            // 2️⃣ BUAT TEACHER
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'sapaan' => $request->sapaan,
            ]);

            // 3️⃣ BUAT TEACHER CLASSES DAN SUBJECTS
            foreach ($selectedClasses as $classId) {
                if (!empty($classId)) {
                    $teacherClass = TeacherClass::create([
                        'teacher_id' => $teacher->id,
                        'classes_id' => $classId,
                    ]);

                    // Sync subjects jika ada
                    $subjectKey = "classes_{$classId}_subject_ids";
                    if ($request->has($subjectKey)) {
                        $subjectIds = $request->input($subjectKey);
                        if (is_array($subjectIds) && !empty($subjectIds)) {
                            $teacherClass->subjects()->sync($subjectIds);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('teachers.index')->with('success', 'Guru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan guru: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $teacher = User::with([
            'teacher',
            'teacher.teacherClasses.classes',
            'teacher.teacherClasses.subjects'
        ])->findOrFail($id);

        // Transform data untuk response JSON
        $data = [
            'id' => $teacher->id,
            'name' => $teacher->name,
            'email' => $teacher->email,
            'teacher' => $teacher->teacher,
            'teacherClasses' => $teacher->teacher ? $teacher->teacher->teacherClasses->map(function ($tc) {
                return [
                    'classes_id' => $tc->classes_id,
                    'subjects' => $tc->subjects
                ];
            }) : []
        ];

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'nip' => [
                'nullable',
                'string',
                new ValidNIPGuru,
                Rule::unique('teachers', 'nip')->ignore($user->teacher->id ?? null),
            ],
            'sapaan' => 'nullable|string|max:50',
            'selected_classes' => 'required|json',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|regex:/^\S*$/';
        }

        $request->validate($rules, [
            'password.regex' => 'Password tidak boleh mengandung spasi',
            'selected_classes.required' => 'Minimal pilih satu kelas',
            'nip.unique' => 'NIP sudah digunakan',
        ]);

        // Parse selected classes JSON
        $selectedClasses = json_decode($request->selected_classes, true);
        if (!is_array($selectedClasses) || empty($selectedClasses)) {
            return back()->with('error', 'Harap pilih minimal satu kelas')->withInput();
        }

        DB::beginTransaction();
        try {
            // Update user
            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->plain_password = $request->password;
            }

            $user->save();

            // Update teacher
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
            $teacher->nip = $request->nip;
            $teacher->sapaan = $request->sapaan;
            $teacher->save();

            // Hapus semua relasi sebelumnya
            $teacher->teacherClasses()->delete();

            // Buat relasi baru
            foreach ($selectedClasses as $classId) {
                if (!empty($classId)) {
                    $teacherClass = TeacherClass::create([
                        'teacher_id' => $teacher->id,
                        'classes_id' => $classId,
                    ]);

                    // Sync subjects jika ada
                    $subjectKey = "classes_{$classId}_subject_ids";
                    if ($request->has($subjectKey)) {
                        $subjectIds = $request->input($subjectKey);
                        if (is_array($subjectIds) && !empty($subjectIds)) {
                            $teacherClass->subjects()->sync($subjectIds);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Get all subjects (API endpoint) - Semua mapel yang sudah dibuat
     */
    public function getAllSubjects()
    {
        try {
            $subjects = Subject::orderBy('name_subject')->get();
            return response()->json($subjects);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Hanya hapus guru (role)
        if (!$user->hasRole('Guru')) {
            return back()->with('error', 'User ini bukan guru.');
        }

        DB::beginTransaction();
        try {
            // Hapus relasi teacher_classes terlebih dahulu
            if ($user->teacher) {
                $user->teacher->teacherClasses()->delete();
                $user->teacher->delete();
            }

            // Hapus role guru
            $user->removeRole('Guru');

            // Hapus user
            $user->delete();

            DB::commit();
            return back()->with('success', 'Guru berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus guru: ' . $e->getMessage());
        }
    }

    /**
     * Import teachers from Excel/CSV
     */
// 🔄 GANTI BAGIAN IMPORT PADA TeacherController.php

    /**
     * Import teachers from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            $import = new TeacherImport();
            Excel::import($import, $request->file('file'));

            // Get import statistics
            $stats = $import->getImportStats();

            // Simpan stats ke session untuk ditampilkan di view
            session()->flash('import_stats', $stats);

            // Tentukan tipe flash message
            if ($stats['errors'] > 0) {
                return back()->with('import_status', 'warning');
            } elseif ($stats['success'] > 0) {
                return back()->with('import_status', 'success');
            } else {
                return back()->with('import_status', 'info');
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $row = $failure->row();
                $errors[] = "Baris {$row}: " . implode(', ', $failure->errors());
            }

            return back()->with('error', 'Terdapat data yang tidak valid. ' . implode('; ', array_slice($errors, 0, 5)));
        } catch (\Exception $e) {
            \Log::error('Import guru error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
    }


    /**
     * Download import template
     */
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

    /**
     * Preview import data
     */
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

    /**
     * Get teacher detail for quick view
     */
    public function detail($id)
    {
        $teacher = Teacher::with([
            'teacherClasses.classes',
            'teacherClasses.subjects'
        ])->findOrFail($id);

        $data = $teacher->teacherClasses
            ->map(function ($tc) {
                return [
                    'class' => $tc->classes->name_class ?? '-',
                    'subjects' => $tc->subjects->pluck('name_subject')->toArray(),
                ];
            });

        return response()->json($data);
    }

    /**
     * Get subjects by class ID (API endpoint)
     */


    /**
     * Export filtered data
     */
    public function exportFiltered(Request $request)
    {
        // Implementasi export data dengan filter yang sama
        // Bisa menggunakan Maatwebsite/Excel
    }
}
