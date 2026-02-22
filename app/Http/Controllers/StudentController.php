<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Department;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\StudentCodeHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $search      = $request->search_student;
        $classFilter = $request->class_filter;
        $sort        = $request->sort ?? 'newest';
        $perPage     = $request->per_page ?? 10;
        $academicYearParam = $request->academic_year; // bisa null atau string kosong

        // Tentukan tahun ajaran yang akan digunakan
        if ($academicYearParam === null) {
            // Parameter tidak dikirim, default ke tahun aktif
            $activeYear = AcademicYear::active()->first();
            $academicYearId = $activeYear?->id;
        } else {
            // Parameter dikirim, bisa berisi ID atau string kosong (Semua Tahun)
            $academicYearId = $academicYearParam ?: null; // konversi empty string ke null
        }

        $students = Student::with([
            'user',
            'classAssignments' => function ($q) use ($academicYearId) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId)->with('class');
                } else {
                    // Jika tidak ada filter tahun, tampilkan semua assignment (opsional, bisa juga kosong)
                    $q->with('class');
                }
            },
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    })
                        ->orWhere('nis', 'like', "%$search%")
                        ->orWhereHas('classAssignments.class', function ($c) use ($search) {
                            $c->where('name_class', 'like', "%$search%");
                        });
                });
            })
            ->when($classFilter && $academicYearId, function ($query) use ($classFilter, $academicYearId) {
                // Filter kelas hanya berlaku jika ada tahun ajaran tertentu
                $query->whereHas('classAssignments', function ($q) use ($classFilter, $academicYearId) {
                    $q->where('class_id', $classFilter)
                        ->where('academic_year_id', $academicYearId);
                });
            })
            ->when($academicYearId, function ($query) use ($academicYearId) {
                // Hanya tampilkan siswa yang memiliki assignment di tahun tersebut
                $query->whereHas('classAssignments', function ($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            })
            ->when($sort, function ($query) use ($sort) {
                match ($sort) {
                    'oldest'    => $query->orderBy('created_at', 'asc'),
                    'name_asc'  => $query->join('users', 'students.user_id', '=', 'users.id')
                        ->orderBy('users.name', 'asc')
                        ->select('students.*'),
                    'name_desc' => $query->join('users', 'students.user_id', '=', 'users.id')
                        ->orderBy('users.name', 'desc')
                        ->select('students.*'),
                    'nis_asc'   => $query->orderBy('nis', 'asc'),
                    'nis_desc'  => $query->orderBy('nis', 'desc'),
                    default     => $query->orderBy('created_at', 'desc'),
                };
            })
            ->paginate($perPage)
            ->withQueryString();

        $totalStudents = Student::count();
        $totalClasses  = Classes::count();

        // Hitung siswa yang memiliki kelas di tahun yang dipilih (untuk statistik)
        $studentsInClasses = $academicYearId
            ? Student::whereHas('classAssignments', fn($q) => $q->where('academic_year_id', $academicYearId))->count()
            : Student::whereHas('classAssignments')->count(); // jika semua tahun, hitung yang punya kelas

        $avgPerClass = $totalClasses > 0 ? round($totalStudents / $totalClasses, 1) : 0;
        $classes     = Classes::all();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $departments = Department::all();

        // Ambil tahun aktif untuk keperluan lain (misal di modal)
        $activeYear = AcademicYear::active()->first();

        return view('Admins.Students.index', compact(
            'students',
            'classes',
            'studentsInClasses',
            'totalClasses',
            'avgPerClass',
            'activeYear',
            'academicYears',
            'departments'
        ));
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis'      => ['required', 'regex:/^[0-9]{6,10}$/', 'unique:students,nis'],
            'class_id' => 'nullable|exists:classes,id',
        ], [
            'nis.regex'  => 'NIS harus terdiri dari 6–10 digit angka.',
            'nis.unique' => 'NIS sudah terdaftar.',
        ]);

        $activeAcademicYear = AcademicYear::active()->first();
        if (! $activeAcademicYear) {
            return back()->with('error', 'Tahun ajaran aktif belum ditentukan.')->withInput();
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => bcrypt($request->password),
                'plain_password' => $request->password,
            ]);
            $user->assignRole('Murid');

            $student = Student::create([
                'user_id'      => $user->id,
                'nis'          => $request->nis,
                'student_code' => null,
                'status'       => 'siswa',
            ]);

            if ($request->filled('class_id')) {
                StudentClassAssignment::create([
                    'student_id'       => $student->id,
                    'class_id'         => $request->class_id,
                    'academic_year_id' => $activeAcademicYear->id,
                ]);
                $student->assignNewCode($request->class_id, $activeAcademicYear->id);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Murid berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan murid: ' . $e->getMessage())->withInput();
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $userId  = $student->user_id;

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'password' => 'nullable|min:6',
            'nis'      => ['required', 'regex:/^[0-9]{6,10}$/', Rule::unique('students')->ignore($student->id)],
            'class_id' => 'nullable|exists:classes,id',
        ], [
            'nis.regex'  => 'NIS harus terdiri dari 6–10 digit angka.',
            'nis.unique' => 'NIS sudah terdaftar.',
        ]);

        $activeAcademicYear = AcademicYear::active()->first();
        if (! $activeAcademicYear) {
            return back()->with('error', 'Tahun ajaran aktif belum ditentukan.')->withInput();
        }

        DB::beginTransaction();
        try {
            $user        = User::findOrFail($userId);
            $user->name  = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password       = bcrypt($request->password);
                $user->plain_password = $request->password;
            }
            $user->save();

            $student->nis = $request->nis;
            $student->save();

            // Hapus assignment lama di tahun ajaran aktif
            StudentClassAssignment::where('student_id', $student->id)
                ->where('academic_year_id', $activeAcademicYear->id)
                ->delete();

            if ($request->filled('class_id')) {
                StudentClassAssignment::create([
                    'student_id'       => $student->id,
                    'class_id'         => $request->class_id,
                    'academic_year_id' => $activeAcademicYear->id,
                ]);
                $student->assignNewCode($request->class_id, $activeAcademicYear->id);
            } else {
                $student->student_code = null;
                $student->save();
            }

            DB::commit();
            return redirect()->route('students.index')->with('success', 'Data murid berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data murid: ' . $e->getMessage())->withInput();
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Student $student)
    {
        DB::beginTransaction();
        try {
            StudentClassAssignment::where('student_id', $student->id)->delete();
            StudentCodeHistory::where('student_id', $student->id)->delete();
            $student->user->delete();
            $student->delete();
            DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Murid berhasil dihapus.']);
            }
            return redirect()->route('students.index')->with('success', 'Murid berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus murid: ' . $e->getMessage()], 500);
            }
            return redirect()->route('students.index')->with('error', 'Gagal menghapus murid: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // EDIT (JSON untuk modal)
    // =========================================================================

    public function edit($id)
    {
        $student = Student::with('user')->findOrFail($id);

        $activeAcademicYear = AcademicYear::active()->first();
        $currentAssignment  = null;
        if ($activeAcademicYear) {
            $currentAssignment = StudentClassAssignment::where('student_id', $student->id)
                ->where('academic_year_id', $activeAcademicYear->id)
                ->first();
        }

        return response()->json([
            'id'       => $student->id,
            'user'     => [
                'name'  => $student->user->name,
                'email' => $student->user->email,
            ],
            'nis'      => $student->nis,
            'class_id' => $currentAssignment?->class_id,
        ]);
    }

    // =========================================================================
    // DETAIL (JSON untuk modal detail)
    // =========================================================================

    public function detail($id)
    {
        $student = Student::with('user')->findOrFail($id);

        $activeYear        = AcademicYear::active()->first();
        $currentClass      = null;

        if ($activeYear) {
            $currentAssignment = StudentClassAssignment::with('class')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->first();
            $currentClass = $currentAssignment?->class?->name_class;
        }

        $classHistory = StudentClassAssignment::with('class', 'academicYear')
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id', 'desc')
            ->get()
            ->map(fn($a) => [
                'academic_year' => $a->academicYear->name,
                'class'         => $a->class?->name_class ?? '-',
            ]);

        $codeHistory = StudentCodeHistory::with('academicYear', 'class')
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id', 'desc')
            ->get()
            ->map(fn($h) => [
                'academic_year' => $h->academicYear->name,
                'class'         => $h->class?->name_class ?? '-',
                'student_code'  => $h->student_code,
            ]);

        return response()->json([
            'id'            => $student->id,
            'name'          => $student->user->name,
            'email'         => $student->user->email,
            'nis'           => $student->nis,
            'student_code'  => $student->student_code,
            'current_class' => $currentClass,
            'class_history' => $classHistory,
            'code_history'  => $codeHistory,
            'password'      => $student->user->plain_password,
            'created_at'    => $student->created_at->format('d/m/Y H:i'),
        ]);
    }

    // =========================================================================
    // UPDATE CLASS (pindah nomor kelas / rolling)
    // =========================================================================

    /**
     * Pindahkan siswa ke nomor kelas lain dalam grade & jurusan yang sama.
     *
     * Contoh: siswa di "XII RPL 1" + class_number=2 → pindah ke "XII RPL 2"
     *
     * POST /students/{id}/update-class
     * Body: class_number (int, required), academic_year_id (int, required)
     */
    public function updateClass(Request $request, $id)
    {
        $request->validate([
            'class_number'     => 'required|integer|min:1',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $student = Student::findOrFail($id);

        DB::beginTransaction();
        try {
            $currentAssignment = StudentClassAssignment::with('class')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $request->academic_year_id)
                ->first();

            if (! $currentAssignment || ! $currentAssignment->class) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa belum memiliki kelas di tahun ajaran ini.',
                ], 422);
            }

            $currentClass = $currentAssignment->class;

            // PERBAIKAN: gunakan helper statis dari Student model
            $grade        = Student::extractGrade($currentClass->name_class);
            $deptId       = (int) $currentClass->department_id;
            $targetNumber = (string) $request->class_number;

            // Cari kelas tujuan: grade sama, jurusan sama, nomor kelas = targetNumber
            $targetClass = Classes::where('department_id', $deptId)
                ->get()
                ->first(function ($cls) use ($grade, $targetNumber) {
                    return Student::extractGrade($cls->name_class)       === $grade
                        && Student::extractClassNumber($cls->name_class) === $targetNumber;
                });

            if (! $targetClass) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Kelas {$grade} nomor {$targetNumber} dengan jurusan yang sama tidak ditemukan.",
                ], 422);
            }

            if ($targetClass->id === $currentClass->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor kelas tujuan sama dengan kelas saat ini.',
                ], 422);
            }

            // Hapus assignment lama, buat yang baru
            StudentClassAssignment::where('student_id', $student->id)
                ->where('academic_year_id', $request->academic_year_id)
                ->delete();

            StudentClassAssignment::create([
                'student_id'       => $student->id,
                'class_id'         => $targetClass->id,
                'academic_year_id' => $request->academic_year_id,
            ]);

            $student->assignNewCode($targetClass->id, $request->academic_year_id);

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => "Siswa berhasil dipindah ke {$targetClass->name_class}.",
                'new_class' => $targetClass->name_class,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kelas: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // IMPORT & EXPORT TEMPLATE
    // =========================================================================

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $import = new StudentImport;
            Excel::import($import, $request->file('file'));
            $stats = $import->getImportStats();
            session()->flash('import_stats', $stats);

            if (! empty($stats['errors'])) {
                return back()->with('import_status', 'warning');
            }
            if ($stats['imported'] > 0) {
                return back()->with('import_status', 'success');
            }
            return back()->with('import_status', 'info');
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $search = $request->search;
        $grade = $request->grade;
        $departmentId = $request->department_id;
        $classId = $request->class_id;
        $limit = $request->limit ? (int)$request->limit : 0;
        $fields = $request->input('fields', ['name', 'email', 'nis', 'class']); // default

        $activeYear = AcademicYear::active()->first();
        $activeYearId = $activeYear?->id;

        $query = Student::with(['user', 'classAssignments' => function ($q) use ($activeYearId) {
            if ($activeYearId) {
                $q->where('academic_year_id', $activeYearId)->with('class');
            }
        }]);

        // Filter pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })->orWhere('nis', 'like', "%$search%");
            });
        }

        // Filter berdasarkan kelas (jika dipilih langsung)
        if ($classId && $activeYearId) {
            $query->whereHas('classAssignments', function ($q) use ($classId, $activeYearId) {
                $q->where('class_id', $classId)->where('academic_year_id', $activeYearId);
            });
        } else {
            // Filter berdasarkan grade + department
            if ($grade || $departmentId) {
                $query->whereHas('classAssignments', function ($q) use ($grade, $departmentId, $activeYearId) {
                    $q->whereHas('class', function ($c) use ($grade, $departmentId) {
                        if ($grade) {
                            $c->where('name_class', 'like', $grade . '%');
                        }
                        if ($departmentId) {
                            $c->where('department_id', $departmentId);
                        }
                    });
                    if ($activeYearId) {
                        $q->where('academic_year_id', $activeYearId);
                    }
                });
            }
        }

        // Batasi jumlah
        if ($limit > 0) {
            $students = $query->limit($limit)->get();
        } else {
            $students = $query->get();
        }

        // Siapkan data untuk CSV
        $data = [];
        $headers = [];

        // Mapping field ke label
        $fieldLabels = [
            'name' => 'Nama',
            'email' => 'Email',
            'nis' => 'NIS',
            'class' => 'Kelas',
            'student_code' => 'Kode Siswa',
            'password' => 'Password',
            'created_at' => 'Tanggal Daftar',
        ];

        // Tentukan header berdasarkan fields yang dipilih
        foreach ($fields as $field) {
            if (isset($fieldLabels[$field])) {
                $headers[] = $fieldLabels[$field];
            }
        }

        // Jika tidak ada field yang dipilih, gunakan default
        if (empty($headers)) {
            $headers = ['Nama', 'Email', 'NIS', 'Kelas'];
            $fields = ['name', 'email', 'nis', 'class'];
        }

        foreach ($students as $student) {
            $row = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'name':
                        $row[] = $student->user->name ?? '';
                        break;
                    case 'email':
                        $row[] = $student->user->email ?? '';
                        break;
                    case 'nis':
                        $row[] = $student->nis ?? '';
                        break;
                    case 'class':
                        $currentAssignment = $student->classAssignments->first();
                        $row[] = $currentAssignment?->class?->name_class ?? '';
                        break;
                    case 'student_code':
                        $row[] = $student->student_code ?? '';
                        break;
                    case 'password':
                        $row[] = $student->user->plain_password ?? '';
                        break;
                    case 'created_at':
                        $row[] = $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : '';
                        break;
                    default:
                        $row[] = '';
                }
            }
            $data[] = $row;
        }

        // Buat CSV
        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            // Tambahkan BOM untuk UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $filename = 'data-siswa-' . date('Y-m-d-His') . '.csv';
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function printAttendance(Request $request)
    {
        $classId = $request->class_id;
        $academicYearId = $request->academic_year_id;
        $month = $request->month;
        $paperSize = $request->paper_size ?? 'A4-Portrait';

        // Konversi nilai paper_size ke format CSS @page
        $cssPaperSize = match ($paperSize) {
            'A4-Portrait' => 'A4',
            'A4-Landscape' => 'A4 landscape',
            'A3' => 'A3',
            'Letter' => 'letter',
            'Legal' => 'legal',
            default => 'A4',
        };

        if (!$classId || !$academicYearId) {
            return redirect()->back()->with('error', 'Pilih kelas dan tahun ajaran');
        }

        $academicYear = AcademicYear::findOrFail($academicYearId);
        $class = Classes::findOrFail($classId);

        $students = Student::whereHas('classAssignments', function ($q) use ($classId, $academicYearId) {
            $q->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId);
        })
            ->with('user')
            ->orderBy('nis')
            ->get();

        $data = [
            'class' => $class,
            'academicYear' => $academicYear,
            'students' => $students,
            'month' => $month,
            'date' => now()->format('d F Y'),
            'paperSize' => $paperSize,
            'cssPaperSize' => $cssPaperSize,
            'kepalaSekolah' => 'Prapri Widodo', // Data dari profil sekolah
        ];

        return view('Admins.Students.print-attendance', $data);
    }
}
