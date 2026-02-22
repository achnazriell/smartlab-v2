<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClassesRequest;
use App\Http\Requests\UpdateClassesRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ClassesImport;
use App\Models\Department;
use Dotenv\Validator;

class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search_class', '');
        $sort = $request->input('sort', 'newest');
        $grade = $request->input('grade', '');
        $perPage = $request->input('per_page', 10);

        $query = Classes::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_class', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Apply grade filter - PERBAIKAN: filter yang lebih spesifik
        if ($grade) {
            $query->where('name_class', 'like', $grade . ' %'); // Tambah spasi setelah grade
        }

        // Apply sorting
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name_class', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name_class', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $classes = $query->paginate($perPage)->withQueryString();

        // Calculate class statistics by grade - PERBAIKAN: Hitung yang benar
        $classStats = [
            'X' => Classes::where('name_class', 'like', 'X %')->orWhere('name_class', 'like', 'X-%')->count(),
            'XI' => Classes::where('name_class', 'like', 'XI %')->orWhere('name_class', 'like', 'XI-%')->count(),
            'XII' => Classes::where('name_class', 'like', 'XII %')->orWhere('name_class', 'like', 'XII-%')->count(),
        ];

        // Calculate total classes based on current filters
        $totalClasses = $classes->total();

        return view('Admins.Classes.index', compact(
            'classes',
            'search',
            'classStats',
            'totalClasses' // Tambahkan ini
        ));
    }


    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grade' => 'required|in:X,XI,XII',
            'department_id' => 'nullable|exists:departments,id',
            'class_number' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ], [
            'grade.required' => 'Angkatan wajib dipilih',
            'class_number.required' => 'Nomor kelas wajib diisi',
            'class_number.integer' => 'Nomor kelas harus angka',
        ]);

        // Generate name_class
        $department = Department::find($request->department_id);
        $departmentName = $department ? $department->name : '';
        // Ambil kode jurusan atau nama? Kita gunakan kode atau nama? Sesuai permintaan: setelah grade, jurusan (misal "RPL") lalu nomor.
        // Bisa menggunakan kode jurusan atau nama singkat. Kita gunakan kode jurusan jika ada.
        $deptCode = $department ? $department->code : '';
        $nameClass = trim($request->grade . ' ' . $deptCode . ' ' . $request->class_number);

        // Check duplicate
        $exists = Classes::where('name_class', $nameClass)->exists();
        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['name_class' => 'Kelas dengan nama ini sudah ada']);
        }

        Classes::create([
            'name_class' => $nameClass,
            'description' => $request->description,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('classes.index')->with('success', 'Kelas ' . $nameClass . ' berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $class = Classes::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $class->id,
                'name' => $class->name_class,
                'description' => $class->description,
                'createdAt' => $class->created_at->format('d/m/Y H:i'),
                'updatedAt' => $class->updated_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $class = Classes::findOrFail($id);
        // Pisahkan nama kelas: misal "XII RPL 3" -> grade "XII", deptCode "RPL", classNumber 3
        $parts = explode(' ', $class->name_class);
        $grade = $parts[0] ?? '';
        $deptCode = $parts[1] ?? '';
        $classNumber = isset($parts[2]) ? (int)$parts[2] : 1; // default 1 jika tidak ada

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $class->id,
                'grade' => $grade,
                'department_id' => $class->department_id,
                'class_number' => $classNumber,
                'description' => $class->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $class = Classes::findOrFail($id);

        $request->validate( [
            'grade' => 'required|in:X,XI,XII',
            'department_id' => 'nullable|exists:departments,id',
            'class_number' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $department = Department::find($request->department_id);
        $deptCode = $department ? $department->code : '';
        $nameClass = trim($request->grade . ' ' . $deptCode . ' ' . $request->class_number);

        // Check duplicate excluding current
        $exists = Classes::where('name_class', $nameClass)->where('id', '!=', $id)->exists();
        if ($exists) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas dengan nama ini sudah ada'
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['name_class' => 'Kelas dengan nama ini sudah ada']);
        }

        $class->update([
            'name_class' => $nameClass,
            'description' => $request->description,
            'department_id' => $request->department_id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil diperbarui'
            ]);
        }

        return redirect()->route('classes.index')->with('success', 'Kelas ' . $nameClass . ' berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $class = Classes::findOrFail($id);
            $className = $class->name_class;
            $class->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kelas ' . $className . ' berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick view for class details
     */
    public function detail($id)
    {
        $class = Classes::findOrFail($id);
        $grade = explode(' ', $class->name_class)[0] ?? '';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $class->id,
                'name' => $class->name_class,
                'description' => $class->description,
                'grade' => $grade,
                'createdAt' => $class->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Import classes from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new ClassesImport();
            Excel::import($import, $request->file('file'));

            $stats = $import->getImportStats();
            $message = "Import selesai! ";

            if ($stats['success'] > 0) {
                $message .= "✅ {$stats['success']} kelas berhasil diimport. ";
            }

            if ($stats['skipped'] > 0) {
                $message .= "⚠️ {$stats['skipped']} data dilewati (duplikat). ";
            }

            if ($stats['errors'] > 0) {
                $message .= "❌ {$stats['errors']} data error.";
                return back()
                    ->with('warning', $message)
                    ->with('import_errors', $import->getErrors());
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Export classes data
     */
    public function export(Request $request)
    {
        $search = $request->input('search_class', '');
        $grade = $request->input('grade', '');

        $query = Classes::query();

        if ($search) {
            $query->where('name_class', 'like', '%' . $search . '%');
        }

        // Apply grade filter
        if ($grade) {
            $query->where('name_class', 'like', $grade . ' %');
        }

        $classes = $query->get();

        $filename = 'data-kelas-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($classes) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, ['No', 'Nama Kelas', 'Deskripsi', 'Angkatan', 'Dibuat', 'Diperbarui']);

            foreach ($classes as $index => $class) {
                $grade = explode(' ', $class->name_class)[0] ?? '';
                fputcsv($file, [
                    $index + 1,
                    $class->name_class,
                    $class->description ?? '',
                    $grade,
                    $class->created_at->format('d/m/Y'),
                    $class->updated_at->format('d/m/Y')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
