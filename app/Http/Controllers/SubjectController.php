<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Classes;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SubjectImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $minClasses = $request->input('min_classes');
        $maxClasses = $request->input('max_classes');
        $status = $request->input('status');

        $query = Subject::query();

        // Apply search filter
        if ($search) {
            $query->where('name_subject', 'like', '%' . $search . '%');
        }

        // Apply letter filter
        if ($letter) {
            $query->where('name_subject', 'like', $letter . '%');
        }

        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get subjects first, then manually calculate class counts
        $subjectsQuery = $query->clone();
        $subjects = $subjectsQuery->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Calculate classes count for each subject
        $subjectIds = $subjects->pluck('id')->toArray();
        $classCounts = [];

        if (!empty($subjectIds)) {
            // Check what columns exist in the classes table
            $classCountsQuery = Classes::query();

            // Coba beberapa kemungkinan nama kolom foreign key
            if (Schema::hasColumn('classes', 'id_subject')) {
                $classCounts = Classes::whereIn('id_subject', $subjectIds)
                    ->select('id_subject', DB::raw('count(*) as count'))
                    ->groupBy('id_subject')
                    ->pluck('count', 'id_subject')
                    ->toArray();
            } elseif (Schema::hasColumn('classes', 'subject_id')) {
                $classCounts = Classes::whereIn('subject_id', $subjectIds)
                    ->select('subject_id', DB::raw('count(*) as count'))
                    ->groupBy('subject_id')
                    ->pluck('count', 'subject_id')
                    ->toArray();
            }
        }

        // Add classes_count to each subject
        foreach ($subjects as $subject) {
            $subject->classes_count = $classCounts[$subject->id] ?? 0;
        }

        // Apply sorting after pagination (client-side sorting)
        if ($sort === 'name_asc') {
            $subjects = $subjects->sortBy('name_subject')->values();
        } elseif ($sort === 'name_desc') {
            $subjects = $subjects->sortByDesc('name_subject')->values();
        } elseif ($sort === 'popular') {
            $subjects = $subjects->sortByDesc('classes_count')->values();
        } elseif ($sort === 'oldest') {
            $subjects = $subjects->sortBy('created_at')->values();
        }

        // Calculate statistics
        $totalSubjects = Subject::count();
        $totalClasses = Classes::count();
        $avgClassesPerSubject = $totalSubjects > 0 ? round($totalClasses / $totalSubjects, 1) : 0;
        $newThisMonth = Subject::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Calculate popular subjects (with 5 or more classes)
        $popularSubjects = 0;
        $allSubjects = Subject::all();
        foreach ($allSubjects as $subject) {
            // Get class count for each subject
            $count = 0;
            if (Schema::hasColumn('classes', 'id_subject')) {
                $count = Classes::where('id_subject', $subject->id)->count();
            } elseif (Schema::hasColumn('classes', 'subject_id')) {
                $count = Classes::where('subject_id', $subject->id)->count();
            }

            if ($count >= 5) {
                $popularSubjects++;
            }
        }

        return view('Admins.Subject.index', compact(
            'subjects',
            'order',
            'totalSubjects',
            'totalClasses',
            'avgClassesPerSubject',
            'newThisMonth',
            'popularSubjects'
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
        // Check for duplicate
        $exists = Subject::where('name_subject', $request->name_subject)->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name_subject' => 'Mata pelajaran dengan nama ini sudah ada']);
        }

        Subject::create($request->all());

        return redirect()->route('subject.index')
            ->with('success', 'Mata pelajaran ' . $request->name_subject . ' berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);

        // Get class count
        $classCount = 0;
        if (\Schema::hasColumn('classes', 'id_subject')) {
            $classCount = Classes::where('id_subject', $subject->id)->count();
        } elseif (\Schema::hasColumn('classes', 'subject_id')) {
            $classCount = Classes::where('subject_id', $subject->id)->count();
        }

        return response()->json([
            'id' => $subject->id,
            'name' => $subject->name_subject,
            'description' => $subject->description,
            'classCount' => $classCount,
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
        $subject = Subject::findOrFail($id);

        // Check for duplicate (excluding current subject)
        $exists = Subject::where('name_subject', $request->name_subject)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name_subject' => 'Mata pelajaran dengan nama ini sudah ada']);
        }

        $subject->update($request->all());

        return redirect()->route('subject.index')
            ->with('success', 'Mata pelajaran ' . $request->name_subject . ' berhasil diperbarui');
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

        // Create a simple template if doesn't exist
        return redirect()->back()->with('error', 'Template tidak ditemukan');
    }

    /**
     * Export subjects data
     */
    public function export(Request $request)
    {
        $search = $request->input('search', '');
        $letter = $request->input('letter', '');

        $query = Subject::query();

        if ($search) {
            $query->where('name_subject', 'like', '%' . $search . '%');
        }

        if ($letter) {
            $query->where('name_subject', 'like', $letter . '%');
        }

        $subjects = $query->get();

        // Add class counts to each subject
        foreach ($subjects as $subject) {
            $classCount = 0;
            if (\Schema::hasColumn('classes', 'id_subject')) {
                $classCount = Classes::where('id_subject', $subject->id)->count();
            } elseif (\Schema::hasColumn('classes', 'subject_id')) {
                $classCount = Classes::where('subject_id', $subject->id)->count();
            }
            $subject->classes_count = $classCount;
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subjects-export-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($subjects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Mata Pelajaran', 'Jumlah Kelas', 'Dibuat', 'Diperbarui', 'Status']);

            foreach ($subjects as $index => $subject) {
                fputcsv($file, [
                    $index + 1,
                    $subject->name_subject,
                    $subject->classes_count ?? 0,
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

        // Get class count
        $classCount = 0;
        if (\Schema::hasColumn('classes', 'id_subject')) {
            $classCount = Classes::where('id_subject', $subject->id)->count();
        } elseif (\Schema::hasColumn('classes', 'subject_id')) {
            $classCount = Classes::where('subject_id', $subject->id)->count();
        }

        return response()->json([
            'id' => $subject->id,
            'name' => $subject->name_subject,
            'description' => $subject->description,
            'classCount' => $classCount,
            'createdAt' => $subject->created_at->format('d/m/Y H:i'),
        ]);
    }
}
