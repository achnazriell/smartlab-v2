<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Classes;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreMateriRequest;
use App\Http\Requests\UpdateMateriRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order', 'desc');
        $user = auth()->user();

        // Filter dan Search Materi menggunakan whereHas
        $materis = Materi::with('subject', 'classes')
            ->where('user_id', auth()->id()) // Filter berdasarkan user ID terlebih dahulu
            ->where(function ($query) use ($search) {
                $query->whereHas('classes', function ($q) use ($search) {
                    $q->where('name_class', 'like', '%' . $search . '%');
                })
                    ->orWhere('title_materi', 'like', '%' . $search . '%')
                    ->orWhere('created_at', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', $order) // Urutkan berdasarkan waktu pembuatan
            ->paginate(5); // Pagination

        // Filter Dropdown Kelas
        $classes = $user->classes()->get();
        return view('Guru.Materi.index', compact('materis', 'classes'));
    }

    public function show(Materi $materi)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Bukan akun guru');
        }

        // Optional: pastikan materi milik guru ini
        if ($materi->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengakses materi ini');
        }

        return view('Guru.Materi.show', compact('materi'));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Bukan akun guru');
        }

        // Mapel yang diajar guru
        $mapels = $teacher->teacherClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->values();

        // Kelas yang diajar guru
        $classes = $teacher->teacherClasses
            ->pluck('classes')
            ->flatten()
            ->unique('id')
            ->values();

        return view('Guru.Materi.create', compact('mapels', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_materi' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|array',
            'class_id.*' => 'exists:classes,id',
            'file_materi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'Akun ini bukan guru');
        }

        // Upload file jika ada
        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $filePath = $request->file('file_materi')
                ->store('file_materi', 'public');
        }

        // Simpan materi
        $materi = Materi::create([
            'title_materi' => $request->title_materi,
            'description' => $request->description,
            'subject_id' => $request->subject_id,
            'user_id' => $user->id,
            'file_materi' => $filePath,
            'slug' => Str::slug($request->title_materi) . '-' . uniqid(),
        ]);

        // Relasi kelas (many to many)
        $materi->classes()->sync($request->class_id);

        return redirect()
            ->route('materis.index')
            ->with('success', 'Materi berhasil ditambahkan');
    }

    // TAMBAHKAN METHOD untuk get kelas berdasarkan mapel (AJAX)
    public function getClassesBySubject($subjectId)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['classes' => []]);
        }

        // Ambil kelas yang diajar guru untuk mapel tertentu
        $classes = $teacher->teacherClasses()
            ->whereHas('subjects', function ($query) use ($subjectId) {
                $query->where('subjects.id', $subjectId);
            })
            ->with('classes')
            ->get()
            ->pluck('classes')
            ->flatten()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name_class
                ];
            })
            ->values();

        return response()->json(['classes' => $classes]);
    }

    public function edit(Materi $materi)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Bukan akun guru');
        }

        // Ambil mapel yang diajar guru
        $mapels = $teacher
            ->teacherClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->values();

        // Ambil kelas yang diajar guru
        $classes = $teacher->teacherClasses
            ->pluck('classes')
            ->flatten()
            ->unique('id')
            ->values();

        return view('Guru.Materi.edit', compact(
            'materi',
            'classes',
            'mapels'
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMateriRequest $request, Materi $materi)
    {
        // Validasi input
        $validated = $request->validated();

        // Cek apakah ada file yang diunggah
        if ($request->hasFile('file_materi')) {
            // Hapus file lama jika ada
            if ($materi->file_materi) {
                Storage::disk('public')->delete($materi->file_materi);
            }

            // Simpan file baru dan tambahkan ke array $validated
            $file = $request->file('file_materi')->store('file_materi', 'public');
            $validated['file_materi'] = $file;
        }

        // Update data materi, hanya tambahkan file_materi jika ada
        $updateData = [
            'title_materi' => $validated['title_materi'],
            'description' => $validated['description'],
            'user_id' => auth()->id(),
        ];

        // Tambahkan file_materi jika ada dalam $validated
        if (isset($validated['file_materi'])) {
            $updateData['file_materi'] = $validated['file_materi'];
        }

        // Lakukan update
        $materi->update($updateData);

        $materi->classes()->sync($request->class_id);

        return redirect()->route('materis.index')->with('success', 'Data Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Materi $materi)
    {
        try {
            $fileName = $materi->file_materi;
            $materi->delete();
            Storage::disk('public')->delete($fileName);
            return redirect()->route('materis.index')->with('success', 'Data Berhasil Dihapus');
        } catch (\Exception $e) {
            return redirect()->route('materis.index')->withErrors('Data gagal dihapus Karena Masih Digunakan');
        }
    }
}
