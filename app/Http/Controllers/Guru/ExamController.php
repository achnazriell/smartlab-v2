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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    /**
     * Dapatkan ID teacher dari user yang login
     */
    private function teacherId()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru');
        return $teacher->id;
    }

    /**
     * GET /guru/exams
     * Tampilkan daftar semua ujian yang dibuat oleh guru
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->back()
                ->with('error', 'Anda harus login sebagai guru untuk mengakses halaman ini.');
        }

        // Query exams berdasarkan teacher
        $query = Exam::where('teacher_id', $this->teacherId())
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

        // Filter jenis
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $exams = $query->paginate(10);

        // Statistik
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

    /**
     * GET /guru/exams/create
     * Tampilkan form untuk membuat ujian baru
     */
    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Anda harus login sebagai guru');
        }

        // Dapatkan semua mata pelajaran yang diajar oleh guru ini
        $teacherId = $teacher->id;

        // Ambil ID kelas yang diajar guru
        $teacherClassIds = TeacherClass::where('teacher_id', $teacherId)->pluck('id');

        // Ambil ID subjects dari kelas-kelas tersebut
        $subjectIds = TeacherClassSubject::whereIn('teacher_class_id', $teacherClassIds)
            ->pluck('subject_id')
            ->unique();

        // Ambil data subjects
        $mapels = Subject::whereIn('id', $subjectIds)->get();

        // Ambil kelas yang diajar
        $classes = $teacher->classes;

        return view('Guru.Exam.create', compact('mapels', 'classes'));
    }

    /**
     * POST /guru/exams
     * Simpan ujian baru ke database
     */
    public function store(Request $request)
    {
        // VALIDASI INPUT
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,QUIZ,LAINNYA',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'duration' => 'required|integer|min:1|max:300',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'limit_attempts' => 'nullable|integer|min:1|max:10',
            'violation_limit' => 'nullable|integer|min:1|max:50',
            'min_pass_grade' => 'nullable|numeric|min:0|max:100',

            // Validasi untuk semua field yang mungkin
            'custom_type' => 'nullable|string|max:100',
            'time_per_question' => 'nullable|integer|min:5|max:300',
            'quiz_mode' => 'nullable|in:live,homework',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'show_result_after' => 'nullable|in:never,immediately,after_submit,after_exam',
        ]);

        if ($request->type === 'LAINNYA') {
            $request->validate([
                'custom_type' => 'required|string|max:100',
            ]);
        }

        // Validasi waktu jika keduanya diisi
        if ($request->start_date && $request->end_date) {
            $request->validate([
                'end_date' => 'after:start_date',
            ], [
                'end_date.after' => 'Waktu selesai harus setelah waktu mulai.'
            ]);
        }

        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru');

        DB::beginTransaction();
        try {
            // LOG DATA YANG DITERIMA
            \Log::info('Exam Store Request Data:', $request->all());

            // Tentukan tipe ujian
            $type = $request->type;
            $customType = null;

            if ($type === 'LAINNYA' && $request->has('custom_type')) {
                $customType = $request->custom_type;
            }

            // Tentukan show_result_after
            $showResultAfter = $request->input('show_result_after', 'never');

            // DATA UNTUK QUIZ
            $quizData = [];
            if ($type === 'QUIZ') {
                $quizData = [
                    'time_per_question' => $request->input('time_per_question', 60),
                    'quiz_mode' => $request->input('quiz_mode', 'live'),
                    'difficulty_level' => $request->input('difficulty_level', 'medium'),
                    'show_leaderboard' => $request->has('show_leaderboard') ? 1 : 0,
                    'enable_music' => $request->has('enable_music') ? 1 : 0,
                    'enable_memes' => $request->has('enable_memes') ? 1 : 0,
                    'enable_powerups' => $request->has('enable_powerups') ? 1 : 0,
                    'randomize_questions' => $request->has('randomize_questions') ? 1 : 0,
                    'instant_feedback' => $request->has('instant_feedback') ? 1 : 0,
                    'streak_bonus' => $request->has('streak_bonus') ? 1 : 0,
                    'time_bonus' => $request->has('time_bonus') ? 1 : 0,
                ];
            }

            // Tentukan nilai security berdasarkan dropdown
            $securityLevel = $request->input('security_level', 'none');

            // Setel nilai checkbox berdasarkan security level
            $fullscreenMode = false;
            $blockNewTab = false;
            $preventCopyPaste = false;

            // PERBAIKAN: Jika disable_violations dicentang, matikan semua pengaturan keamanan
            $disableViolations = $request->has('disable_violations');

            if ($disableViolations) {
                // Jika disable_violations dicentang, matikan semua
                $fullscreenMode = false;
                $blockNewTab = false;
                $preventCopyPaste = false;
            } else {
                // Jika tidak, gunakan logic security level biasa
                if ($securityLevel === 'basic') {
                    $fullscreenMode = true;
                    $blockNewTab = true;
                    $preventCopyPaste = false;
                } elseif ($securityLevel === 'strict') {
                    $fullscreenMode = true;
                    $blockNewTab = true;
                    $preventCopyPaste = true;
                } elseif ($securityLevel === 'custom') {
                    // Gunakan nilai dari checkbox jika custom
                    $fullscreenMode = $request->has('fullscreen_mode');
                    $blockNewTab = $request->has('block_new_tab');
                    $preventCopyPaste = $request->has('prevent_copy_paste');
                }
            }

            // Konversi nilai boolean ke integer untuk database
            $fullscreenMode = $fullscreenMode ? 1 : 0;
            $blockNewTab = $blockNewTab ? 1 : 0;
            $preventCopyPaste = $preventCopyPaste ? 1 : 0;
            $disableViolations = $disableViolations ? 1 : 0;

            // Atur waktu
            $startAt = $request->start_date ?: null;
            $endAt = $request->end_date ?: null;

            // BUAT EXAM
            $examData = [
                'teacher_id' => $teacher->id,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'title' => $request->title,
                'type' => $type,
                'custom_type' => $customType,
                'duration' => $request->duration,
                'start_at' => $startAt,
                'end_at' => $endAt,

                // PENGATURAN DASAR
                'shuffle_question' => $request->has('shuffle_question') ? 1 : 0,
                'shuffle_answer' => $request->has('shuffle_answer') ? 1 : 0,
                'show_score' => $request->has('show_score') ? 1 : 0,

                // PENGATURAN KEAMANAN
                'fullscreen_mode' => $fullscreenMode,
                'block_new_tab' => $blockNewTab,
                'prevent_copy_paste' => $preventCopyPaste,
                'disable_violations' => $disableViolations,
                'violation_limit' => $request->input('violation_limit', 3),

                // PROCTORING
                'enable_proctoring' => $request->has('enable_proctoring') ? 1 : 0,
                'require_camera' => $request->has('require_camera') ? 1 : 0,
                'require_mic' => $request->has('require_mic') ? 1 : 0,

                // HASIL
                'show_correct_answer' => $request->has('show_correct_answer') ? 1 : 0,
                'show_result_after' => $showResultAfter,
                'limit_attempts' => $request->input('limit_attempts', 1),
                'min_pass_grade' => $request->input('min_pass_grade', 0),

                // STATUS AWAL
                'status' => 'draft',
            ];

            // Simpan security_level ke database jika ada di migration
            if (Schema::hasColumn('exams', 'security_level')) {
                $examData['security_level'] = $securityLevel;
            }

            // MERGE QUIZ DATA jika tipe QUIZ
            if ($type === 'QUIZ') {
                $examData = array_merge($examData, $quizData);
            }

            \Log::info('Exam Data to Save:', $examData);

            $exam = Exam::create($examData);

            DB::commit();

            \Log::info('Exam created successfully with ID: ' . $exam->id);

            // Redirect ke halaman tambah soal
            return redirect()
                ->route('guru.exams.soal', $exam->id)
                ->with('success', 'Ujian berhasil dibuat! Sekarang tambahkan soal-soal.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating exam: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /guru/exams/{id}/soal
     * Tampilkan halaman untuk mengelola soal ujian
     */
    public function soal($id)
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru.');

        $exam = Exam::where('teacher_id', $teacher->id)
            ->with(['subject', 'class', 'questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->findOrFail($id);

        return view('Guru.Exam.soal', compact('exam'));
    }

    /**
     * POST /guru/exams/{id}/soal
     * Simpan soal baru (AJAX)
     */
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

            // Hitung order terakhir
            $lastOrder = ExamQuestion::where('exam_id', $exam->id)->max('order') ?? 0;

            $questionData = [
                'exam_id' => $exam->id,
                'type' => $request->type,
                'question' => $request->question,
                'score' => $request->score,
                'order' => $lastOrder + 1,
                'enable_skip' => $request->boolean('enable_skip', true),
                'enable_mark_review' => $request->boolean('enable_mark_review', true),
                'show_explanation' => $request->boolean('show_explanation', false),
                'randomize_choices' => $request->boolean('randomize_choices', false),
            ];

            // Untuk soal ISIAN SINGKAT
            if ($request->type === 'IS') {
                // Pisahkan jawaban dengan koma
                $answers = array_map('trim', explode(',', $request->short_answer));
                $questionData['short_answers'] = json_encode($answers);
            }

            $question = ExamQuestion::create($questionData);

            // Untuk soal PILIHAN GANDA
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

    /**
     * POST /guru/exams/{id}/finalize
     * Finalize exam (ubah dari draft ke active)
     */
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

            // Update status menjadi active
            $exam->update([
                'status' => 'active',
                'updated_at' => now()
            ]);

            // Jika QUIZ, hitung total waktu berdasarkan jumlah soal
            if ($exam->type === 'QUIZ' && $exam->time_per_question > 0) {
                $totalQuestions = $exam->questions->count();
                $totalTime = $totalQuestions * $exam->time_per_question;

                $exam->update([
                    'duration' => max(1, ceil($totalTime / 60)) // Konversi ke menit
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dipublikasikan!',
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

    /**
     * GET /guru/exams/get-classes-by-subject/{subjectId}
     * AJAX endpoint untuk mendapatkan kelas berdasarkan subject
     */
    public function getClassesBySubject($subjectId)
    {
        $teacher = auth()->user()->teacher;

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

        return response()->json([
            'success' => true,
            'classes' => $classes
        ]);
    }

    /**
     * GET /guru/exams/{id}
     * Tampilkan detail ujian
     */
    public function show($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->with(['subject', 'class', 'questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->findOrFail($id);

        $totalQuestions = $exam->questions->count();
        $totalScore = $exam->questions->sum('score');

        return view('Guru.Exam.show', compact('exam', 'totalQuestions', 'totalScore'));
    }

    /**
     * DELETE /guru/exams/{id}
     * Hapus ujian
     */
    public function destroy($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        try {
            // Hanya bisa menghapus jika masih draft
            if (!$exam->is_draft) {
                return redirect()->back()
                    ->with('error', 'Hanya ujian dalam status draft yang bisa dihapus');
            }

            $exam->delete();

            return redirect()->route('guru.exams.index')
                ->with('success', 'Ujian berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * PUT /guru/exams/{id}
     * Update ujian
     */
    public function edit($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        // PERBAIKAN: Cek apakah exam masih draft
        if ($exam->status !== 'draft') {
            return redirect()->route('guru.exams.show', $exam->id)
                ->with('error', 'Hanya ujian dalam status draft yang bisa diedit');
        }

        $teacher = auth()->user()->teacher;

        // Ambil semua subjects yang diajar guru
        $teacherId = $teacher->id;
        $teacherClassIds = TeacherClass::where('teacher_id', $teacherId)->pluck('id');
        $subjectIds = TeacherClassSubject::whereIn('teacher_class_id', $teacherClassIds)
            ->pluck('subject_id')
            ->unique();
        $mapels = Subject::whereIn('id', $subjectIds)->get();

        // Ambil semua kelas yang diajar guru
        $classes = $teacher->classes;

        return view('Guru.Exam.edit', compact('exam', 'mapels', 'classes'));
    }

    /**
     * PUT /guru/exams/{id}
     * Update ujian
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        // PERBAIKAN: Cek status draft
        if ($exam->status !== 'draft') {
            return back()->with('error', 'Hanya ujian dalam status draft yang bisa diedit');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,QUIZ,LAINNYA',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'duration' => 'required|integer|min:1|max:300',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'violation_limit' => 'nullable|integer|min:0|max:50',  // PERUBAHAN: dari max_violations
            'limit_attempts' => 'nullable|integer|min:1|max:10',  // PERUBAHAN: dari max_attempts
            'min_pass_grade' => 'nullable|numeric|min:0|max:100', // PERUBAHAN: dari passing_grade
        ]);

        if ($request->type === 'LAINNYA') {
            $request->validate([
                'custom_type' => 'required|string|max:100',
            ]);
        }

        try {
            DB::beginTransaction();

            $type = $request->type;
            $customType = null;

            if ($type === 'LAINNYA' && $request->has('custom_type')) {
                $customType = $request->custom_type;
            }

            // PERBAIKAN: Atur keamanan jika disable_violations dicentang
            $disableViolations = $request->has('disable_violations');
            $fullscreenMode = false;
            $blockNewTab = false;
            $preventCopyPaste = false;

            if ($disableViolations) {
                // Matikan semua jika disable_violations
                $fullscreenMode = false;
                $blockNewTab = false;
                $preventCopyPaste = false;
            } else {
                // Gunakan nilai dari form
                $fullscreenMode = $request->has('fullscreen_mode');
                $blockNewTab = $request->has('block_new_tab');
                $preventCopyPaste = $request->has('prevent_copy_paste');
            }

            // Update basic exam info
            $updateData = [
                'title' => $request->title,
                'type' => $type,
                'custom_type' => $customType,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'duration' => $request->duration,
                'start_at' => $request->start_date,
                'end_at' => $request->end_date,

                // PENGATURAN DASAR - PERUBAHAN NAMA FIELD
                'shuffle_question' => $request->boolean('shuffle_question'),
                'shuffle_answer' => $request->boolean('shuffle_answer'),
                'show_score' => $request->boolean('show_score'),
                'show_correct_answer' => $request->boolean('show_correct_answer'),

                // KEAMANAN - PERUBAHAN NAMA FIELD
                'fullscreen_mode' => $fullscreenMode,
                'block_new_tab' => $blockNewTab,
                'prevent_copy_paste' => $preventCopyPaste,
                'disable_violations' => $disableViolations,
                'violation_limit' => $request->input('violation_limit', 3),

                // PROCTORING
                'enable_proctoring' => $request->boolean('enable_proctoring'),
                'require_camera' => $request->boolean('require_camera'),
                'require_mic' => $request->boolean('require_mic'),

                // HASIL - PERUBAHAN NAMA FIELD
                'show_result_after' => $request->input('show_result_after', 'never'),
                'limit_attempts' => $request->input('limit_attempts', 1),
                'min_pass_grade' => $request->input('min_pass_grade', 0),
            ];

            // Update Quiz Settings if type is QUIZ
            if ($type === 'QUIZ') {
                $updateData = array_merge($updateData, [
                    'quiz_mode' => $request->input('quiz_mode', 'live'),
                    'difficulty_level' => $request->input('difficulty_level', 'medium'),
                    'show_leaderboard' => $request->boolean('show_leaderboard'),
                    'instant_feedback' => $request->boolean('instant_feedback'),
                    'enable_music' => $request->boolean('enable_music'),
                    'enable_powerups' => $request->boolean('enable_powerups'),
                ]);
            }

            $exam->update($updateData);

            DB::commit();

            return redirect()
                ->route('guru.exams.show', $exam->id)
                ->with('success', 'Ujian berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * GET /guru/exams/{id}/edit
     * Tampilkan form edit ujian
     */


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
