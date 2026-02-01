<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search_student;
        $classFilter = $request->class_filter;
        $sort = $request->sort ?? 'newest';
        $perPage = $request->per_page ?? 10;

        $students = Student::with(['user', 'class'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })
                    ->orWhere('nis', 'like', "%$search%")
                    ->orWhereHas('class', function ($q) use ($search) {
                        $q->where('name_class', 'like', "%$search%");
                    });
            })
            ->when($classFilter, function ($query) use ($classFilter) {
                $query->where('class_id', $classFilter);
            })
            ->when($sort, function ($query) use ($sort) {
                if ($sort === 'newest') {
                    $query->orderBy('created_at', 'desc');
                } elseif ($sort === 'oldest') {
                    $query->orderBy('created_at', 'asc');
                } elseif ($sort === 'name_asc') {
                    $query->join('users', 'students.user_id', '=', 'users.id')
                        ->orderBy('users.name', 'asc')
                        ->select('students.*');
                } elseif ($sort === 'name_desc') {
                    $query->join('users', 'students.user_id', '=', 'users.id')
                        ->orderBy('users.name', 'desc')
                        ->select('students.*');
                } elseif ($sort === 'nis_asc') {
                    $query->orderBy('nis', 'asc');
                } elseif ($sort === 'nis_desc') {
                    $query->orderBy('nis', 'desc');
                }
            })
            ->paginate($perPage)
            ->withQueryString();

        $classes = Classes::all();

        return view('Admins.Students.index', compact('students', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => [
                'required',
                'regex:/^[0-9]{6,10}$/',
                'unique:students,nis'
            ],
            'class_id' => 'nullable|exists:classes,id',
        ], [
            'nis.regex' => 'NIS harus terdiri dari 6-10 digit angka',
            'nis.unique' => 'NIS sudah terdaftar',
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

        // 3. Student - Hapus status karena default sudah 'siswa'
        Student::create([
            'user_id' => $user->id,
            'nis' => $request->nis,
            'class_id' => $request->class_id,
            // Hapus status karena semua siswa baru adalah siswa aktif
        ]);

        return redirect()->back()->with('success', 'Murid berhasil ditambahkan.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            $import = new StudentImport;

            // Import data
            Excel::import($import, $request->file('file'));

            // Get import statistics
            $stats = $import->getImportStats();

            // Buat pesan notifikasi yang lebih informatif
            $message = "<div class='text-left'>";

            // Header pesan
            if ($stats['imported'] > 0) {
                $message .= "<p class='font-bold text-green-600 text-lg mb-2'>✅ Import Berhasil!</p>";
            } elseif (!empty($stats['errors'])) {
                $message .= "<p class='font-bold text-red-600 text-lg mb-2'>⚠️ Import dengan Beberapa Masalah</p>";
            } else {
                $message .= "<p class='font-bold text-amber-600 text-lg mb-2'>ℹ️ Tidak Ada Data Baru</p>";
            }

            // Statistik utama
            $message .= "<div class='space-y-1'>";
            $message .= "<p><strong>📊 Ringkasan Import:</strong></p>";
            $message .= "<p>• Total data diproses: <strong>{$stats['total_processed']}</strong></p>";
            $message .= "<p>• Berhasil diimport: <strong class='text-green-600'>{$stats['imported']}</strong> murid</p>";

            if ($stats['duplicate'] > 0) {
                $message .= "<p>• Data duplikat: <strong class='text-amber-600'>{$stats['duplicate']}</strong> data (dilewati)</p>";
            }

            if ($stats['skipped'] > 0) {
                $message .= "<p>• Data dilewati: <strong class='text-slate-600'>{$stats['skipped']}</strong> data</p>";
            }

            // Kelas yang dibuat otomatis
            if (!empty($stats['created_classes'])) {
                $message .= "<p>• Kelas dibuat otomatis: <strong class='text-blue-600'>" . count($stats['created_classes']) . "</strong> kelas</p>";
                foreach ($stats['created_classes'] as $className => $count) {
                    $message .= "<p class='ml-4'>- {$className} ({$count} siswa)</p>";
                }
            }
            $message .= "</div>";

            // Warnings (data duplikat, kelas dibuat, dll)
            if (!empty($stats['warnings'])) {
                $message .= "<div class='mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg'>";
                $message .= "<p class='font-medium text-amber-800 mb-1'>⚠️ Peringatan:</p>";
                $message .= "<ul class='text-sm text-amber-700 space-y-1 max-h-40 overflow-y-auto'>";
                foreach (array_slice($stats['warnings'], 0, 10) as $warning) {
                    $message .= "<li class='flex items-start'>";
                    $message .= "<span class='mr-1'>•</span> {$warning}";
                    $message .= "</li>";
                }
                if (count($stats['warnings']) > 10) {
                    $message .= "<li class='text-xs italic'>... dan " . (count($stats['warnings']) - 10) . " peringatan lainnya</li>";
                }
                $message .= "</ul>";
                $message .= "</div>";
            }

            // Errors (data error validasi)
            if (!empty($stats['errors'])) {
                $message .= "<div class='mt-3 p-3 bg-red-50 border border-red-200 rounded-lg'>";
                $message .= "<p class='font-medium text-red-800 mb-1'>❌ Error yang ditemukan:</p>";
                $message .= "<ul class='text-sm text-red-700 space-y-1 max-h-40 overflow-y-auto'>";
                foreach (array_slice($stats['errors'], 0, 10) as $error) {
                    $message .= "<li class='flex items-start'>";
                    $message .= "<span class='mr-1'>•</span> {$error}";
                    $message .= "</li>";
                }
                if (count($stats['errors']) > 10) {
                    $message .= "<li class='text-xs italic'>... dan " . (count($stats['errors']) - 10) . " error lainnya</li>";
                }
                $message .= "</ul>";
                $message .= "</div>";
            }

            // Success message contoh
            if ($stats['imported'] > 0) {
                $message .= "<div class='mt-3 p-3 bg-green-50 border border-green-200 rounded-lg'>";
                $message .= "<p class='font-medium text-green-800'>🎉 Import berhasil menambahkan {$stats['imported']} data siswa baru!</p>";
                if ($stats['duplicate'] > 0) {
                    $message .= "<p class='text-green-700 text-sm mt-1'>{$stats['duplicate']} data duplikat dilewati.</p>";
                }
                $message .= "</div>";
            }

            $message .= "</div>";

            // Tentukan tipe flash message
            if (!empty($stats['errors'])) {
                return back()->with('error', $message);
            } elseif ($stats['imported'] > 0) {
                return back()->with('success', $message);
            } else {
                return back()->with('info', $message);
            }
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            \Log::error('File: ' . $request->file('file')->getClientOriginalName());
            return back()->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        // Temukan student berdasarkan ID
        $student = Student::findOrFail($id);
        $userId = $student->user_id;

        $request->validate([
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'nullable|min:6',
            'nis' => [
                'required',
                'regex:/^[0-9]{6,10}$/',
                Rule::unique('students')->ignore($student->id)
            ],
            'class_id' => 'nullable|exists:classes,id',
        ], [
            'nis.regex' => 'NIS harus terdiri dari 6-10 digit angka',
            'nis.unique' => 'NIS sudah terdaftar',
        ]);


        // Update user
        $user = User::findOrFail($userId);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Update password jika diisi
        if ($request->password) {
            $updateData['password'] = bcrypt($request->password);
            $updateData['plain_password'] = $request->password;
        }

        $user->update($updateData);

        // Update student
        $student->update([
            'nis' => $request->nis,
            'class_id' => $request->class_id,
        ]);

        return redirect()->route('students.index')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Student::where('user_id', $id)->delete();
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Murid berhasil dihapus.');
    }

    /**
     * Ekspor template Excel
     */
    public function exportTemplate()
    {
        $data = collect([
            ['nama', 'email', 'nis', 'password', 'kelas'],
            ['Ahmad Budi Santoso', 'ahmad@email.com', '2023001', 'siswa123', 'X IPA 1'],
            ['Siti Nurhaliza', 'siti@email.com', '2023002', '', 'X IPS 2'],
            ['Rina Wijaya', 'rina@email.com', '2023003', '', 'XI IPA 1'],
        ]);

        $csv = $data->map(function ($row) {
            return implode(',', $row);
        })->implode("\n");

        $fileName = 'template_import_murid_' . date('Ymd_His') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function edit($id)
    {
        $student = Student::with(['user', 'class'])->findOrFail($id);
        return response()->json([
            'id' => $student->id,
            'user' => [
                'name' => $student->user->name,
                'email' => $student->user->email
            ],
            'nis' => $student->nis,
            'class_id' => $student->class_id
        ]);
    }

    public function detail($id)
    {
        $student = Student::with(['user', 'class'])->findOrFail($id);
        return response()->json([
            'name' => $student->user->name,
            'email' => $student->user->email,
            'nis' => $student->nis,
            'class' => $student->class ? $student->class->name_class : null,
            'password' => $student->user->plain_password,
            'created_at' => $student->created_at->format('d/m/Y H:i')
        ]);
    }
}

