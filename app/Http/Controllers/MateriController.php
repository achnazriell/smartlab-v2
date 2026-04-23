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
use App\Models\AcademicYear;
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
        $search  = $request->input('search');
        $order   = $request->input('order', 'desc');
        $kelasId = $request->input('kelas');   // ✅ filter kelas (id)
        $mapelId = $request->input('mapel');   // ✅ filter mapel (id)
        $tipe    = $request->input('tipe');    // ✅ filter tipe file
        $sort    = $request->input('sort');    // ✅ sort
        $user    = auth()->user();

        $materisQuery = Materi::with('subject', 'classes')
            ->where('user_id', $user->id)
            ->when($kelasId, fn($q) => $q->whereHas('classes', fn($cq) => $cq->where('classes.id', $kelasId)))
            ->when($mapelId, fn($q) => $q->where('subject_id', $mapelId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title_materi', 'like', '%' . $search . '%')
                        ->orWhereHas('classes', fn($cq) => $cq->where('name_class', 'like', '%' . $search . '%'));
                });
            })
            // ✅ Filter tipe file
            ->when($tipe, function ($q) use ($tipe) {
                if ($tipe === 'pdf') {
                    $q->where('file_materi', 'like', '%.pdf');
                } elseif ($tipe === 'video') {
                    $q->where(function ($sq) {
                        $sq->where('file_materi', 'like', '%.mp4')
                           ->orWhere('file_materi', 'like', '%.webm')
                           ->orWhere('file_materi', 'like', '%.mov');
                    });
                } elseif ($tipe === 'link') {
                    $q->whereNotNull('link_materi')->where('link_materi', '!=', '');
                } elseif ($tipe === 'doc') {
                    $q->where(function ($sq) {
                        $sq->where('file_materi', 'like', '%.doc')
                           ->orWhere('file_materi', 'like', '%.docx')
                           ->orWhere('file_materi', 'like', '%.ppt')
                           ->orWhere('file_materi', 'like', '%.pptx');
                    });
                }
            });

        // ✅ Sort
        switch ($sort) {
            case 'terlama':
                $materisQuery->orderBy('created_at', 'asc');
                break;
            case 'judul_asc':
                $materisQuery->orderBy('title_materi', 'asc');
                break;
            default: // terbaru
                $materisQuery->orderBy('created_at', 'desc');
                break;
        }

        $materis = $materisQuery->paginate(5)->withQueryString();

        // ✅ kelasList — dibutuhkan blade untuk dropdown filter (id + name_class)
        $kelasList = $user->classes()->select('id', 'name_class')->get();

        // ✅ mapelList — dibutuhkan blade untuk dropdown filter (id + name)
        $mapelList = Subject::whereHas('materis', fn($q) => $q->where('user_id', $user->id))
            ->select('id', 'name_subject as name')
            ->get();

        // $classes tetap ada untuk keperluan lain
        $classes = $kelasList;

        return view('Guru.Materi.index', compact('materis', 'classes', 'kelasList', 'mapelList'));
    }

    public function show(Materi $materi)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Bukan akun guru');
        }

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

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels  = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();

        return view('Guru.Materi.create', compact('mapels', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_materi' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'subject_id'   => 'required|exists:subjects,id',
            'class_id'     => 'required|array',
            'class_id.*'   => 'exists:classes,id',
            'file_materi'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user    = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'Akun ini bukan guru');
        }

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $filePath = $request->file('file_materi')->store('file_materi', 'public');
        }

        $materi = Materi::create([
            'title_materi' => $request->title_materi,
            'description'  => $request->description,
            'subject_id'   => $request->subject_id,
            'user_id'      => $user->id,
            'file_materi'  => $filePath,
            'slug'         => Str::slug($request->title_materi) . '-' . uniqid(),
        ]);

        $materi->classes()->sync($request->class_id);

        return redirect()->route('materis.index')->with('success', 'Materi berhasil ditambahkan');
    }

    public function getClassesBySubject($subjectId)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return response()->json(['classes' => []]);
        }

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $classes = $teacher->classesTaughtInAcademicYear($yearId)
            ->wherePivot('subject_id', $subjectId)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name_class]);

        return response()->json(['classes' => $classes]);
    }

    public function edit(Materi $materi)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) abort(403);

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $mapels  = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();

        return view('Guru.Materi.edit', compact('materi', 'mapels', 'classes'));
    }

    public function update(UpdateMateriRequest $request, Materi $materi)
    {
        $validated = $request->validated();

        if ($request->hasFile('file_materi')) {
            if ($materi->file_materi) {
                Storage::disk('public')->delete($materi->file_materi);
            }
            $file = $request->file('file_materi')->store('file_materi', 'public');
            $validated['file_materi'] = $file;
        }

        $updateData = [
            'title_materi' => $validated['title_materi'],
            'description'  => $validated['description'],
            'user_id'      => auth()->id(),
        ];

        if (isset($validated['file_materi'])) {
            $updateData['file_materi'] = $validated['file_materi'];
        }

        $materi->update($updateData);
        $materi->classes()->sync($request->class_id);

        return redirect()->route('materis.index')->with('success', 'Data Berhasil Diubah');
    }

    public function destroy(Materi $materi)
    {
        try {
            $fileName = $materi->file_materi;
            $materi->delete();
            if ($fileName) Storage::disk('public')->delete($fileName);
            return redirect()->route('materis.index')->with('success', 'Data Berhasil Dihapus');
        } catch (\Exception $e) {
            return redirect()->route('materis.index')->withErrors('Data gagal dihapus Karena Masih Digunakan');
        }
    }
}