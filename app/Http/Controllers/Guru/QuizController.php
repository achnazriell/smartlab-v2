<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ExamAttempt;
use App\Models\QuizParticipant;
use App\Models\QuizSession;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use Illuminate\Support\Facades\Log; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Display a listing of quizzes
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $quizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($request->has('status')) {
            $quizzes->where('status', $request->status);
        }

        // Filter berdasarkan kelas
        if ($request->has('class_id')) {
            $quizzes->where('class_id', $request->class_id);
        }

        // Filter berdasarkan mapel
        if ($request->has('subject_id')) {
            $quizzes->where('subject_id', $request->subject_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $quizzes->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', "%{$search}%");
                    });
            });
        }

        $quizzes = $quizzes->paginate(10);

        $classes = $teacher->classes()->get();
        $subjects = $teacher->subjects()->get();

        return view('guru.quiz.index', compact('quizzes', 'classes', 'subjects'));
    }

    /**
     * Show the form for creating a new quiz
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

        return view('guru.quiz.create', compact('mapels', 'classes'));
    }

    /**
     * Store a newly created quiz
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:QUIZ',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'time_per_question' => 'required|integer|min:5|max:300',
            'quiz_mode' => 'required|in:live,homework',
            'duration' => 'nullable|integer|min:1|max:480', // Durasi dalam menit (maks 8 jam)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teacher = Auth::user()->teacher;

        // Debug: Log data yang diterima
        \Log::info('Storing quiz', [
            'teacher_id' => $teacher->id,
            'request_data' => $request->all()
        ]);

        try {
            DB::beginTransaction();

            // Hitung durasi otomatis jika tidak diisi
            $duration = $request->duration;
            if (!$duration) {
                // Default: 30 menit atau hitung berdasarkan jumlah soal nanti
                $duration = 30;
            }

            // Simpan quiz
            $quiz = Exam::create([
                'teacher_id' => $teacher->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'type' => 'QUIZ',
                'duration' => $duration,
                'start_at' => null, // Quiz tidak punya tanggal mulai
                'end_at' => null, // Quiz tidak punya tanggal selesai
                'difficulty_level' => $request->difficulty_level,
                'time_per_question' => $request->time_per_question,
                'quiz_mode' => $request->quiz_mode,
                'status' => 'draft',

                // Quiz settings
                'shuffle_question' => $request->boolean('shuffle_question'),
                'shuffle_answer' => $request->boolean('shuffle_answer'),
                'show_score' => $request->boolean('show_score'),
                'show_correct_answer' => $request->boolean('show_correct_answer'),
                'fullscreen_mode' => $request->boolean('fullscreen_mode'),
                'block_new_tab' => $request->boolean('block_new_tab'),
                'prevent_copy_paste' => $request->boolean('prevent_copy_paste'),
                'show_result_after' => $request->show_result_after ?? 'immediately',
                'limit_attempts' => $request->limit_attempts ?? 1,
                'min_pass_grade' => $request->min_pass_grade ?? 0,

                // Quiz features
                'show_leaderboard' => $request->boolean('show_leaderboard'),
                'enable_music' => $request->boolean('enable_music'),
                'enable_memes' => $request->boolean('enable_memes'),
                'enable_powerups' => $request->boolean('enable_powerups'),
                'instant_feedback' => $request->boolean('instant_feedback'),
                'streak_bonus' => $request->boolean('streak_bonus'),
                'time_bonus' => $request->boolean('time_bonus'),
                'enable_retake' => $request->boolean('enable_retake'),

                // Room settings
                'is_room_open' => false,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => null,
            ]);

            // Debug: Log exam yang dibuat
            \Log::info('Quiz created', [
                'exam_id' => $quiz->id,
                'title' => $quiz->title,
                'duration' => $quiz->duration
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quiz berhasil dibuat!',
                    'exam_id' => $quiz->id,
                    'redirect' => route('guru.quiz.questions', $quiz->id)
                ]);
            }

            return redirect()->route('guru.quiz.questions', $quiz->id)
                ->with('success', 'Quiz berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Debug: Log error
            \Log::error('Error creating quiz: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat quiz: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal membuat quiz: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show quiz question creator page
     */
    public function showQuestionCreator(Exam $quiz)
    {
        // Cek ownership dengan benar
        $teacher = Auth::user()->teacher;

        // PERBAIKAN: Gunakan $quiz->id bukan $quiz->exam_id
        if ($quiz->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        // Cek tipe harus QUIZ
        if ($quiz->type !== 'QUIZ') {
            // PERBAIKAN: Gunakan route yang benar untuk exam
            return redirect()->route('guru.exams.soal', $quiz->id);
        }

        $questions = $quiz->questions()->with('choices')->orderBy('order')->get();
        $questionCount = $questions->count();
        $totalScore = $questions->sum('score');

        // PERBAIKAN: Handle jika short_answers null
        $questionsData = $questions->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
                'score' => $q->score,
                'explanation' => $q->explanation ?? '',
                'choices' => $q->choices->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'text' => $c->text,
                        'is_correct' => (bool) $c->is_correct,
                    ];
                })->values(),
                'short_answers' => $q->type === 'IS' ?
                    (json_decode($q->short_answers ?? '[]') ?? []) : [],
            ];
        })->values();

        // Get other quizzes for import
        $otherQuizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->where('id', '!=', $quiz->id)
            ->whereHas('questions')
            ->withCount('questions')
            ->get();

        return view('guru.quiz.questions', compact(
            'quiz',
            'questions',
            'questionCount',
            'totalScore',
            'questionsData',
            'otherQuizzes'
        ));
    }

    /**
     * Import preview
     */
    public function importPreview($quizId)
    {
        $sourceExam = Exam::findOrFail($quizId);

        // Cek ownership
        if ($sourceExam->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $questions = $sourceExam->questions()->with('choices')->get();

        $formattedQuestions = $questions->map(function ($question) {
            $data = [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'score' => $question->score,
                'explanation' => $question->explanation,
            ];

            if ($question->type === 'PG' && $question->choices->isNotEmpty()) {
                $data['choices'] = $question->choices->map(function ($choice) {
                    return [
                        'text' => $choice->text,
                        'is_correct' => $choice->is_correct,
                    ];
                })->toArray();
            } elseif ($question->type === 'IS') {
                $data['short_answers'] = json_decode($question->short_answers ?? '[]');
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'questions' => $formattedQuestions,
        ]);
    }

    /**
     * Get questions list (AJAX)
     */
    public function getQuestions(Exam $quiz)
    {
        try {
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get questions with choices, termasuk untuk quiz draft
            $questions = $quiz->questions()
                ->with('choices')
                ->orderBy('order')
                ->get();

            Log::info('Getting questions for quiz', [
                'quiz_id' => $quiz->id,
                'status' => $quiz->status,
                'count' => $questions->count()
            ]);

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'total_questions' => $questions->count(),
                'total_score' => $questions->sum('score')
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store questions (multiple) - AJAX
     */
    public function storeQuestions(Request $request, Exam $quiz)
    {
        try {
            // Validasi kepemilikan
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke quiz ini.'
                ], 403);
            }

            // Validasi request
            $validator = Validator::make($request->all(), [
                'questions' => 'required|array|min:1',
                'questions.*.question' => 'required|string|max:5000',
                'questions.*.type' => 'required|in:PG,IS,ES',
                'questions.*.score' => 'required|integer|min:1|max:100',
                'questions.*.explanation' => 'nullable|string|max:1000',
                'questions.*.choices' => 'required_if:questions.*.type,PG|array|min:2|max:6',
                'questions.*.choices.*.text' => 'required_with:questions.*.choices|string|max:1000',
                'questions.*.choices.*.is_correct' => 'required_if:questions.*.type,PG|boolean',
                'questions.*.short_answers' => 'required_if:questions.*.type,IS|array|min:1',
                'questions.*.short_answers.*' => 'required_with:questions.*.short_answers|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // PERBAIKAN: Hapus soal lama hanya setelah validasi sukses
                $quiz->questions()->delete();

                $createdQuestions = [];
                $order = 0;

                foreach ($request->questions as $questionData) {
                    // Buat soal baru
                    $question = ExamQuestion::create([
                        'exam_id' => $quiz->id,
                        'question' => $questionData['question'],
                        'type' => $questionData['type'],
                        'score' => $questionData['score'],
                        'explanation' => $questionData['explanation'] ?? null,
                        'order' => $order++,
                    ]);

                    // Jika PG, buat choices
                    if ($questionData['type'] === 'PG' && !empty($questionData['choices'])) {
                        $choiceIndex = 0;
                        foreach ($questionData['choices'] as $choiceData) {
                            if (!empty(trim($choiceData['text']))) {
                                ExamChoice::create([
                                    'question_id' => $question->id,
                                    'label' => chr(65 + $choiceIndex), // A, B, C, D
                                    'text' => $choiceData['text'],
                                    'is_correct' => $choiceData['is_correct'] ?? false,
                                    'order' => $choiceIndex++,
                                ]);
                            }
                        }
                    }
                    // Jika IS, simpan short_answers
                    elseif ($questionData['type'] === 'IS' && !empty($questionData['short_answers'])) {
                        $question->update([
                            'short_answers' => json_encode($questionData['short_answers'])
                        ]);
                    }

                    // Load choices untuk response
                    $question->load('choices');
                    $createdQuestions[] = $question;
                }

                // Update durasi quiz berdasarkan jumlah soal
                if ($quiz->time_per_question) {
                    $totalDuration = $quiz->questions()->count() * $quiz->time_per_question;
                    $quiz->update([
                        'duration' => ceil($totalDuration / 60) // Konversi ke menit
                    ]);
                }

                DB::commit();

                Log::info('Questions saved successfully', [
                    'quiz_id' => $quiz->id,
                    'count' => count($createdQuestions),
                    'status' => $quiz->status
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Soal berhasil disimpan!',
                    'questions' => $createdQuestions,
                    'total_questions' => count($createdQuestions),
                    'total_score' => array_sum(array_column($request->questions, 'score'))
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error storing questions', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan soal: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get questions list (AJAX)
     */
    public function getQuestionsList(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $questions = $quiz->questions()->with('choices')->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'questions' => $questions,
            'total_questions' => $questions->count(),
            'total_score' => $questions->sum('score')
        ]);
    }

    /**
     * Import questions from other exam
     */
    public function importQuestions(Request $request, Exam $quiz)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sourceExam = Exam::findOrFail($request->exam_id);

        // Cek ownership
        if ($sourceExam->teacher_id !== Auth::user()->teacher->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke soal ini'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $questions = $sourceExam->questions()->with('choices')->get();
            $importedCount = 0;

            $currentOrder = $quiz->questions()->max('order') ?? -1;

            foreach ($questions as $question) {
                $currentOrder++;

                $newQuestion = ExamQuestion::create([
                    'exam_id' => $quiz->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'score' => $question->score,
                    'explanation' => $question->explanation,
                    'order' => $currentOrder,
                ]);

                // Duplicate choices if exists
                if ($question->choices->isNotEmpty()) {
                    foreach ($question->choices as $choice) {
                        ExamChoice::create([
                            'question_id' => $newQuestion->id,
                            'label' => $choice->label,
                            'text' => $choice->text,
                            'is_correct' => $choice->is_correct,
                            'order' => $choice->order,
                        ]);
                    }
                }

                // Duplicate short_answers for IS type
                if ($question->type === 'IS' && $question->short_answers) {
                    $newQuestion->update([
                        'short_answers' => $question->short_answers
                    ]);
                }

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} soal",
                'count' => $importedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update question
     */
    public function updateQuestion(Request $request, Exam $quiz, ExamQuestion $question)
    {
        // Cek ownership
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:1000',
            'score' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:500',
            'choices' => 'required_if:type,PG|array|min:2',
            'choices.*.text' => 'required|string|max:500',
            'choices.*.is_correct' => 'required_if:type,PG|boolean',
            'short_answers' => 'required_if:type,IS|array|min:1',
            'short_answers.*' => 'string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $question->update([
                'question' => $request->question,
                'score' => $request->score,
                'explanation' => $request->explanation,
            ]);

            // Update choices jika PG
            if ($question->type === 'PG' && $request->has('choices')) {
                // Delete existing choices
                $question->choices()->delete();

                // Create new choices
                foreach ($request->choices as $index => $choiceData) {
                    ExamChoice::create([
                        'question_id' => $question->id,
                        'label' => chr(65 + $index),
                        'text' => $choiceData['text'],
                        'is_correct' => $choiceData['is_correct'] ?? false,
                        'order' => $index,
                    ]);
                }
            }

            // Update short_answers jika IS
            if ($question->type === 'IS' && $request->has('short_answers')) {
                $question->update([
                    'short_answers' => json_encode($request->short_answers)
                ]);
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
                'message' => 'Gagal memperbarui soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete question
     */
    public function deleteQuestion(Exam $quiz, ExamQuestion $question)
    {
        // Cek ownership
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $question->delete();

            // Reorder remaining questions
            $questions = $quiz->questions()->orderBy('order')->get();
            foreach ($questions as $index => $q) {
                $q->update(['order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus',
                'total_questions' => $quiz->questions()->count(),
                'total_score' => $quiz->questions()->sum('score')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder questions
     */
    public function reorderQuestions(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*' => 'exists:exam_questions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->order as $index => $questionId) {
                ExamQuestion::where('id', $questionId)
                    ->where('exam_id', $quiz->id)
                    ->update(['order' => $index]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan soal berhasil diubah'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah urutan soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview quiz
     */
    public function previewQuiz(Exam $quiz)
    {
        try {
            // Cek ownership
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                abort(403, 'Anda tidak memiliki akses ke quiz ini.');
            }

            // Ambil questions dengan choices - TIDAK peduli status
            $questions = $quiz->questions()
                ->with('choices')
                ->orderBy('order')
                ->get();

            Log::info('Preview quiz', [
                'quiz_id' => $quiz->id,
                'status' => $quiz->status,
                'questions_count' => $questions->count()
            ]);

            return view('guru.quiz.preview', compact('quiz', 'questions'));
        } catch (\Exception $e) {
            Log::error('Error in previewQuiz: ' . $e->getMessage());
            return redirect()->route('guru.quiz.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    /**
     * Finalize quiz (publish atau save draft)
     */
    public function finalizeQuiz(Request $request, Exam $quiz)
    {
        try {
            // Cek ownership
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'action' => 'required|in:publish,save_draft'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->action === 'publish') {
                // Cek minimal soal
                if ($quiz->questions()->count() < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimal harus ada 1 soal sebelum mempublish'
                    ], 422);
                }

                $quiz->update([
                    'status' => 'active',
                    'published_at' => now()
                ]);

                $message = 'Quiz berhasil dipublikasikan!';
            } else {
                $quiz->update(['status' => 'draft']);
                $message = 'Quiz disimpan sebagai draft.';
            }

            Log::info('Quiz finalized', [
                'quiz_id' => $quiz->id,
                'action' => $request->action,
                'status' => $quiz->status
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'exam_status' => $quiz->status,
                'redirect' => route('guru.quiz.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Error finalizing quiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish quiz
     */
    public function publishQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $quiz->update([
                'status' => 'active',
                'published_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dipublish!',
                'exam_status' => $quiz->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mempublish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unpublish quiz
     */
    public function unpublishQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $quiz->update([
                'status' => 'draft',
                'published_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil diunpublish!',
                'exam_status' => $quiz->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunpublish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate quiz
     */
    public function duplicateQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Duplicate exam
            $newExam = $quiz->replicate();
            $newExam->title = $quiz->title . ' (Salinan)';
            $newExam->status = 'draft';
            $newExam->published_at = null;
            $newExam->save();

            // Duplicate questions
            $questions = $quiz->questions()->with('choices')->get();

            foreach ($questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->exam_id = $newExam->id;
                $newQuestion->save();

                // Duplicate choices
                if ($question->choices->isNotEmpty()) {
                    foreach ($question->choices as $choice) {
                        $newChoice = $choice->replicate();
                        $newChoice->question_id = $newQuestion->id;
                        $newChoice->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil diduplikasi!',
                'new_exam_id' => $newExam->id,
                'redirect' => route('guru.quiz.questions', $newExam->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menduplikasi quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show quiz results
     */
    public function quizResults(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts' => $attempts->total(),
            'average_score' => $attempts->avg('final_score') ?? 0,
            'highest_score' => $attempts->max('final_score') ?? 0,
            'lowest_score' => $attempts->min('final_score') ?? 0,
        ];

        return view('guru.quiz.results', compact('quiz', 'attempts', 'stats'));
    }

    /**
     * Export quiz results
     */
    public function exportResults(Exam $quiz, $format = 'excel')
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        // Implement export logic here
        // You can use Laravel Excel package or simple CSV

        return response()->json([
            'success' => true,
            'message' => 'Export feature coming soon'
        ]);
    }

    /**
     * Show student results
     */
    public function studentResults(Exam $quiz, $studentId)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        $student = \App\Models\User::findOrFail($studentId);

        return view('guru.quiz.student-results', compact('quiz', 'attempts', 'student'));
    }

    /**
     * Show draft quizzes
     */
    public function draftQuizzes()
    {
        $teacher = Auth::user()->teacher;

        $quizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->where('status', 'draft')
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('guru.quiz.draft', compact('quizzes'));
    }

    /**
     * Show active quizzes
     */
    public function activeQuizzes()
    {
        $teacher = Auth::user()->teacher;

        $quizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->where('status', 'active')
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('guru.quiz.active', compact('quizzes'));
    }

    /**
     * Show completed quizzes
     */
    public function completedQuizzes()
    {
        $teacher = Auth::user()->teacher;

        $quizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->where('status', 'finished')
            ->with(['subject', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('guru.quiz.completed', compact('quizzes'));
    }

    /**
     * Bulk delete quizzes
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:exams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teacher = Auth::user()->teacher;

        try {
            DB::beginTransaction();

            $deletedCount = Exam::where('teacher_id', $teacher->id)
                ->where('type', 'QUIZ')
                ->whereIn('id', $request->ids)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} quiz berhasil dihapus",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk publish quizzes
     */
    public function bulkPublish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:exams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teacher = Auth::user()->teacher;

        try {
            DB::beginTransaction();

            $updatedCount = Exam::where('teacher_id', $teacher->id)
                ->where('type', 'QUIZ')
                ->where('status', 'draft')
                ->whereIn('id', $request->ids)
                ->update([
                    'status' => 'active',
                    'published_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} quiz berhasil dipublish",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mempublish quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        if ($quiz->type !== 'QUIZ') {
            // Perbaiki juga di sini
            return redirect()->route('guru.exams.edit', ['quiz' => $quiz->id]);
        }

        $teacher = auth()->user()->teacher;

        // Ambil semua subjects yang diajar guru
        $teacherId = $teacher->id;
        $teacherClassIds = TeacherClass::where('teacher_id', $teacherId)->pluck('id');
        $subjectIds = TeacherClassSubject::whereIn('teacher_class_id', $teacherClassIds)
            ->pluck('subject_id')
            ->unique();
        $mapels = Subject::whereIn('id', $subjectIds)->get();

        // Ambil kelas berdasarkan subject yang dipilih
        $classes = Classes::whereHas('teacherClasses', function ($q) use ($teacher, $quiz) {
            $q->where('teacher_id', $teacher->id)
                ->whereHas('subjects', function ($q2) use ($quiz) {
                    $q2->where('subjects.id', $quiz->subject_id);
                });
        })->get();

        return view('guru.quiz.edit', compact('quiz', 'mapels', 'classes'));
    }

    /**
     * Update the specified quiz
     */
    public function update(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'time_per_question' => 'required|integer|min:5|max:300',
            'quiz_mode' => 'required|in:live,homework',
            'duration' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'title' => $request->title,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'difficulty_level' => $request->difficulty_level,
                'time_per_question' => $request->time_per_question,
                'quiz_mode' => $request->quiz_mode,
                'duration' => $request->duration ?? $quiz->duration,

                // Quiz settings
                'shuffle_question' => $request->boolean('shuffle_question'),
                'shuffle_answer' => $request->boolean('shuffle_answer'),
                'show_score' => $request->boolean('show_score'),
                'show_correct_answer' => $request->boolean('show_correct_answer'),
                'fullscreen_mode' => $request->boolean('fullscreen_mode'),
                'block_new_tab' => $request->boolean('block_new_tab'),
                'prevent_copy_paste' => $request->boolean('prevent_copy_paste'),
                'show_result_after' => $request->show_result_after ?? 'immediately',
                'limit_attempts' => $request->limit_attempts ?? 1,
                'min_pass_grade' => $request->min_pass_grade ?? 0,

                // Quiz features
                'show_leaderboard' => $request->boolean('show_leaderboard'),
                'enable_music' => $request->boolean('enable_music'),
                'enable_memes' => $request->boolean('enable_memes'),
                'enable_powerups' => $request->boolean('enable_powerups'),
                'instant_feedback' => $request->boolean('instant_feedback'),
                'streak_bonus' => $request->boolean('streak_bonus'),
                'time_bonus' => $request->boolean('time_bonus'),
                'enable_retake' => $request->boolean('enable_retake'),
            ];

            // Update status jika ada
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            $quiz->update($updateData);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quiz berhasil diperbarui!',
                    'redirect' => route('guru.quiz.index')
                ]);
            }

            return redirect()->route('guru.quiz.index')
                ->with('success', 'Quiz berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui quiz: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal memperbarui quiz: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified quiz
     */
    public function destroy(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $quiz->delete();

            return redirect()->route('guru.quiz.index')
                ->with('success', 'Quiz berhasil dihapus!');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open quiz room
     */
    public function openRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validasi quiz harus active
            if ($quiz->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz harus dipublish terlebih dahulu sebelum membuka ruangan!'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Cek apakah sudah ada session aktif
                $session = $quiz->activeSession;

                if (!$session) {
                    // Buat session baru
                    $session = QuizSession::create([
                        'exam_id' => $quiz->id,
                        'teacher_id' => $user->teacher->id,
                        'session_code' => QuizSession::generateSessionCode(),
                        'session_status' => 'waiting',
                        'session_started_at' => now(),
                    ]);
                }

                // Buka ruangan
                $quiz->update([
                    'is_room_open' => true,
                    'is_quiz_started' => false,
                    'room_opened_at' => now(),
                ]);

                DB::commit();

                Log::info('Room opened', [
                    'quiz_id' => $quiz->id,
                    'session_id' => $session->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ruangan quiz berhasil dibuka! Siswa sekarang dapat bergabung.',
                    'session_id' => $session->id,
                    'session_code' => $session->session_code,
                    'redirect' => route('guru.quiz.room', $quiz->id)
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error opening room', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close quiz room
     */
    // Tambahkan method ini jika belum ada di QuizController.php
    public function closeRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Tutup ruangan
                $quiz->update([
                    'is_room_open' => false,
                    'is_quiz_started' => false,
                ]);

                // Hapus semua participant yang belum mulai
                $session = $quiz->activeSession;
                if ($session) {
                    QuizParticipant::where('quiz_session_id', $session->id)
                        ->whereIn('status', ['waiting', 'ready'])
                        ->delete();

                    // Update session jadi finished jika tidak ada yang started
                    $startedCount = QuizParticipant::where('quiz_session_id', $session->id)
                        ->where('status', 'started')
                        ->count();

                    if ($startedCount == 0) {
                        $session->update([
                            'session_status' => 'finished',
                            'session_ended_at' => now()
                        ]);
                    }
                }

                DB::commit();

                Log::info('Room closed', ['quiz_id' => $quiz->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ruangan quiz berhasil ditutup!'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error closing room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startQuiz(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan'
                ], 422);
            }

            // Cek apakah ada siswa yang ready
            $readyCount = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('status', 'ready')
                ->count();

            if ($readyCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada siswa yang siap. Tunggu minimal 1 siswa menekan tombol "Siap"'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Update quiz status
                $quiz->update([
                    'is_quiz_started' => true,
                    'quiz_started_at' => now(),
                ]);

                // Update session
                $session->update([
                    'session_status' => 'active',
                    'session_started_at' => now(),
                    'total_duration' => $quiz->duration * 60,
                ]);

                // Update semua siswa yang ready menjadi started
                QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('status', 'ready')
                    ->update([
                        'status' => 'started',
                        'started_at' => now(),
                    ]);

                // Siswa yang masih waiting tidak bisa ikut
                QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('status', 'waiting')
                    ->update([
                        'status' => 'disconnected',
                    ]);

                DB::commit();

                Log::info('Quiz started', [
                    'quiz_id' => $quiz->id,
                    'started_count' => $readyCount
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Quiz berhasil dimulai! {$readyCount} siswa akan otomatis diarahkan ke halaman pengerjaan.",
                    'started_count' => $readyCount
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error starting quiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show quiz room
     */
    public function showRoom($quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with(['class', 'subject', 'activeSession'])
                ->where('type', 'QUIZ')
                ->findOrFail($quizId);

            // Pastikan quiz milik guru ini
            if ($quiz->teacher_id != $user->teacher->id) {
                return redirect()->route('guru.quiz.index')
                    ->with('error', 'Anda tidak memiliki akses ke quiz ini.');
            }

            $session = $quiz->activeSession;

            return view('quiz.room', compact('quiz', 'session'));
        } catch (\Exception $e) {
            Log::error('Error in showQuizRoom: ' . $e->getMessage());
            return redirect()->route('guru.quiz.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get room status (AJAX)
     */
    public function getRoomParticipants(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session = $quiz->activeSession;
        $participants = $session ? $session->participants()->with('student')->get() : collect();

        $stats = $quiz->getParticipantStats();

        $formattedParticipants = $participants->map(function ($participant) {
            return [
                'id' => $participant->id,
                'name' => $participant->student->name ?? 'Unknown',
                'email' => $participant->student->email ?? '',
                'status' => $participant->status,
                'joined_at' => $participant->joined_at ? $participant->joined_at->format('H:i') : null,
                'ready_at' => $participant->ready_at ? $participant->ready_at->format('H:i') : null,
                'started_at' => $participant->started_at ? $participant->started_at->format('H:i') : null,
                'submitted_at' => $participant->submitted_at ? $participant->submitted_at->format('H:i') : null,
                'is_present' => (bool) $participant->is_present,
            ];
        });

        return response()->json([
            'success' => true,
            'participants' => $formattedParticipants,
            'stats' => $stats,
            'time_remaining' => $quiz->getQuizTimeRemaining(),
            'quiz_started' => (bool) $quiz->is_quiz_started,
            'room_open' => (bool) $quiz->is_room_open,
        ]);
    }

    /**
     * Get room status (AJAX)
     */
    public function getRoomStatus(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with(['class', 'activeSession'])->findOrFail($quizId);

            // Validasi akses guru
            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $session = $quiz->activeSession;

            // Data default jika belum ada session
            if (!$session) {
                return response()->json([
                    'success' => true,
                    'is_room_open' => false,
                    'is_quiz_started' => false,
                    'stats' => [
                        'total_students' => 0,
                        'joined' => 0,
                        'ready' => 0,
                        'started' => 0,
                        'submitted' => 0
                    ],
                    'participants' => [],
                    'time_remaining' => null,
                    'session_id' => null
                ]);
            }

            // Ambil participants - HANYA SISWA
            $participants = QuizParticipant::with(['student'])
                ->where('quiz_session_id', $session->id)
                ->where('is_present', true)
                ->whereHas('student', function ($query) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('name', 'Murid');
                    });
                })
                ->get();

            // Hitung statistik
            $stats = [
                'total_students' => $participants->count(),
                'joined' => $participants->where('status', '!=', 'disconnected')->count(),
                'ready' => $participants->where('status', 'ready')->count(),
                'started' => $participants->where('status', 'started')->count(),
                'submitted' => $participants->where('status', 'submitted')->count()
            ];

            // Format data participants
            $participantsData = $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'student_id' => $participant->student_id,
                    'name' => $participant->student->name ?? 'Unknown',
                    'email' => $participant->student->email ?? '',
                    'status' => $participant->status,
                    'joined_time' => $participant->joined_at ? $participant->joined_at->format('H:i') : '-',
                    'ready_time' => $participant->ready_at ? $participant->ready_at->format('H:i') : null,
                    'started_time' => $participant->started_at ? $participant->started_at->format('H:i') : null,
                ];
            });

            // Hitung sisa waktu
            $timeRemaining = null;
            if ($quiz->is_quiz_started && $session->session_started_at && $quiz->duration) {
                $elapsed = now()->diffInSeconds($session->session_started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            }

            return response()->json([
                'success' => true,
                'is_room_open' => (bool) $quiz->is_room_open,
                'is_quiz_started' => (bool) $quiz->is_quiz_started,
                'stats' => $stats,
                'participants' => $participantsData,
                'time_remaining' => $timeRemaining,
                'session_id' => $session->id,
                'session_code' => $session->session_code ?? null,
                'can_start' => $stats['ready'] > 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getRoomStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get participants list (AJAX)
     */
    public function getParticipants(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session = $quiz->activeSession;

        if (!$session) {
            return response()->json([
                'success' => true,
                'participants' => []
            ]);
        }

        $participants = $session->participants()
            ->with(['student:id,name,email'])
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'student_id' => $participant->student_id,
                    'student_name' => $participant->student->name,
                    'student_email' => $participant->student->email,
                    'status' => $participant->status,
                    'joined_at' => $participant->joined_at ? $participant->joined_at->format('H:i:s') : null,
                    'submitted_at' => $participant->submitted_at ? $participant->submitted_at->format('H:i:s') : null,
                    'is_present' => $participant->is_present,
                ];
            });

        return response()->json([
            'success' => true,
            'participants' => $participants
        ]);
    }


    // Tambahkan method ini setelah method getRoomParticipants()

    /**
     * Kick participant from room
     */
    public function kickParticipant(Request $request, $quizId, $participantId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->created_by != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $participant = QuizParticipant::findOrFail($participantId);

            // Validasi participant ada di session quiz ini
            if ($participant->exam_id != $quizId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant tidak ditemukan di quiz ini'
                ], 422);
            }

            DB::beginTransaction();

            // Hapus participant
            $participant->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dikeluarkan dari ruangan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in kickParticipant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tandai participant sebagai ready (guru bisa mark ready untuk siswa)
     */
    public function markParticipantAsReady(Request $request, $quizId, $participantId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->created_by != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $participant = QuizParticipant::findOrFail($participantId);

            // Validasi participant ada di session quiz ini
            if ($participant->exam_id != $quizId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant tidak ditemukan di quiz ini'
                ], 422);
            }

            DB::beginTransaction();

            // Update status menjadi ready
            $participant->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status peserta berhasil diubah menjadi Siap!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in markParticipantAsReady: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop quiz (hentikan paksa)
     */
    public function stopQuiz(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->created_by != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan'
                ], 422);
            }

            DB::beginTransaction();

            // Update quiz status
            $quiz->update([
                'is_quiz_started' => false,
                'is_room_open' => false,
            ]);

            // Update session
            $session->update([
                'session_status' => 'finished',
                'session_ended_at' => now(),
            ]);

            // Submit semua siswa yang masih mengerjakan
            QuizParticipant::where('quiz_session_id', $session->id)
                ->where('status', 'started')
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dihentikan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in stopQuiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
