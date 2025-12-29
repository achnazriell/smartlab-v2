<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\TeacherClass;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::where('teacher_id', auth()->id())
            ->latest()
            ->get();

        $total = $exams->count();
        $active = $exams->where('status', 'active')->count();
        $draft = $exams->where('status', 'draft')->count();

        return view('Guru.Exam.index', compact(
            'exams',
            'total',
            'active',
            'draft'
        ));
    }

    public function create()
    {
        $teacherId = auth()->id();

        // Ambil kelas guru (sama seperti dashboard)
        $teacherClasses = TeacherClass::with([
            'classes.studentList.user',
        ])->get();

        // Ambil mapel guru
        $teacherSubjects = TeacherSubject::with('subject')
            ->where('teacher_id', $teacherId)
            ->get();

        // Rapikan data untuk blade
        $classes = $teacherClasses
            ->pluck('classes')
            ->filter(); // buang null

        $subjects = $teacherSubjects
            ->pluck('subject')
            ->filter();

        return view('Guru.Exam.create', compact('classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'class_id' => 'required',
            'type' => 'required',
            'duration' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        Exam::create([
            'teacher_id' => auth()->id(),
            'class_id' => $request->class_id,
            'title' => $request->title,
            'type' => $request->type,
            'duration' => $request->duration,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'shuffle_question' => $request->boolean('shuffle_question'),
            'shuffle_answer' => false,
            'show_score' => $request->boolean('show_score'),
            'allow_copy' => $request->boolean('allow_copy'),
            'status' => 'draft',
        ]);

        abort_if(
            ! auth()->user()->subjects->contains($request->subject_id),
            403,
            'Mapel tidak valid'
        );

        abort_if(
            ! auth()->user()->classes->contains($request->class_id),
            403,
            'Kelas tidak valid'
        );

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil dibuat');
    }

    public function edit(Exam $exam)
    {
        return view('exam.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $exam->update($request->all());

        return redirect()->route('exams.index')
            ->with('success', 'Ujian berhasil diperbarui');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Ujian dihapus');
    }
}
