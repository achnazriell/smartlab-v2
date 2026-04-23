<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Materi;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\AcademicYear;
use App\Models\Student;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $classID = $request->input('class_id');
        $mapelID = $request->input('mapel');       // ✅ tambahan filter mapel
        $status  = $request->input('status');      // ✅ tambahan filter status
        $sort    = $request->input('sort', 'terbaru'); // ✅ tambahan sort
        $user    = auth()->user();

        $tasksQuery = Task::with(['classes', 'subject', 'materi'])
            ->where('user_id', $user->id)
            ->when($classID, fn($q) => $q->whereHas('classes', fn($cq) => $cq->where('id', $classID)))
            ->when($mapelID, fn($q) => $q->where('subject_id', $mapelID)) // ✅ filter mapel
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title_task', 'like', '%' . $search . '%')
                        ->orWhere('date_collection', 'like', '%' . $search . '%')
                        ->orWhereHas('classes', fn($cq) => $cq->where('name_class', 'like', '%' . $search . '%'))
                        ->orWhereHas('subject', fn($sq) => $sq->where('name_subject', 'like', '%' . $search . '%'))
                        ->orWhereHas('materi', fn($mq) => $mq->where('title_materi', 'like', '%' . $search . '%'));
                });
            });

        // ✅ Filter status (aktif/berakhir)
        if ($status === 'aktif') {
            $tasksQuery->where('date_collection', '>=', now());
        } elseif ($status === 'berakhir') {
            $tasksQuery->where('date_collection', '<', now());
        }

        // ✅ Sorting
        switch ($sort) {
            case 'terlama':
                $tasksQuery->orderBy('created_at', 'asc');
                break;
            case 'deadline_asc':
                $tasksQuery->orderBy('date_collection', 'asc');
                break;
            case 'deadline_desc':
                $tasksQuery->orderBy('date_collection', 'desc');
                break;
            case 'judul_asc':
                $tasksQuery->orderBy('title_task', 'asc');
                break;
            default: // terbaru
                $tasksQuery->orderBy('created_at', 'desc');
                break;
        }

        $tasks = $tasksQuery->paginate(5)->withQueryString();

        // ✅ Data untuk dropdown filter di blade
        $kelas     = $user->classes()->get();
        $kelasList = $kelas->pluck('name_class')->unique()->sort()->values(); // array nama kelas
        $mapelList = Subject::whereHas('tasks', fn($q) => $q->where('user_id', $user->id))
            ->select('id', 'name_subject as name')
            ->get(); // ✅ diperlukan oleh blade (id + name)

        $collections = Collection::with('user')
            ->where('status', 'Sudah mengumpulkan')
            ->get()
            ->groupBy('task_id');

        $pengumpulans = Collection::with(['user', 'task'])
            ->whereHas('task', fn($q) => $q->where('user_id', $user->id))
            ->orderByRaw("FIELD(status, 'Belum mengumpulkan') DESC")
            ->orderBy('status', 'asc')
            ->get()
            ->groupBy('task_id');

        $subjects = Subject::all(); // tetap ada untuk keperluan lain

        return view('Guru.Tasks.index', compact(
            'tasks',
            'kelas',
            'kelasList',  // ✅ untuk dropdown nama kelas di blade
            'mapelList',  // ✅ untuk dropdown mapel di blade
            'subjects',
            'collections',
            'pengumpulans'
        ));
    }

    public function create()
    {
        $user    = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini bukan guru');
        }

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $mapels  = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $materis = collect();

        return view('Guru.Tasks.create', compact('mapels', 'materis', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_task'       => 'required|string|max:255',
            'subject_id'       => 'required|exists:subjects,id',
            'materi_id'        => 'nullable|exists:materis,id',
            'description_task' => 'nullable|string',
            'date_collection'  => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (now()->addMinutes(30)->gt(\Carbon\Carbon::parse($value))) {
                        $fail('Tanggal pengumpulan harus minimal 30 menit dari waktu sekarang.');
                    }
                },
            ],
            'class_id'  => 'required|array|min:1',
            'class_id.*' => 'exists:classes,id',
            'file_task' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'title_task.required'      => 'Judul tugas wajib diisi.',
            'subject_id.required'      => 'Mata pelajaran wajib dipilih.',
            'date_collection.required' => 'Tanggal pengumpulan wajib diisi.',
            'class_id.required'        => 'Pilih minimal satu kelas.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_task')) {
            $filePath = $request->file('file_task')->store('file_task', 'public');
        }

        $task = Task::create([
            'title_task'       => $request->title_task,
            'subject_id'       => $request->subject_id,
            'materi_id'        => $request->materi_id ?: null,
            'description_task' => $request->description_task ?? null,
            'date_collection'  => $request->date_collection,
            'file_task'        => $filePath,
            'user_id'          => auth()->id(),
        ]);

        $task->classes()->sync($request->class_id);

        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            return back()->with('error', 'Tahun ajaran aktif belum ditentukan.');
        }

        $students = Student::whereHas('classAssignments', function ($q) use ($request, $activeYear) {
            $q->whereIn('class_id', $request->class_id)
                ->where('academic_year_id', $activeYear->id);
        })->with('user')->get();

        foreach ($students as $student) {
            Collection::firstOrCreate([
                'user_id' => $student->user_id,
                'task_id' => $task->id,
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tugas berhasil ditambahkan');
    }

    public function getMateri($subjectId)
    {
        return Materi::where('subject_id', $subjectId)->get();
    }

    public function getClasses($subjectId)
    {
        return Classes::where('subject_id', $subjectId)->get();
    }

    public function show(Task $task)
    {
        //
    }

    public function edit(Task $task)
    {
        $user    = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) abort(403);

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $mapels  = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $materis = Materi::where('subject_id', $task->subject_id)->get();

        return view('Guru.Tasks.edit', compact('task', 'mapels', 'materis', 'classes'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title_task'       => 'required|string|max:255',
            'subject_id'       => 'nullable|exists:subjects,id',
            'materi_id'        => 'nullable|exists:materis,id',
            'description_task' => 'nullable|string',
            'date_collection'  => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (now()->addMinutes(30)->gt(\Carbon\Carbon::parse($value))) {
                        $fail('Tanggal pengumpulan harus minimal 30 menit dari waktu sekarang.');
                    }
                },
            ],
            'class_id'   => 'required|array|min:1',
            'class_id.*' => 'exists:classes,id',
            'file_task'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $filePath = $task->file_task;
        if ($request->hasFile('file_task')) {
            if ($task->file_task) {
                Storage::disk('public')->delete($task->file_task);
            }
            $filePath = $request->file('file_task')->store('file_task', 'public');
        }

        $task->update([
            'title_task'       => $request->title_task,
            'subject_id'       => $request->subject_id ?? $task->subject_id,
            'materi_id'        => $request->materi_id ?: null,
            'date_collection'  => $request->date_collection,
            'description_task' => $request->description_task ?? null,
            'file_task'        => $filePath,
        ]);

        $task->classes()->sync($request->class_id);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui');
    }

    public function destroy(Task $task)
    {
        try {
            DB::beginTransaction();

            if ($task->collections()->exists()) {
                $task->collections()->delete();
            }

            if (method_exists($task, 'assessments')) {
                $task->assessments()->delete();
            }

            if ($task->file_task) {
                Storage::disk('public')->delete($task->file_task);
            }

            $task->delete();

            DB::commit();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Tugas dan data terkait berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors('Tugas tidak bisa dihapus karena masih memiliki data terkait');
        }
    }

    // ============================================================
    // ✅ SERVE FILE — untuk Railway dan environment tanpa symlink
    // Tambahkan route: Route::get('/file/{path}', [TaskController::class, 'serveFile'])
    //                       ->where('path', '.*')->name('file.serve.task');
    // ============================================================
    public function serveFile($path)
    {
        // Cegah path traversal
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) abort(403);

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        $file     = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }
}
