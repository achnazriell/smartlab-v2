<?php

namespace App\Http\Controllers;

use App\Models\Exam;
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
        $draft  = $exams->where('status', 'draft')->count();

        return view('Guru.Exam.index', compact(
            'exams',
            'total',
            'active',
            'draft'
        ));
    }

    public function create()
    {
        return view('exam.create');
    }

    public function store(Request $request)
    {
        Exam::create([
            'teacher_id' => auth()->id(),
            'class_id' => $request->class_id,
            'title' => $request->title,
            'type' => $request->type,
            'duration' => $request->duration,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'shuffle_question' => $request->boolean('shuffle_question'),
            'shuffle_answer' => $request->boolean('shuffle_answer'),
            'show_score' => $request->boolean('show_score'),
            'allow_copy' => $request->boolean('allow_copy'),
            'status' => 'draft',
        ]);

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

        return redirect()->route('guru.ujian.index')
            ->with('success', 'Ujian berhasil diperbarui');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Ujian dihapus');
    }
}
