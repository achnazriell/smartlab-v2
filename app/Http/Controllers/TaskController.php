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
        $search = $request->input('search');
        $classID = $request->input('class_id');
        $user = auth()->user();

        $tasks = Task::with(['classes', 'materi.subject'])
            ->where('user_id', $user->id)
            ->when($classID, function ($query) use ($classID) {
                $query->whereHas('classes', function ($q) use ($classID) {
                    $q->where('id', $classID);
                });
            })
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('title_task', 'like', '%' . $search . '%')
                        ->orWhere('date_collection', 'like', '%' . $search . '%')
                        ->orWhereHas('classes', function ($q) use ($search) {
                            $q->where('name_class', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('subject', function ($q) use ($search) {
                            $q->where('name_subject', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('materi', function ($q) use ($search) {
                            $q->where('title_materi', 'like', '%' . $search . '%');
                        });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $kelas = $user->classes()->get();

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

        $subjects = Subject::all();

        return view('Guru.Tasks.index', compact(
            'tasks',
            'kelas',
            'subjects',
            'collections',
            'pengumpulans'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini bukan guru');
        }

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $materis = collect();

        return view('Guru.Tasks.create', compact('mapels', 'materis', 'classes'));
    }

    public function store(Request $request)
    {
        // ============================================================
        // VALIDASI MANUAL (tidak pakai StoreTaskRequest agar bisa
        // mengatur pesan & aturan custom lebih fleksibel)
        // ============================================================
        $minDateTime = now()->addMinutes(30)->format('Y-m-d\TH:i');

        $request->validate([
            'title_task'       => 'required|string|max:255',
            'subject_id'       => 'required|exists:subjects,id',
            'materi_id'        => 'nullable|exists:materis,id',   // ✅ OPSIONAL
            'description_task' => 'nullable|string',
            'date_collection'  => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Tanggal pengumpulan minimal 30 menit dari sekarang
                    if (now()->addMinutes(30)->gt(\Carbon\Carbon::parse($value))) {
                        $fail('Tanggal pengumpulan harus minimal 30 menit dari waktu sekarang.');
                    }
                },
            ],
            'class_id'         => 'required|array|min:1',
            'class_id.*'       => 'exists:classes,id',
            'file_task'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'title_task.required'    => 'Judul tugas wajib diisi.',
            'subject_id.required'    => 'Mata pelajaran wajib dipilih.',
            'date_collection.required' => 'Tanggal pengumpulan wajib diisi.',
            'class_id.required'      => 'Pilih minimal satu kelas.',
        ]);

        // ============================================================
        // UPLOAD FILE (jika ada)
        // ============================================================
        $filePath = null;
        if ($request->hasFile('file_task')) {
            $filePath = $request->file('file_task')->store('file_task', 'public');
        }

        // ============================================================
        // BUAT TASK
        // ============================================================
        $task = Task::create([
            'title_task'       => $request->title_task,
            'subject_id'       => $request->subject_id,
            'materi_id'        => $request->materi_id ?: null,  // ✅ null jika kosong
            'description_task' => $request->description_task ?? null,
            'date_collection'  => $request->date_collection,
            'file_task'        => $filePath,                    // ✅ simpan file
            'user_id'          => auth()->id(),
        ]);

        // Simpan relasi kelas
        $task->classes()->sync($request->class_id);

        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            return back()->with('error', 'Tahun ajaran aktif belum ditentukan.');
        }

        // Buat collection untuk setiap siswa di kelas yang dipilih
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) abort(403);

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $materis = Materi::where('subject_id', $task->subject_id)->get();

        return view('Guru.Tasks.edit', compact('task', 'mapels', 'materis', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $minDateTime = now()->addMinutes(30)->format('Y-m-d\TH:i');

        $request->validate([
            'title_task'       => 'required|string|max:255',
            'subject_id'       => 'nullable|exists:subjects,id',
            'materi_id'        => 'nullable|exists:materis,id',   // ✅ OPSIONAL
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
            'class_id'         => 'required|array|min:1',
            'class_id.*'       => 'exists:classes,id',
            'file_task'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
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

    /**
     * Remove the specified resource from storage.
     */
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
}
