<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Assessment;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function byTask(Request $request, Task $task)
    {
        $user = auth()->user();
        $search = $request->search;

        $collections = Collection::with(['user.classes', 'task'])
            ->where('task_id', $task->id) // 🔥 INI KUNCI UTAMA
            ->whereHas('task', function ($q) use ($user) {
                $q->where('user_id', $user->id); // hanya tugas milik guru
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%");
                });
            })
            ->orderByRaw("FIELD(status, 'Sudah mengumpulkan', 'Belum mengumpulkan')")
            ->latest()
            ->paginate(5);

        return view('Guru.Collections.index', compact('collections', 'task'));
    }


    public function updateCollection(Request $request, $task_id)
    {
        $request->validate([
            'file_collection' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'file_collection.required' => 'File tugas wajib diunggah',
        ]);

        $user = auth()->user();
        $user_id = $user->id;

        $task = Task::findOrFail($task_id);

        // Ambil atau buat collection
        $collection = Collection::updateOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $user_id,
            ],
            [
                'status' => 'Belum mengumpulkan',
            ]
        );

        // Upload file
        if ($request->hasFile('file_collection')) {
            $file = $request->file('file_collection');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('collections', $fileName, 'public');

            // Hapus file lama jika ada
            if ($collection->file_collection && Storage::exists('public/' . $collection->file_collection)) {
                Storage::delete('public/' . $collection->file_collection);
            }

            $collection->update([
                'file_collection' => $filePath,
                'status' => 'Sudah mengumpulkan',
            ]);
        }

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan!');
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
    public function store(StoreCollectionRequest $request)
    {
        Collection::create($request->all());

        return redirect()->route('collections.index')->with('Berhasil', 'Data Baru Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        try {
            $collection->delete();

            return redirect()->route('collections.index')->with('Berhasil', 'Data Yang Dipilih Berhasil Dihapus');
        } catch (\Exception $e) {
            return redirect()->route('collections.index')->with('Gagal', 'Data Yang Dipilih Masih Dipakai Tabel Lain');
        }
    }
}
