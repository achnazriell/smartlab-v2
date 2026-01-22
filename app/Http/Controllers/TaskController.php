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
use App\Models\Student;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order', 'desc');
        $taskId = $request->input('task_id');
        $user = auth()->user();
        $classID = $request->input('class_id');
        $class = $user->class; // Kelas yang dimiliki oleh user
        $subject = $user->subject;

        // Ambil semua tugas dengan relasi Classes, Subject, Materi
        $tasks = Task::where('user_id', auth()->id())
            ->when($classID, function ($query) use ($classID) {
                $query->whereHas('Classes', function ($q) use ($classID) {
                    $q->where('id', $classID);
                });
            })
            ->where(function ($query) use ($search) {
                $query->whereHas('Classes', function ($q) use ($search) {
                    $q->where('name_class', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('Subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('Materi', function ($q) use ($search) {
                        $q->where('title_materi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('title_task', 'like', '%' . $search . '%')
                    ->orWhere('date_collection', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $collections = Collection::with('user')->where('status', 'Sudah mengumpulkan')
            ->get()
            ->groupBy('task_id');

        $classes = $user->classes()->get();
        $subjects = Subject::all();

        $materis = collect(); // Inisialisasi default
        if ($user && $class && $subject) {
            $materis = Materi::where('user_id', $user->id)
                ->whereHas('classes', function ($query) use ($class) {
                    $query->whereIn('class_id', $class->pluck('id'));
                })
                ->where('subject_id', $subject->id)
                ->get();
        }

        $pengumpulans = Collection::with(['user', 'task'])
            ->when($taskId, function ($query) use ($taskId) {
                $query->where('task_id', $taskId);
            })
            ->whereHas('task', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByRaw("FIELD(status, 'Belum mengumpulkan') DESC")
            ->orderBy('status', 'asc')
            ->get()
            ->groupBy('task_id');


        $kelas = $user->classes ?? collect();

        return view('Guru.Tasks.index', compact('tasks', 'classes', 'subjects', 'materis', 'collections', 'pengumpulans', 'kelas'));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini bukan guru');
        }

        $mapels = $teacher
            ->teacherClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->values();

        $materis = collect();
        $classes = $teacher->teacherClasses->pluck('classes');

        return view('Guru.Tasks.create', compact(
            'mapels',
            'materis',
            'classes'
        ));
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();

        // 1️⃣ BUAT TASK SEKALI SAJA
        $task = Task::create([
            'title_task'       => $validated['title_task'],
            'subject_id'       => $validated['subject_id'],
            'materi_id'        => $validated['materi_id'],
            'description_task' => $validated['description_task'] ?? null,
            'date_collection'  => $validated['date_collection'],
            'user_id'          => auth()->id(),
        ]);

        // 2️⃣ ATTACH SEMUA KELAS SEKALIGUS
        $task->classes()->sync($validated['class_id']);

        // 3️⃣ AMBIL SISWA DARI KELAS YANG DIPILIH
        $students = Student::whereIn('class_id', $validated['class_id'])
            ->with('user')
            ->get();

        // 4️⃣ BUAT COLLECTION UNTUK SETIAP SISWA
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

        if (! $teacher) {
            abort(403, 'Akun ini bukan guru');
        }

        // MAPEL GURU (SAMA SEPERTI CREATE)
        $mapels = $teacher
            ->teacherClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->values();

        // MATERI BERDASARKAN SUBJECT TASK
        $materis = Materi::where('subject_id', $task->subject_id)->get();

        // KELAS YANG SUDAH TERPILIH DI TASK
        $classes = $teacher->teacherClasses
            ->pluck('classes')
            ->flatten()
            ->unique('id')
            ->values();


        return view('Guru.Tasks.edit', compact(
            'task',
            'mapels',
            'materis',
            'classes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {

        $validated = $request->validated();

        if ($request->hasFile('file_task')) {
            if ($task->file_task) {
                Storage::disk('public')->delete($task->file_task);
            }
            $file = $request->file('file_task')->store('file_task', 'public');
            $validated['file_task'] = $file;
        }

        $subject = auth()->user()->Subject;

        $task->update([
            'title_task'       => $validated['title_task'],
            'subject_id'       => $validated['subject_id'] ?? $task->subject_id,
            'materi_id'        => $validated['materi_id'],
            'date_collection'  => $validated['date_collection'],
            'description_task' => $validated['description_task'] ?? null,
            'file_task'        => $validated['file_task'] ?? $task->file_task,
        ]);

        $task->classes()->sync($validated['class_id']);

        return redirect()->route('tasks.index')->with('success', 'Tugas Yang Dipilih Berhasil Diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            DB::beginTransaction();

            // hapus relasi terlebih dahulu
            if ($task->collections()->exists()) {
                $task->collections()->delete();
            }

            if (method_exists($task, 'assessments')) {
                $task->assessments()->delete();
            }

            // hapus file jika ada
            if ($task->file_task) {
                Storage::disk('public')->delete($task->file_task);
            }

            // hapus task
            $task->delete();

            DB::commit();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Tugas dan data terkait berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            // DEBUG (sementara, kalau mau cek error asli)
            // dd($e->getMessage());

            return redirect()
                ->back()
                ->withErrors('Tugas tidak bisa dihapus karena masih memiliki data terkait');
        }
    }
}
