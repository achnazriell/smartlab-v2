<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ExamChoice;
use App\Models\ExamQuestion;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    private function teacherId()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Bukan akun guru');
        return $teacher->id;
    }

    public function index(Request $request)
    {
        // DAPATKAN TEACHER DARI USER YANG LOGIN
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            // Jika user bukan guru
            return redirect()->back()
                ->with('error', 'Anda harus login sebagai guru untuk mengakses halaman ini.');
        }

        // PAKAI TEACHER_ID, BUKAN USER_ID!
        $teacherId = $teacher->id;

        $query = Exam::where('teacher_id', $this->teacherId()) // ← BENAR!
            ->with(['subject', 'class'])
            ->latest();

        // Filter pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($q) use ($search) {
                        $q->where('name_class', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter jenis soal
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $exams = $query->paginate(10);

        $total = Exam::where('teacher_id', $this->teacherId())->count();
        $active = Exam::where('teacher_id', $this->teacherId())->where('status', 'active')->count();
        $draft = Exam::where('teacher_id', $this->teacherId())->where('status', 'draft')->count();

        return view('Guru.Exam.index', compact(
            'exams',
            'total',
            'active',
            'draft'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Bukan akun guru');
        }

        // PAKAI $teacher, bukan langsung auth()->user()
        $mapels = $teacher
            ->teacherClasses()
            ->with('subjects')
            ->get()
            ->pluck('subjects')
            ->flatten()
            ->unique('id')
            ->values();

        $classes = $teacher->teacherClasses->pluck('classes');

        return view('Guru.Exam.create', compact('mapels', 'classes'));
    }

    public function getClassesBySubject($subjectId)
    {
        $teacher = auth()->user()->teacher;

        // SAMA PERSIS seperti route AJAX di Materi
        $classes = $teacher
            ->teacherClasses()
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })
            ->with('classes')
            ->get()
            ->pluck('classes')
            ->unique('id')
            ->values()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name_class
                ];
            });

        return response()->json(['classes' => $classes]);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,QUIZ,Lainnya',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
        ];

        // Validasi khusus untuk ujian formal
        if ($request->type !== 'QUIZ') {
            $rules['duration'] = 'required|integer|min:1|max:300';
            $rules['start_date'] = 'required|date|after_or_equal:now';
            $rules['end_date'] = 'required|date|after:start_date';
        } else {
            // Untuk QUIZ, gunakan waktu per soal
            $rules['time_per_question'] = 'required|integer|min:0|max:300';
            $rules['quiz_mode'] = 'required|in:live,homework';
        }

        $request->validate($rules);

        // Validasi akses guru ke kelas dan mapel (SAMA seperti di MateriController)
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun ini bukan guru.');
        }

        // Cek apakah guru mengajar kelas ini
        $teacherClass = TeacherClass::where('teacher_id', $teacher->id)
            ->where('classes_id', $request->class_id)
            ->first();

        if (!$teacherClass) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Guru tidak mengajar kelas ini.');
        }

        // Cek apakah mapel tersedia di kelas ini (menggunakan TeacherClassSubject)
        $teacherClassSubject = TeacherClassSubject::where('teacher_class_id', $teacherClass->id)
            ->where('subject_id', $request->subject_id)
            ->first();

        if (!$teacherClassSubject) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Mapel tidak tersedia di kelas ini.');
        }

        try {
            DB::beginTransaction();

            // Tentukan jenis soal
            $type = $request->type;
            if ($type === 'Lainnya' && $request->filled('custom_type')) {
                $type = $request->custom_type;
            }

            // Persiapkan data untuk ujian
            $examData = [
                'teacher_id' => $teacher->id,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'title' => $request->title,
                'type' => $type,
                'status' => 'draft',
            ];

            if ($request->type !== 'QUIZ') {
                // Ujian formal (UH, UTS, UAS)
                $examData['duration'] = $request->duration;
                $examData['start_at'] = $request->start_date;
                $examData['end_at'] = $request->end_date;

                // Pengaturan dasar
                $examData['shuffle_question'] = $request->boolean('shuffle_question');
                $examData['shuffle_answer'] = $request->boolean('shuffle_answer');
                $examData['show_score'] = $request->boolean('show_score');
                $examData['allow_copy'] = $request->boolean('allow_copy');
                $examData['allow_screenshot'] = $request->boolean('allow_screenshot');

                // Pengaturan keamanan
                $examData['require_camera'] = $request->boolean('require_camera');
                $examData['require_mic'] = $request->boolean('require_mic');
                $examData['enable_proctoring'] = $request->boolean('enable_proctoring');
                $examData['block_new_tab'] = $request->boolean('block_new_tab');
                $examData['fullscreen_mode'] = $request->boolean('fullscreen_mode');
                $examData['auto_submit'] = $request->boolean('auto_submit');
                $examData['prevent_copy_paste'] = $request->boolean('prevent_copy_paste');
                $examData['limit_attempts'] = $request->input('limit_attempts', 1);
                $examData['min_pass_grade'] = $request->input('min_pass_grade', 0);
                $examData['show_correct_answer'] = $request->boolean('show_correct_answer');
                $examData['show_result_after'] = $request->input('show_result_after', 'never');
            } else {
                // Quiz game mode
                $examData['time_per_question'] = $request->time_per_question;
                $examData['quiz_mode'] = $request->quiz_mode;
                $examData['show_leaderboard'] = $request->boolean('show_leaderboard');
                $examData['enable_music'] = $request->boolean('enable_music');
                $examData['enable_memes'] = $request->boolean('enable_memes');
                $examData['enable_powerups'] = $request->boolean('enable_powerups');
                $examData['randomize_questions'] = $request->boolean('randomize_questions');
                $examData['instant_feedback'] = $request->boolean('instant_feedback');
                $examData['streak_bonus'] = $request->boolean('streak_bonus');
                $examData['time_bonus'] = $request->boolean('time_bonus');
                $examData['difficulty_level'] = $request->input('difficulty_level', 'medium');
                $examData['duration'] = 0; // Quiz tidak punya durasi global

                // Default settings untuk quiz
                $examData['shuffle_question'] = true;
                $examData['show_score'] = true;
            }

            $exam = Exam::create($examData);

            DB::commit();

            return redirect()->route('guru.exams.soal', ['exam' => $exam->id])
                ->with('success', 'Ujian berhasil dibuat! Sekarang tambahkan soal.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function soal($id)
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru.');

        $exam = Exam::where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'questions.choices'])
            ->findOrFail($id);

        return view('Guru.Exam.soal', compact('exam'));
    }

    // Tambahkan method ini di ExamController.php

    public function storeQuestion(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:PG,IS',
            'score' => 'required|integer|min:1|max:100',
            'options' => 'required_if:type,PG|array|min:2',
            'options.*' => 'required_if:type,PG|string',
            'correct_answer' => 'required_if:type,PG|integer',
            'short_answer' => 'required_if:type,IS|string',
        ]);

        try {
            DB::beginTransaction();

            $questionData = [
                'exam_id' => $exam->id,
                'type' => $request->type,
                'question' => $request->question,
                'score' => $request->score,
            ];

            if ($request->type === 'IS') {
                // Simpan multiple jawaban untuk isian singkat (pisahkan dengan koma)
                $answers = array_map('trim', explode(',', $request->short_answer));
                $questionData['short_answers'] = json_encode($answers);
            }

            $question = ExamQuestion::create($questionData);

            if ($request->type === 'PG') {
                foreach ($request->options as $index => $option) {
                    ExamChoice::create([
                        'question_id' => $question->id,
                        'label' => chr(65 + $index), // A, B, C, D
                        'text' => $option,
                        'is_correct' => $index == $request->correct_answer,
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil ditambahkan',
                'question' => $question->load('choices')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateQuestion(Request $request, $id, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->findOrFail($questionId);

        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:PG,IS',
            'score' => 'required|integer|min:1|max:100',
            'options' => 'required_if:type,PG|array|min:2',
            'options.*' => 'required_if:type,PG|string',
            'correct_answer' => 'required_if:type,PG|integer',
            'short_answer' => 'required_if:type,IS|string',
        ]);

        try {
            DB::beginTransaction();

            $question->update([
                'question' => $request->question,
                'type' => $request->type,
                'score' => $request->score,
            ]);

            if ($request->type === 'IS') {
                $answers = array_map('trim', explode(',', $request->short_answer));
                $question->short_answers = json_encode($answers);
                $question->save();

                // Hapus pilihan jika ada
                $question->choices()->delete();
            } else {
                // Hapus pilihan lama
                $question->choices()->delete();

                // Buat pilihan baru
                foreach ($request->options as $index => $option) {
                    ExamChoice::create([
                        'question_id' => $question->id,
                        'label' => chr(65 + $index),
                        'text' => $option,
                        'is_correct' => $index == $request->correct_answer,
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil diperbarui',
                'question' => $question->load('choices')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteQuestion($id, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->findOrFail($questionId);

        try {
            DB::beginTransaction();

            // Hapus pilihan terlebih dahulu
            $question->choices()->delete();

            // Hapus soal
            $question->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getQuestion($id, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->with('choices')
            ->findOrFail($questionId);

        return response()->json([
            'success' => true,
            'question' => $question
        ]);
    }

    public function edit($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $teacherId = Auth::id();

        // Ambil data kelas dan mapel untuk dropdown
        $teacherClasses = TeacherClass::where('teacher_id', $teacherId)
            ->with('classes')
            ->get();

        $teacherSubjects = TeacherSubject::where('teacher_id', $teacherId)
            ->with('subject')
            ->get();

        $classes = $teacherClasses->map(function ($teacherClass) {
            return $teacherClass->classes;
        })->filter();

        $subjects = $teacherSubjects->map(function ($teacherSubject) {
            return $teacherSubject->subject;
        })->filter();

        return view('Guru.Exam.edit', compact('exam', 'classes', 'subjects'));
    }

    public function update(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,QUIZ,Lainnya',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
        ];

        if ($exam->type !== 'QUIZ') {
            $rules['duration'] = 'required|integer|min:1|max:300';
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after:start_date';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $type = $request->type;
            if ($type === 'Lainnya' && $request->filled('custom_type')) {
                $type = $request->custom_type;
            }

            $examData = [
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'title' => $request->title,
                'type' => $type,
            ];

            if ($exam->type !== 'QUIZ') {
                $examData['duration'] = $request->duration;
                $examData['start_at'] = $request->start_date;
                $examData['end_at'] = $request->end_date;

                // Update pengaturan keamanan
                $examData['shuffle_question'] = $request->boolean('shuffle_question');
                $examData['shuffle_answer'] = $request->boolean('shuffle_answer');
                $examData['show_score'] = $request->boolean('show_score');
                $examData['allow_copy'] = $request->boolean('allow_copy');
                $examData['allow_screenshot'] = $request->boolean('allow_screenshot');
                $examData['require_camera'] = $request->boolean('require_camera');
                $examData['require_mic'] = $request->boolean('require_mic');
                $examData['enable_proctoring'] = $request->boolean('enable_proctoring');
                $examData['block_new_tab'] = $request->boolean('block_new_tab');
                $examData['fullscreen_mode'] = $request->boolean('fullscreen_mode');
                $examData['auto_submit'] = $request->boolean('auto_submit');
                $examData['prevent_copy_paste'] = $request->boolean('prevent_copy_paste');
                $examData['limit_attempts'] = $request->input('limit_attempts', 1);
                $examData['min_pass_grade'] = $request->input('min_pass_grade', 0);
                $examData['show_correct_answer'] = $request->boolean('show_correct_answer');
                $examData['show_result_after'] = $request->input('show_result_after', 'never');
            } else {
                $examData['time_per_question'] = $request->time_per_question;
                $examData['quiz_mode'] = $request->quiz_mode;
                $examData['show_leaderboard'] = $request->boolean('show_leaderboard');
                $examData['enable_music'] = $request->boolean('enable_music');
                $examData['enable_memes'] = $request->boolean('enable_memes');
                $examData['enable_powerups'] = $request->boolean('enable_powerups');
                $examData['randomize_questions'] = $request->boolean('randomize_questions');
                $examData['instant_feedback'] = $request->boolean('instant_feedback');
                $examData['streak_bonus'] = $request->boolean('streak_bonus');
                $examData['time_bonus'] = $request->boolean('time_bonus');
                $examData['difficulty_level'] = $request->input('difficulty_level', 'medium');
            }

            $exam->update($examData);

            DB::commit();

            return redirect()->route('exams.index')
                ->with('success', 'Ujian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        try {
            $exam->delete();

            return redirect()->route('guru.exams.index')
                ->with('success', 'Ujian berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with(['subject', 'class', 'questions' => function ($query) {
                $query->with('choices');
            }])
            ->findOrFail($id);

        // Hitung total soal dan total skor
        $totalQuestions = $exam->questions->count();
        $totalScore = $exam->questions->sum('score');

        return view('Guru.Exam.show', compact('exam', 'totalQuestions', 'totalScore'));
    }

    // PERBAIKI METHOD exportResults() - ada typo parameter
    public function exportResults($id) // bukan $examId
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with(['attempts' => function ($query) {
                $query->with('student')
                    ->where('status', 'submitted')
                    ->orderBy('final_score', 'desc');
            }])
            ->findOrFail($id); // PERBAIKI: $id bukan $examId

        // Export logic here - contoh sederhana
        return response()->json([
            'success' => true,
            'exam' => $exam->title,
            'attempt_count' => $exam->attempts->count()
        ]);

        // Atau implementasi export ke Excel/PDF sesuai kebutuhan
    }

    // TAMBAHKAN METHOD untuk mendapatkan data exam dalam bentuk JSON (untuk AJAX)
    public function getExamData($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with(['subject', 'class', 'questions.choices'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'exam' => $exam,
            'security_settings' => $exam->getSecuritySettings(),
            'quiz_settings' => $exam->getQuizSettings(),
        ]);
    }

    // TAMBAHKAN METHOD untuk update status langsung
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,active,inactive,finished'
        ]);

        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        try {
            $exam->update(['status' => $request->status]);

            return redirect()->route('guru.exams.show', $exam->id)
                ->with('success', 'Status ujian berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // TAMBAHKAN METHOD untuk statistics
    public function statistics($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with(['attempts' => function ($query) {
                $query->where('status', 'submitted');
            }])
            ->findOrFail($id);

        $totalAttempts = $exam->attempts->count();
        $averageScore = $exam->attempts->avg('final_score');
        $highestScore = $exam->attempts->max('final_score');
        $lowestScore = $exam->attempts->min('final_score');
        $passCount = $exam->attempts->where('final_score', '>=', $exam->min_pass_grade)->count();
        $failCount = $totalAttempts - $passCount;

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_attempts' => $totalAttempts,
                'average_score' => round($averageScore, 2),
                'highest_score' => round($highestScore, 2),
                'lowest_score' => round($lowestScore, 2),
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'pass_percentage' => $totalAttempts > 0 ? round(($passCount / $totalAttempts) * 100, 2) : 0,
                'min_pass_grade' => $exam->min_pass_grade
            ]
        ]);
    }

    // ExamController.php - tambahkan sebelum method terakhir

    public function finalize($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with('questions')
            ->findOrFail($id);

        // Validasi minimal 1 soal
        if ($exam->questions->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian harus memiliki minimal 1 soal'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update status exam dari draft ke active
            $exam->update([
                'status' => 'active',
                'updated_at' => now()
            ]);

            // Jika QUIZ, set waktu total berdasarkan jumlah soal
            if ($exam->type === 'QUIZ' && $exam->time_per_question > 0) {
                $totalQuestions = $exam->questions->count();
                $totalTime = $totalQuestions * $exam->time_per_question;

                $exam->update([
                    'duration' => $totalTime
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil disimpan!',
                'redirect' => route('guru.exams.show', $exam->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // TAMBAHKAN METHOD untuk menghapus semua attempts
    public function clearAttempts($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete all attempts and related answers
            foreach ($exam->attempts as $attempt) {
                $attempt->answers()->delete();
                $attempt->delete();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Semua percobaan ujian berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
