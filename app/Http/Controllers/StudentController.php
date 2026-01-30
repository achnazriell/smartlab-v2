<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Builder\Class_;
use Illuminate\Validation\Rule;


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
            ->paginate(10);
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
            'class_id' => 'required|exists:classes,id',
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            $import = new StudentImport;

            // Import data
            Excel::import($import, $request->file('file'));

            // Get import statistics
            $stats = $import->getImportStats();

            // Untuk debugging, lihat apa yang ada di failures
            $failures = $import->failures();

            // Prepare response message dengan HTML yang benar
            $message = "Import selesai!<br>";
            $message .= "• Berhasil diimport: {$stats['imported']} murid<br>";

            if ($stats['skipped'] > 0) {
                $message .= "• Dilewati: {$stats['skipped']} data<br>";
            }

            // Jika ada failures dari validation
            if (!empty($failures)) {
                $message .= "• Validasi gagal:<br>";
                foreach ($failures as $failure) {
                    $row = $failure->row();
                    $errors = implode(', ', $failure->errors());
                    $message .= "&nbsp;&nbsp;- Baris {$row}: {$errors}<br>";
                }
            }

            // Jika ada import errors
            if (!empty($stats['errors'])) {
                $message .= "• Error yang ditemukan:<br>";
                foreach ($stats['errors'] as $error) {
                    $message .= "&nbsp;&nbsp;- {$error}<br>";
                }
            }

            // Tentukan jenis pesan
            if ($stats['imported'] > 0) {
                return back()->with('success', $message);
            } elseif (!empty($stats['errors']) || !empty($failures)) {
                return back()->with('error', $message);
            } else {
                return back()->with('warning', $message);
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
            'class_id' => 'required|exists:classes,id',
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

        return redirect()->route('Students')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Student::where('user_id', $id)->delete();
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Murid berhasil dihapus.');
    }
}
