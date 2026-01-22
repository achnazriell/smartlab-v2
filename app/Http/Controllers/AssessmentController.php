<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Collection;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $task)
    {
        $user = auth()->user();
        $search = $request->input('search');

        // Ubah $task menjadi model
        $task = Task::findOrFail($task);

        $collections = Collection::with([
            'user.classes',
            'assessment'
        ])
            ->where('task_id', $task->id)
            ->where('status', 'Sudah mengumpulkan')
            ->whereHas('task', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'asc')
            ->paginate(5);

        // hitung
        $countSiswa = User::whereHas('roles', fn($q) => $q->where('name', 'Murid'))
            ->whereHas('classes', fn($q) => $q->where('classes.id', $task->class_id))
            ->count();

        $countCollection = $collections->total();

        return view('Guru.Assesments.index', compact(
            'collections',
            'task',
            'countSiswa',
            'countCollection'
        ));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $taskId)
    {
        // Validasi mark_task untuk setiap item dalam array
        $request->validate([
            'mark_task.*' => 'required|min:0|max:100', // Validasi setiap nilai dalam array
        ], [
            'mark_task.*.required' => 'Nilai tidak boleh kosong.',
            'mark_task.*.min' => 'Nilai harus di atas atau sama dengan 0.',
            'mark_task.*.max' => 'Nilai harus di bawah atau sama dengan 100.',
        ]);

        foreach ($request->mark_task as $userId => $collections) {
            foreach ($collections as $collectionId => $mark) {
                // Lakukan update atau create berdasarkan user_id dan collection_id
                Assessment::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'collection_id' => $collectionId,
                    ],
                    [
                        'mark_task' => $mark,
                        'status' => 'Sudah Di-nilai',
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Penilaian berhasil disimpan.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi data
        $validated = $request->validate([
            'mark_task' => 'required|numeric|min:0|max:100',
        ]);

        // Cari penilaian berdasarkan ID dan perbarui
        $assessment = Assessment::findOrFail($id);
        $assessment->update($validated);

        return redirect()->route('assessments.index')->with('success', 'Penilaian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Hapus penilaian berdasarkan ID
            $assessment = Assessment::findOrFail($id);
            $assessment->delete();

            return redirect()->route('assessments.index')->with('success', 'Penilaian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('assessments.index')->with('error', 'Gagal menghapus penilaian. Data masih dibutuhkan.');
        }
    }

    /**
     * Menampilkan siswa yang sudah mengumpulkan tugas.
     */
    public function showCollectionsByTask($taskId)
    {
        $user = auth()->user(); // Ambil pengguna yang sedang login

        // Ambil koleksi tugas berdasarkan ID tugas yang dikumpulkan siswa
        $collections = Collection::where('task_id', $taskId)
            ->where('status', 'Sudah mengumpulkan')
            ->with('user')
            ->get();

        // Tampilkan halaman khusus guru untuk menilai tugas
        if ($user->hasRole('Guru')) {
            return view('Guru.Assesments.Collections', compact('collections'));
        } else {
            abort(403, 'Unauthorized');
        }
    }
}
