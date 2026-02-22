<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'newest');
        $perPage = $request->input('per_page', 10);

        $query = Department::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'code_asc':
                $query->orderBy('code', 'asc');
                break;
            case 'code_desc':
                $query->orderBy('code', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $departments = $query->paginate($perPage)->withQueryString();
        $totalDepartments = Department::count();

        return view('Admins.Departments.index', compact('departments', 'search', 'totalDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama jurusan wajib diisi',
            'code.required' => 'Kode jurusan wajib diisi',
            'code.unique' => 'Kode jurusan sudah digunakan',
        ]);

        Department::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Jurusan ' . $request->name . ' berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $department = Department::with('classes')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'class_count' => $department->class_count,
                'student_count' => $department->student_count,
                'createdAt' => $department->created_at->format('d/m/Y H:i'),
                'updatedAt' => $department->updated_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($id)
            ],
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama jurusan wajib diisi',
            'code.required' => 'Kode jurusan wajib diisi',
            'code.unique' => 'Kode jurusan sudah digunakan',
        ]);

        $department->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Jurusan ' . $request->name . ' berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);

            // Check if department has classes
            if ($department->classes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jurusan tidak dapat dihapus karena masih memiliki kelas terkait'
                ], 400);
            }

            $departmentName = $department->name;
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jurusan ' . $departmentName . ' berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jurusan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick view for department details
     */
    public function detail($id)
    {
        $department = Department::with('classes')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'class_count' => $department->class_count,
                'student_count' => $department->student_count,
                'createdAt' => $department->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Export departments data
     */
    public function export(Request $request)
    {
        $search = $request->input('search', '');

        $query = Department::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        }

        $departments = $query->get();

        $filename = 'data-jurusan-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($departments) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, ['No', 'Nama Jurusan', 'Kode', 'Deskripsi', 'Jumlah Kelas', 'Dibuat', 'Diperbarui']);

            foreach ($departments as $index => $department) {
                fputcsv($file, [
                    $index + 1,
                    $department->name,
                    $department->code,
                    $department->description ?? '',
                    $department->class_count,
                    $department->created_at->format('d/m/Y'),
                    $department->updated_at->format('d/m/Y')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
