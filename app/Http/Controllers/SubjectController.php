<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SubjectImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Teacher;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order', 'desc');
        $sort = $request->input('sort', 'newest');
        $perPage = $request->input('per_page', 10);
        $letter = $request->input('letter');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $minTeachers = $request->input('min_teachers');
        $maxTeachers = $request->input('max_teachers');
        $status = $request->input('status');

        // Query utama dengan count teachers - PERBAIKAN DI SINI
        $query = Subject::query()
            ->select('subjects.*')
            ->leftJoin('teacher_subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->groupBy('subjects.id', 'subjects.name_subject', 'subjects.created_at', 'subjects.updated_at')
            ->selectRaw('COUNT(DISTINCT teacher_subjects.teacher_id) as teachers_count');

        // Apply search filter
        if ($search) {
            $query->where('subjects.name_subject', 'like', '%' . $search . '%');
        }

        // Apply letter filter
        if ($letter) {
            $query->where('subjects.name_subject', 'like', $letter . '%');
        }

        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('subjects.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('subjects.created_at', '<=', $dateTo);
        }

        // Apply teacher count filters
        if ($minTeachers) {
            $query->havingRaw('COUNT(DISTINCT teacher_subjects.teacher_id) >= ?', [$minTeachers]);
        }
        if ($maxTeachers) {
            $query->havingRaw('COUNT(DISTINCT teacher_subjects.teacher_id) <= ?', [$maxTeachers]);
        }

        // Apply sorting
        if ($sort === 'name_asc') {
            $query->orderBy('subjects.name_subject', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('subjects.name_subject', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderByDesc('teachers_count');
        } elseif ($sort === 'oldest') {
            $query->orderBy('subjects.created_at', 'asc');
        } else {
            $query->orderBy('subjects.created_at', 'desc');
        }

        // Get paginated results
        $subjects = $query->paginate($perPage);

        // Calculate statistics
        $totalSubjects = Subject::count();
        $totalClasses = Classes::count();

        // Hitung total guru yang mengajar semua mapel
        $totalTeachers = 0;
        if (Schema::hasTable('teacher_subjects')) {
            $totalTeachers = DB::table('teacher_subjects')
                ->distinct('teacher_id')
                ->count('teacher_id');
        }

        $avgTeachersPerSubject = $totalSubjects > 0 ? round($totalTeachers / $totalSubjects, 1) : 0;

        $newThisMonth = Subject::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Hitung mata pelajaran populer (dengan 3 atau lebih guru)
        $popularSubjects = Subject::whereHas('teachers', function ($query) {
            $query->select('teacher_id')
                ->groupBy('teacher_id')
                ->havingRaw('COUNT(DISTINCT teacher_id) >= 3');
        })->count();

        return view('Admins.Subject.index', compact(
            'subjects',
            'order',
            'totalSubjects',
            'totalClasses',
            'avgTeachersPerSubject',
            'newThisMonth',
            'popularSubjects',
            'totalTeachers'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubjectRequest $request)
    {
        try {
            // Check for duplicate
            $exists = Subject::where('name_subject', $request->name_subject)->exists();
            if ($exists) {
                // Jika request AJAX
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'name_subject' => ['Mata pelajaran dengan nama ini sudah ada']
                        ]
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['name_subject' => 'Mata pelajaran dengan nama ini sudah ada']);
            }

            Subject::create($request->all());

            // Jika request AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mata pelajaran ' . $request->name_subject . ' berhasil ditambahkan'
                ]);
            }

            return redirect()->route('subject.index')
                ->with('success', 'Mata pelajaran ' . $request->name_subject . ' berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error('Error creating subject: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menambahkan data'
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan data');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);

        // Hitung jumlah guru
        $teachersCount = DB::table('teacher_subjects')
            ->where('subject_id', $id)
            ->distinct('teacher_id')
            ->count('teacher_id');

        return response()->json([
            'id' => $subject->id,
            'name' => $subject->name_subject,
            'description' => $subject->description ?? '-',
            'teachersCount' => $teachersCount,
            'createdAt' => $subject->created_at->format('d/m/Y H:i'),
            'updatedAt' => $subject->updated_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $subject = Subject::findOrFail($id);

        return response()->json([
            'id' => $subject->id,
            'name_subject' => $subject->name_subject,
            'description' => $subject->description,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubjectRequest $request, $id)
    {
        try {
            $subject = Subject::findOrFail($id);

            // Check for duplicate (excluding current subject)
            $exists = Subject::where('name_subject', $request->name_subject)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'name_subject' => ['Mata pelajaran dengan nama ini sudah ada']
                        ]
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['name_subject' => 'Mata pelajaran dengan nama ini sudah ada']);
            }

            $subject->update($request->all());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mata pelajaran ' . $request->name_subject . ' berhasil diperbarui'
                ]);
            }

            return redirect()->route('subject.index')
                ->with('success', 'Mata pelajaran ' . $request->name_subject . ' berhasil diperbarui');
        } catch (\Exception $e) {
            \Log::error('Error updating subject: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data'
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subjectName = $subject->name_subject;
            $subject->delete();

            return redirect()->route('subject.index')
                ->with('success', 'Mata pelajaran ' . $subjectName . ' berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('subject.index')->withErrors('Data gagal dihapus: ' . $e->getMessage());
        }
    }

    /**
     * Import subjects from Excel file
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
            $import = new SubjectImport();
            Excel::import($import, $request->file('file'));

            $stats = $import->getImportStats();
            $message = "Import selesai!<br>";

            if ($stats['success'] > 0) {
                $message .= "✅ <strong>{$stats['success']}</strong> mapel berhasil diimport.<br>";
            }

            if ($stats['skipped'] > 0) {
                $message .= "⚠️ <strong>{$stats['skipped']}</strong> data dilewati (duplikat).<br>";
            }

            if ($stats['errors'] > 0) {
                $message .= "❌ <strong>{$stats['errors']}</strong> data error.";
                return back()
                    ->with('info', $message)
                    ->with('errors', $import->getErrors());
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_import_mapel.xlsx');

        if (file_exists($path)) {
            return response()->download($path, 'Template_Import_Mapel.xlsx');
        }

        return redirect()->back()->with('error', 'Template tidak ditemukan');
    }

    /**
     * Export subjects data
     */
    public function export(Request $request)
    {
        $search = $request->input('search', '');
        $letter = $request->input('letter', '');

        $query = Subject::query()
            ->select('subjects.*')
            ->leftJoin('teacher_subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->groupBy('subjects.id', 'subjects.name_subject', 'subjects.created_at', 'subjects.updated_at')
            ->selectRaw('COUNT(DISTINCT teacher_subjects.teacher_id) as teachers_count');

        if ($search) {
            $query->where('subjects.name_subject', 'like', '%' . $search . '%');
        }

        if ($letter) {
            $query->where('subjects.name_subject', 'like', $letter . '%');
        }

        $subjects = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subjects-export-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($subjects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Mata Pelajaran', 'Jumlah Guru', 'Dibuat', 'Diperbarui', 'Status']);

            foreach ($subjects as $index => $subject) {
                fputcsv($file, [
                    $index + 1,
                    $subject->name_subject,
                    $subject->teachers_count ?? 0,
                    $subject->created_at->format('d/m/Y H:i'),
                    $subject->updated_at->format('d/m/Y H:i'),
                    'Aktif'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Duplicate a subject
     */
    public function duplicate($id)
    {
        $subject = Subject::findOrFail($id);

        $newSubject = $subject->replicate();
        $newSubject->name_subject = $subject->name_subject . ' (Salinan)';
        $newSubject->created_at = now();
        $newSubject->updated_at = now();
        $newSubject->save();

        return redirect()->route('subject.index')
            ->with('success', 'Mata pelajaran berhasil diduplikasi');
    }

    /**
     * Bulk delete subjects
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih mata pelajaran terlebih dahulu');
        }

        $count = Subject::whereIn('id', $ids)->delete();

        return redirect()->route('subject.index')
            ->with('success', $count . ' mata pelajaran berhasil dihapus');
    }

    /**
     * Get subject details for quick view
     */
    public function detail($id)
    {
        $subject = Subject::findOrFail($id);

        $teachersCount = DB::table('teacher_subjects')
            ->where('subject_id', $id)
            ->distinct('teacher_id')
            ->count('teacher_id');

        return response()->json([
            'id' => $subject->id,
            'name' => $subject->name_subject,
            'description' => $subject->description ?? '-',
            'teachersCount' => $teachersCount,
            'createdAt' => $subject->created_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Get teachers for a specific subject
     */
    public function teachers($id)
    {
        $subject = Subject::findOrFail($id);

        $teachers = DB::table('teacher_subjects')
            ->join('teachers', 'teacher_subjects.teacher_id', '=', 'teachers.id')
            ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
            ->where('teacher_subjects.subject_id', $id)
            ->select(
                'teachers.id',
                'users.name as name',
                'users.email as email',
                'users.phone as phone'
            )
            ->get();

        $teachersCount = $teachers->count();

        return response()->json([
            'subject' => $subject->name_subject,
            'teachersCount' => $teachersCount,
            'teachers' => $teachers
        ]);
    }
}
