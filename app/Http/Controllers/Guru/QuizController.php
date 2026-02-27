<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\QuizParticipant;
use App\Models\QuizSession;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($request->has('status')) {
            $quizzes->where('status', $request->status);
        }

        if ($request->has('class_id')) {
            $quizzes->where('class_id', $request->class_id);
        }

        if ($request->has('subject_id')) {
            $quizzes->where('subject_id', $request->subject_id);
        }

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

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $subjects = $teacher->subjectsTaughtInAcademicYear($yearId)->get();

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

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        // Ambil mapel yang diajar guru di tahun ajaran aktif
        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();

        // Ambil kelas yang diajar guru di tahun ajaran aktif
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();

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
            'duration' => 'nullable|integer|min:1|max:480',
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

            $duration = $request->duration ?? 30;

            // ✅ CREATE QUIZ
            $quiz = Exam::create([
                'teacher_id' => $teacher->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'title' => $request->title,
                'type' => 'QUIZ',
                'duration' => $duration,
                'start_at' => null,
                'end_at' => null,
                'difficulty_level' => $request->difficulty_level ?? 'medium',
                'time_per_question' => $request->time_per_question,
                'quiz_mode' => $request->quiz_mode,
                'status' => 'draft',

                'shuffle_question' => $request->boolean('shuffle_question', false),
                'shuffle_answer' => $request->boolean('shuffle_answer', false),
                'show_score' => $request->boolean('show_score', true),
                'show_correct_answer' => $request->boolean('show_correct_answer', false),
                'fullscreen_mode' => $request->boolean('fullscreen_mode', false),
                'block_new_tab' => $request->boolean('block_new_tab', false),
                'prevent_copy_paste' => $request->boolean('prevent_copy_paste', false),
                'show_result_after' => $request->show_result_after ?? 'immediately',
                'limit_attempts' => $request->limit_attempts ?? 1,
                'min_pass_grade' => $request->min_pass_grade ?? 0,

                'show_leaderboard' => $request->boolean('show_leaderboard', true),
                'enable_music' => $request->boolean('enable_music', false),
                'enable_memes' => $request->boolean('enable_memes', false),
                'enable_powerups' => $request->boolean('enable_powerups', false),
                'instant_feedback' => $request->boolean('instant_feedback', false),
                'streak_bonus' => $request->boolean('streak_bonus', false),
                'time_bonus' => $request->boolean('time_bonus', false),
                'enable_retake' => $request->boolean('enable_retake', false),

                'is_room_open' => false,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => null,
            ]);

            // ✅ AUTO ASSIGN QUIZ KE SEMUA SISWA DI KELAS
            $this->assignQuizToStudents($quiz);

            DB::commit();

            Log::info("Quiz created successfully", [
                'quiz_id' => $quiz->id,
                'class_id' => $quiz->class_id,
                'teacher_id' => $teacher->id
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quiz berhasil dibuat dan di-assign ke siswa!',
                    'exam_id' => $quiz->id,
                    'redirect' => route('guru.quiz.questions', $quiz->id)
                ]);
            }

            return redirect()->route('guru.quiz.questions', $quiz->id)
                ->with('success', 'Quiz berhasil dibuat dan di-assign ke siswa!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quiz: ' . $e->getMessage(), [
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
        $teacher = Auth::user()->teacher;

        if ($quiz->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        if ($quiz->type !== 'QUIZ') {
            return redirect()->route('guru.exams.soal', $quiz->id);
        }

        $questions = $quiz->questions()->with('choices')->orderBy('order')->get();
        $questionCount = $questions->count();
        $totalScore = $questions->sum('score');

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

    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        // Proses file (gunakan library seperti PhpSpreadsheet)
        // Kembalikan JSON dengan format:
        return response()->json([
            'success' => true,
            'questions' => [
                [
                    'type' => 'PG',
                    'question' => 'Contoh soal dari upload',
                    'score' => 10,
                    'choices' => [
                        ['text' => 'A', 'is_correct' => false],
                        ['text' => 'B', 'is_correct' => true],
                        ['text' => 'C', 'is_correct' => false],
                        ['text' => 'D', 'is_correct' => false]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Import preview
     */
    public function importPreview($quizId)
    {
        $sourceExam = Exam::findOrFail($quizId);

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

            $questions = $quiz->questions()
                ->with('choices')
                ->orderBy('order')
                ->get();

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
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke quiz ini.'
                ], 403);
            }

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

            $quiz->questions()->delete();

            $createdQuestions = [];
            $order = 0;

            foreach ($request->questions as $questionData) {
                $question = ExamQuestion::create([
                    'exam_id' => $quiz->id,
                    'question' => $questionData['question'],
                    'type' => $questionData['type'],
                    'score' => $questionData['score'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'order' => $order++,
                ]);

                if ($questionData['type'] === 'PG' && !empty($questionData['choices'])) {
                    $choiceIndex = 0;
                    foreach ($questionData['choices'] as $choiceData) {
                        if (!empty(trim($choiceData['text']))) {
                            ExamChoice::create([
                                'question_id' => $question->id,
                                'label' => chr(65 + $choiceIndex),
                                'text' => $choiceData['text'],
                                'is_correct' => $choiceData['is_correct'] ?? false,
                                'order' => $choiceIndex++,
                            ]);
                        }
                    }
                } elseif ($questionData['type'] === 'IS' && !empty($questionData['short_answers'])) {
                    $question->update([
                        'short_answers' => json_encode($questionData['short_answers'])
                    ]);
                }

                $question->load('choices');
                $createdQuestions[] = $question;
            }

            if ($quiz->time_per_question) {
                $totalDuration = $quiz->questions()->count() * $quiz->time_per_question;
                $quiz->update([
                    'duration' => ceil($totalDuration / 60)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil disimpan!',
                'questions' => $createdQuestions,
                'total_questions' => count($createdQuestions),
                'total_score' => array_sum(array_column($request->questions, 'score'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan soal: ' . $e->getMessage()
            ], 500);
        }
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

    public function storeSingleQuestion(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:5000',
            'type' => 'required|in:PG',
            'score' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:1000',
            'choices' => 'required|array|min:2|max:6',
            'choices.*.text' => 'required|string|max:1000',
            'choices.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $order = $quiz->questions()->max('order') ?? 0;
            $order++;

            $question = ExamQuestion::create([
                'exam_id' => $quiz->id,
                'question' => $request->question,
                'type' => 'PG',
                'score' => $request->score,
                'explanation' => $request->explanation ?? null,
                'order' => $order,
            ]);

            $choiceIndex = 0;
            foreach ($request->choices as $choiceData) {
                if (!empty(trim($choiceData['text']))) {
                    ExamChoice::create([
                        'question_id' => $question->id,
                        'label' => chr(65 + $choiceIndex),
                        'text' => $choiceData['text'],
                        'is_correct' => $choiceData['is_correct'],
                        'order' => $choiceIndex++,
                    ]);
                }
            }

            DB::commit();

            $question->load('choices');

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil disimpan',
                'question' => $question
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving single question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan soal: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update question
     */
    public function updateQuestion(Request $request, Exam $quiz, ExamQuestion $question)
    {
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type'       => 'required|in:PG,IS',               // ✅ tambahkan
            'question'   => 'required|string|max:1000',
            'score'      => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:500',
            'choices'    => 'required_if:type,PG|array|min:2',
            'choices.*.text'       => 'required|string|max:500',
            'choices.*.is_correct' => 'required_if:type,PG|boolean',
            'short_answers' => 'required_if:type,IS|array|min:1',
            'short_answers.*' => 'string|max:200',
        ]);

        // ... validasi gagal ...

        DB::beginTransaction();

        // Update data utama, termasuk type
        $question->update([
            'type'        => $request->type,      // ✅ perbarui tipe
            'question'    => $request->question,
            'score'       => $request->score,
            'explanation' => $request->explanation,
        ]);

        // Bersihkan data lama sesuai tipe baru
        if ($request->type === 'PG') {
            // Hapus short_answers (jika ada)
            $question->short_answers = null;
            $question->save();

            // Hapus pilihan lama & buat ulang
            $question->choices()->delete();
            foreach ($request->choices as $index => $choiceData) {
                ExamChoice::create([
                    'question_id' => $question->id,
                    'label'       => chr(65 + $index),
                    'text'        => $choiceData['text'],
                    'is_correct'  => $choiceData['is_correct'] ?? false,
                    'order'       => $index,
                ]);
            }
        } else { // IS
            // Hapus semua pilihan lama (jika sebelumnya PG)
            $question->choices()->delete();

            // Simpan short_answers
            $question->update([
                'short_answers' => json_encode($request->short_answers)
            ]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Soal berhasil diperbarui',
            'question' => $question->load('choices')
        ]);
    }

    /**
     * Delete question
     */
    public function deleteQuestion(Exam $quiz, ExamQuestion $question)
    {
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $question->delete();

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
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                abort(403, 'Anda tidak memiliki akses ke quiz ini.');
            }

            $questions = $quiz->questions()
                ->with('choices')
                ->orderBy('order')
                ->get();

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
                if ($quiz->questions()->count() < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimal harus ada 1 soal sebelum mempublish'
                    ], 422);
                }

                $quiz->update([
                    'status' => 'active',
                ]);

                $message = 'Quiz berhasil dipublikasikan!';
            } else {
                $quiz->update(['status' => 'draft']);
                $message = 'Quiz disimpan sebagai draft.';
            }

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

            $newExam = $quiz->replicate();
            $newExam->title = $quiz->title . ' (Salinan)';
            $newExam->status = 'draft';
            $newExam->save();

            $questions = $quiz->questions()->with('choices')->get();

            foreach ($questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->exam_id = $newExam->id;
                $newQuestion->save();

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
            abort(403);
        }

        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts' => ExamAttempt::where('exam_id', $quiz->id)->count(),
            'average_score'  => ExamAttempt::where('exam_id', $quiz->id)->avg('final_score') ?? 0,
            'highest_score'  => ExamAttempt::where('exam_id', $quiz->id)->max('final_score') ?? 0,
            'lowest_score'   => ExamAttempt::where('exam_id', $quiz->id)->min('final_score') ?? 0,
        ];

        return view('guru.quiz.results', compact('quiz', 'attempts', 'stats'));
    }

    public function attemptDetail(Exam $quiz, ExamAttempt $attempt)
    {
        // Verifikasi akses (gunakan $quiz atau $attempt->exam)
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403);
        }

        // Pastikan teacher yang login memiliki akses ke quiz ini
        $quiz = $attempt->exam;
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Ambil semua jawaban attempt ini, sertakan relasi soal dan kunci jawaban
        $answers = ExamAnswer::where('attempt_id', $attempt->id)
            ->with(['question', 'choice', 'question.choices' => function ($q) {
                $q->where('is_correct', true); // ambil kunci jawaban
            }])
            ->get();

        $totalQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $score = $attempt->final_score;

        return view('guru.quiz.attempt-detail', compact(
            'attempt',
            'quiz',
            'answers',
            'totalQuestions',
            'correctAnswers',
            'score'
        ));
    }

    /**
     * Export quiz results
     */
    public function exportResults(Exam $quiz, $format = 'excel')
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

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
            return redirect()->route('guru.exams.edit', ['exam' => $quiz->id]);
        }

        $teacher = auth()->user()->teacher;
        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();

        // Kelas yang diajar guru untuk mapel yang sudah dipilih di quiz
        $classes = $teacher->classesTaughtInAcademicYear($yearId)
            ->wherePivot('subject_id', $quiz->subject_id)  // filter sesuai mapel quiz
            ->get();

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
            'difficulty_level' => 'in:easy,medium,hard',
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

                'show_leaderboard' => $request->boolean('show_leaderboard'),
                'enable_music' => $request->boolean('enable_music'),
                'enable_memes' => $request->boolean('enable_memes'),
                'enable_powerups' => $request->boolean('enable_powerups'),
                'instant_feedback' => $request->boolean('instant_feedback'),
                'streak_bonus' => $request->boolean('streak_bonus'),
                'time_bonus' => $request->boolean('time_bonus'),
                'enable_retake' => $request->boolean('enable_retake'),
            ];

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

    // ==================== MANAJEMEN RUANGAN QUIZ ====================

    /**
     * Open quiz room
     */
    public function openRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with(['class', 'subject'])->findOrFail($quizId);

            // Verify teacher ownership
            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Verify quiz has questions
            if ($quiz->questions()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz harus memiliki minimal 1 soal sebelum dibuka.'
                ], 422);
            }

            DB::beginTransaction();

            // Generate session code
            $sessionCode = $this->generateUniqueSessionCode();

            // Create quiz session
            $session = QuizSession::create([
                'exam_id' => $quiz->id,
                'teacher_id' => $user->teacher->id,
                'session_code' => $sessionCode,
                'session_status' => 'waiting',
                'session_started_at' => null,
                'session_ended_at' => null,
                'total_duration' => $quiz->duration,
                'total_students' => 0,
                'students_joined' => 0,
                'students_ready' => 0,
                'students_started' => 0,
                'students_submitted' => 0,
            ]);

            // Update quiz status
            $quiz->update([
                'is_room_open' => true,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => $quiz->duration * 60, // in seconds
                'status' => 'active',
            ]);

            DB::commit();

            Log::info("Quiz room opened successfully", [
                'quiz_id' => $quiz->id,
                'session_id' => $session->id,
                'session_code' => $sessionCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil dibuka!',
                'session_code' => $sessionCode,
                'session_id' => $session->id,
                'redirect' => route('guru.quiz.room', $quiz->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error opening quiz room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close quiz room
     */
    public function closeRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

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

            DB::beginTransaction();

            // Update quiz
            $quiz->update([
                'is_room_open' => false,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => null,
            ]);

            // Update session
            $session->update([
                'session_status' => 'finished',
                'session_ended_at' => now(),
            ]);

            // Clear all participants
            QuizParticipant::where('quiz_session_id', $session->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil ditutup!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing quiz room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function assignQuizToStudents(Exam $quiz)
    {
        try {
            // Get active academic year
            $activeYear = AcademicYear::active()->first();

            if (!$activeYear) {
                Log::warning("No active academic year found when assigning quiz", [
                    'quiz_id' => $quiz->id
                ]);
                return;
            }

            // Get all students in the class for current academic year
            $studentIds = StudentClassAssignment::where('class_id', $quiz->class_id)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('student_id')
                ->toArray();

            if (empty($studentIds)) {
                Log::warning("No students found in class", [
                    'quiz_id' => $quiz->id,
                    'class_id' => $quiz->class_id,
                    'academic_year_id' => $activeYear->id
                ]);
                return;
            }

            // Get User IDs from Student records
            $userIds = Student::whereIn('id', $studentIds)
                ->pluck('user_id')
                ->toArray();

            if (empty($userIds)) {
                Log::warning("No user IDs found for students", [
                    'quiz_id' => $quiz->id,
                    'student_ids' => $studentIds
                ]);
                return;
            }

            // Create exam assignment records for each student
            $assignments = [];
            foreach ($userIds as $userId) {
                $assignments[] = [
                    'exam_id' => $quiz->id,
                    'student_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert exam assignments
            if (!empty($assignments)) {
                DB::table('exam_student')->insert($assignments);

                Log::info("Quiz assigned to students successfully", [
                    'quiz_id' => $quiz->id,
                    'student_count' => count($assignments)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error assigning quiz to students: ' . $e->getMessage(), [
                'quiz_id' => $quiz->id,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception, just log it
        }
    }

    /**
     * Start quiz (mulai pengerjaan)
     */
    public function startQuiz(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

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

            DB::beginTransaction();

            // Update quiz
            $quiz->update([
                'is_quiz_started' => true,
                'quiz_started_at' => now(),
                'quiz_remaining_time' => $quiz->duration * 60, // in seconds
            ]);

            // Update session
            $session->update([
                'session_status' => 'active',
                'session_started_at' => now(),
            ]);

            // Create exam attempts for all participants who are ready
            $readyParticipants = $session->participants()
                ->where('status', 'ready')
                ->get();

            foreach ($readyParticipants as $participant) {
                // Create exam attempt
                ExamAttempt::create([
                    'exam_id' => $quiz->id,
                    'student_id' => $participant->student_id,
                    'quiz_session_id' => $session->id,
                    'started_at' => now(),
                    'status' => 'in_progress',
                    'remaining_time' => $quiz->duration * 60,
                    'ip_address' => $participant->ip_address,
                    'user_agent' => $participant->user_agent,
                    'exam_settings' => json_encode([]),
                ]);

                // Update participant status
                $participant->update([
                    'status' => 'started',
                    'started_at' => now(),
                ]);
            }

            $session->updateStats();

            DB::commit();

            Log::info("Quiz started successfully", [
                'quiz_id' => $quiz->id,
                'session_id' => $session->id,
                'participants_started' => $readyParticipants->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz dimulai! Peserta dapat mengerjakan sekarang.',
                'started_count' => $readyParticipants->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting quiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show quiz room (halaman guru)
     */
    public function showRoom(Exam $quiz)
    {
        $teacher = Auth::user()->teacher;

        if ($quiz->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        $quiz->load([
            'class',
            'subject',
            'questions', 'activeSession.participants.student'
        ]);

        return view('quiz.room', compact('quiz'));
    }

    /**
     * Get room participants list (AJAX)
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

    // =========================================================================
    // ✅ FIX #4 – getRoomStatus() dengan violation_count & fallback nama
    // =========================================================================
    /**
     * GET /guru/quiz/{quiz}/room/status
     */
    public function getRoomStatus(Exam $quiz)
    {
        try {
            $teacher = Auth::user()->teacher;

            if ($quiz->teacher_id !== $teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $session = $quiz->activeSession;

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak aktif'
                ], 422);
            }

            // Get participants with their status
            $participants = $session->participants()
                ->with(['student:id,name,email'])
                ->get()
                ->map(function ($participant) {
                    $attempt = ExamAttempt::where('student_id', $participant->student_id)
                        ->where('exam_id', $participant->exam_id)
                        ->where('quiz_session_id', $participant->quiz_session_id)
                        ->first();

                    return [
                        'id' => $participant->id,
                        'student_id' => $participant->student_id,
                        'student_name' => $participant->student->name ?? 'Unknown',
                        'student_email' => $participant->student->email ?? '',
                        'status' => $participant->status,
                        'joined_at' => $participant->joined_at ? $participant->joined_at->format('H:i:s') : null,
                        'ready_at' => $participant->ready_at ? $participant->ready_at->format('H:i:s') : null,
                        'started_at' => $participant->started_at ? $participant->started_at->format('H:i:s') : null,
                        'submitted_at' => $participant->submitted_at ? $participant->submitted_at->format('H:i:s') : null,
                        'is_present' => $participant->is_present,
                        'violation_count' => $attempt ? $attempt->violation_count : 0,
                        'is_cheating_detected' => $attempt ? $attempt->is_cheating_detected : false,
                    ];
                });

            $stats = $session->updateStats();

            return response()->json([
                'success' => true,
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'is_room_open' => $quiz->is_room_open,
                    'is_quiz_started' => $quiz->is_quiz_started,
                    'quiz_started_at' => $quiz->quiz_started_at ? $quiz->quiz_started_at->format('Y-m-d H:i:s') : null,
                    'quiz_remaining_time' => $quiz->quiz_remaining_time,
                    'duration' => $quiz->duration,
                ],
                'session' => [
                    'id' => $session->id,
                    'session_code' => $session->session_code,
                    'session_status' => $session->session_status,
                ],
                'stats' => $stats,
                'participants' => $participants,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting room status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // ✅ FIX #5 – quizLeaderboard() dengan return JSON jika AJAX
    // =========================================================================
    /**
     * GET /guru/quiz/{quiz}/leaderboard – papan peringkat (bisa JSON atau view)
     */
    public function quizLeaderboard($quizId)
    {
        try {
            $quiz = \App\Models\Exam::findOrFail($quizId);

            // ✅ FIX: Ambil submitted DAN timeout
            $attempts = \App\Models\ExamAttempt::where('exam_id', $quiz->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->with('student:id,name,email')
                ->orderByDesc('final_score')
                ->orderBy('ended_at', 'asc')
                ->limit(10)
                ->get();

            \Illuminate\Support\Facades\Log::info('Guru Leaderboard: ' . $attempts->count() . ' attempts for quiz ' . $quiz->id);

            $leaderboard = $attempts->map(function ($attempt, $index) {
                // ✅ FIX: Fallback nama bertingkat
                $studentName = 'Unknown';
                if ($attempt->student) {
                    $studentName = $attempt->student->name ?? 'Unknown';
                } else {
                    $student = \App\Models\User::find($attempt->student_id);
                    $studentName = $student ? $student->name : 'Peserta ' . $attempt->student_id;
                }

                $timeTaken = 0;
                if ($attempt->ended_at && $attempt->started_at) {
                    $timeTaken = $attempt->started_at->diffInSeconds($attempt->ended_at);
                }

                return [
                    'rank'         => $index + 1,
                    'student_id'   => $attempt->student_id,
                    'student_name' => $studentName,
                    'name'         => $studentName,            // fallback frontend
                    'score'        => round($attempt->final_score ?? 0, 2),
                    'final_score'  => round($attempt->final_score ?? 0, 2),
                    'time_taken'   => $timeTaken,
                    'submitted_at' => $attempt->ended_at?->format('Y-m-d H:i:s'),
                    'status'       => $attempt->status,
                ];
            });

            // ✅ FIX: Return JSON jika request AJAX (dari room.blade.php)
            if (request()->expectsJson() || request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success'     => true,
                    'leaderboard' => $leaderboard,
                    'count'       => $leaderboard->count(),
                    'quiz_id'     => $quiz->id,
                    'quiz_title'  => $quiz->title,
                ]);
            }

            // Return view untuk akses langsung
            return view('guru.quiz.leaderboard', compact('quiz', 'leaderboard'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in Guru quizLeaderboard: ' . $e->getMessage());
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success'     => false,
                    'leaderboard' => [],
                    'message'     => 'Error: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Gagal memuat leaderboard.');
        }
    }

    /**
     * Get quiz leaderboard (AJAX)
     */
    public function getQuizLeaderboard($quizId)
    {
        // Alias untuk quizLeaderboard
        return $this->quizLeaderboard($quizId);
    }

    /**
     * Get participants list (AJAX) – alternatif
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

    /**
     * Generate unique session code
     */
    private function generateUniqueSessionCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (QuizSession::where('session_code', $code)->exists());

        return $code;
    }

    /**
     * Kick participant from room
     */
    public function kickParticipant(Request $request, $quizId, $participantId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $participant = QuizParticipant::findOrFail($participantId);
            $session = $quiz->activeSession;

            if (!$session || $participant->quiz_session_id != $session->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant tidak ditemukan'
                ], 422);
            }

            DB::beginTransaction();

            $participant->delete();
            $session->updateStats();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dikeluarkan!'
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
     * Mark participant as ready (guru bisa tandai)
     */
    public function markParticipantAsReady(Request $request, $quizId, $participantId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $participant = QuizParticipant::findOrFail($participantId);
            $session = $quiz->activeSession;

            if (!$session || $participant->quiz_session_id != $session->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participant tidak ditemukan'
                ], 422);
            }

            DB::beginTransaction();

            $participant->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);

            $session->updateStats();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status peserta berhasil diubah menjadi siap!'
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

            DB::beginTransaction();

            // Update quiz
            $quiz->update([
                'is_quiz_started' => false,
                'is_room_open' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => null,
            ]);

            // Update session
            $session->update([
                'session_status' => 'finished',
                'session_ended_at' => now(),
            ]);

            // Force submit all active attempts
            $activeAttempts = ExamAttempt::where('exam_id', $quiz->id)
                ->where('quiz_session_id', $session->id)
                ->where('status', 'in_progress')
                ->get();

            foreach ($activeAttempts as $attempt) {
                $attempt->submit();
            }

            // Update participants
            QuizParticipant::where('quiz_session_id', $session->id)
                ->whereIn('status', ['started', 'ready', 'waiting'])
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil dihentikan! Semua peserta telah disubmit otomatis.'
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
