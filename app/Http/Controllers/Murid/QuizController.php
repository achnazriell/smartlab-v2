<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamChoice;
use App\Models\ExamQuestion;
use App\Models\QuizParticipant;
use App\Models\QuizSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    // ==========================================
    // HELPER
    // ==========================================

    private function getStudent()
    {
        return Auth::user()->student;
    }

    /**
     * Ambil user_id siswa untuk query ke tabel yang FK-nya ke users
     * exam_attempts.student_id, quiz_participants.student_id, exam_answers.student_id
     * semuanya constrained('users'), bukan ke tabel students
     */
    private function getStudentUserId(): int
    {
        return Auth::id();
    }

    /**
     * Base query untuk quiz — WAJIB pakai withoutGlobalScopes()
     * karena model Exam punya scope yang broken:
     * "exists (select * from users inner join exam_student ... where students.user_id = X)"
     * yang menyebabkan SQLSTATE[42S22]: Unknown column 'students.user_id'
     *
     * Selain itu, tipe quiz di database tersimpan sebagai 'QUIZ' (uppercase)
     */
    private function quizQuery()
    {
        return Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->where('status', '!=', 'draft');
    }

    // ==========================================
    // LISTING
    // ==========================================

    public function index()
    {
        $student = $this->getStudent();

        $quizzes = $this->quizQuery()
            ->whereIn('status', ['active', 'finished'])
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $myAttempts = ExamAttempt::where('student_id', Auth::id())
            ->pluck('exam_id')
            ->toArray();

        return view('murid.quiz.index', compact('quizzes', 'myAttempts'));
    }

    public function activeQuiz()
    {
        $quizzes = $this->quizQuery()
            ->where('status', 'active')
            ->where('is_room_open', true)
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('murid.quiz.index', compact('quizzes'));
    }

    public function upcomingQuiz()
    {
        $quizzes = $this->quizQuery()
            ->where('status', 'active')
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('murid.quiz.index', compact('quizzes'));
    }

    public function completedQuiz()
    {
        $student = $this->getStudent();

        $attemptedIds = ExamAttempt::where('student_id', Auth::id())
            ->whereNotNull('submitted_at')
            ->pluck('exam_id');

        $quizzes = $this->quizQuery()
            ->whereIn('id', $attemptedIds)
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('murid.quiz.index', compact('quizzes'));
    }

    // ==========================================
    // ROOM — JOIN & STATUS
    // ==========================================

    /**
     * Show the waiting room page for student
     * Pakai ID manual, bukan route model binding — untuk bypass scope
     */
    public function joinQuizRoomPage($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->findOrFail($quiz);

        if (!$quiz->is_room_open && $quiz->status !== 'active') {
            return redirect()->route('quiz.index')
                ->with('error', 'Quiz ini tidak tersedia saat ini.');
        }

        $student = $this->getStudent();
        $session = $quiz->activeSession;

        // Ambil data participant jika sudah join
        $participant = null;
        if ($session) {
            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();
        }

        return view('quiz.room', compact('quiz', 'session', 'participant'));
    }

    /**
     * Student joins the quiz room
     */
    public function joinQuizRoom(Request $request, $quiz)
    {
        $quiz = Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->findOrFail($quiz);

        if (!$quiz->is_room_open) {
            return response()->json(['success' => false, 'message' => 'Ruangan quiz belum dibuka'], 422);
        }

        if ($quiz->is_quiz_started) {
            return response()->json(['success' => false, 'message' => 'Quiz sudah dimulai, tidak bisa bergabung'], 422);
        }

        $student = $this->getStudent();
        $session = $quiz->activeSession;

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi quiz tidak ditemukan'], 404);
        }

        // Cek apakah sudah join sebelumnya
        $existing = QuizParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', Auth::id())
            ->first();

        if ($existing) {
            if (!$existing->is_present) {
                $existing->update(['is_present' => true, 'status' => 'waiting', 'joined_at' => now()]);
            }
            return response()->json([
                'success'        => true,
                'message'        => 'Sudah tergabung di room ini',
                'participant_id' => $existing->id,
                'status'         => $existing->status,
            ]);
        }

        // Buat participant baru
        $participant = QuizParticipant::create([
            'quiz_session_id' => $session->id,
            'student_id'      => Auth::id(),
            'exam_id'         => $quiz->id,
            'status'          => 'waiting',
            'joined_at'       => now(),
            'is_present'      => true,
            'ip_address'      => $request->ip(),
            'user_agent'      => $request->userAgent(),
        ]);

        // Update stats di session
        if (method_exists($session, 'updateStats')) {
            $session->updateStats();
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Berhasil bergabung ke room quiz!',
            'participant_id' => $participant->id,
            'status'         => $participant->status,
            'quiz_status'    => $quiz->room_status,
        ]);
    }

    /**
     * Get current room status (polling endpoint)
     */
    public function getQuizRoomStatus($quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();
        $session = $quiz->activeSession;

        $participantStatus = 'not_joined';
        if ($session) {
            $p = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();
            $participantStatus = $p ? $p->status : 'not_joined';
        }

        return response()->json([
            'success'         => true,
            'is_room_open'    => (bool) $quiz->is_room_open,
            'is_quiz_started' => (bool) $quiz->is_quiz_started,
            'quiz_status'     => $quiz->status,
            'room_status'     => $quiz->room_status,
            'my_status'       => $participantStatus,
            'session_code'    => $session?->session_code,
            'time_remaining'  => $quiz->getQuizTimeRemaining(),
        ]);
    }

    /**
     * Student marks themselves as ready
     */
    public function markAsReady($quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();
        $session = $quiz->activeSession;

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak ditemukan'], 404);
        }

        $participant = QuizParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        $participant->update(['status' => 'ready']);

        return response()->json(['success' => true, 'message' => 'Anda ditandai siap!', 'status' => 'ready']);
    }

    // ==========================================
    // PLAY QUIZ
    // ==========================================

    public function playQuiz($quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->with('questions')->findOrFail($quiz);
        $student = $this->getStudent();

        if (!$quiz->is_quiz_started && !$quiz->is_room_open) {
            return redirect()->route('quiz.room', $quiz->id)
                ->with('error', 'Quiz belum dimulai.');
        }

        $session     = $quiz->activeSession;
        $participant = null;

        if ($session) {
            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();

            if (!$participant) {
                return redirect()->route('quiz.room', $quiz->id)
                    ->with('error', 'Anda belum bergabung ke room quiz ini.');
            }

            if (in_array($participant->status, ['kicked', 'disqualified'])) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Anda tidak diizinkan mengikuti quiz ini.');
            }

            if ($participant->status === 'submitted') {
                $attempt = ExamAttempt::where('exam_id', $quiz->id)
                    ->where('student_id', Auth::id())
                    ->latest()
                    ->first();
                return redirect()->route('quiz.result', [$quiz->id, $attempt->id])
                    ->with('info', 'Anda sudah menyelesaikan quiz ini.');
            }

            // Update status jadi started
            if ($participant->status !== 'started') {
                $participant->update(['status' => 'started', 'started_at' => now()]);
            }
        }

        // Buat atau ambil attempt
        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $quiz->id, 'student_id' => Auth::id()],
            ['status' => 'started', 'started_at' => now()]
        );

        // Load soal
        $questions = $quiz->questions()->orderBy('order')->get();
        if ($quiz->shuffle_question) {
            $questions = $questions->shuffle();
        }

        // Load jawaban tersimpan
        $savedAnswers = ExamAnswer::where('attempt_id', $attempt->id)
            ->pluck('answer', 'question_id')
            ->toArray();

        return view('murid.quiz.play_simple', compact('quiz', 'attempt', 'questions', 'savedAnswers', 'session', 'participant'));
    }

    /**
     * Save answer progress (auto-save per soal)
     */
    public function saveQuizProgress(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('submitted_at')
            ->firstOrFail();

        $request->validate([
            'question_id' => 'required|integer',
            'answer'      => 'required|string|max:10',
        ]);

        $question = ExamQuestion::where('id', $request->question_id)
            ->where('exam_id', $quiz->id)
            ->firstOrFail();

        ExamAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $question->id],
            ['answer' => $request->answer, 'answered_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Jawaban tersimpan']);
    }

    /**
     * Submit quiz — hitung skor dan finalisasi
     * Frontend mengirim: { answers:[{question_id,choice_id,text_answer}], total_score, time_spent }
     */
    public function submitQuiz(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->with('questions.choices')->findOrFail($quiz);
        $student = $this->getStudent();

        // Cari attempt aktif (belum di-submit)
        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('submitted_at')
            ->first();

        // Fallback: attempt yang in_progress/started tapi submitted_at belum terisi
        if (!$attempt) {
            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', Auth::id())
                ->whereIn('status', ['in_progress', 'started'])
                ->latest()
                ->first();
        }

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan. Silakan mulai quiz dari awal.'], 404);
        }

        DB::beginTransaction();
        try {
            $incomingAnswers = $request->input('answers', []);
            $questions       = $quiz->questions;
            $totalScore      = 0;
            $maxScore        = 0;

            // Simpan/update jawaban dari payload frontend
            foreach ((array) $incomingAnswers as $ans) {
                if (empty($ans['question_id'])) continue;

                $questionId = (int) $ans['question_id'];
                $choiceId   = !empty($ans['choice_id']) ? (int) $ans['choice_id'] : null;
                $textAnswer = isset($ans['text_answer']) && $ans['text_answer'] !== null
                    ? (string) $ans['text_answer']
                    : null;

                // Cari label jawaban dari choice jika ada
                $answerLabel = null;
                if ($choiceId) {
                    $choice = ExamChoice::find($choiceId);
                    $answerLabel = $choice ? (string) $choice->label : null;
                }

                ExamAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'exam_id'     => $quiz->id,
                        'student_id'  => Auth::id(),
                        'choice_id'   => $choiceId,
                        'answer'      => $answerLabel ?? $textAnswer,
                        'answer_text' => $textAnswer,
                        'answered_at' => now(),
                    ]
                );
            }

            // Hitung skor berdasarkan semua jawaban tersimpan
            $savedAnswers = ExamAnswer::where('attempt_id', $attempt->id)
                ->get()
                ->keyBy('question_id');

            foreach ($questions as $q) {
                $maxScore   += (int) ($q->score ?? 10);
                $isCorrect   = false;
                $earnedScore = 0;

                $savedAns = $savedAnswers->get($q->id);

                if ($savedAns) {
                    if (in_array($q->type, ['PG', 'DD', 'BS', 'PGK'])) {
                        // Pilihan ganda: cek via choice is_correct
                        if ($savedAns->choice_id) {
                            $choice    = $q->choices->firstWhere('id', $savedAns->choice_id);
                            $isCorrect = $choice && $choice->is_correct;
                        } else {
                            // Fallback: bandingkan label
                            $givenLabel  = strtoupper(trim((string) ($savedAns->answer ?? '')));
                            $correctChoice = $q->choices->firstWhere('is_correct', true);
                            $correctLabel  = $correctChoice ? strtoupper(trim((string) $correctChoice->label)) : '';
                            $isCorrect     = $givenLabel !== '' && $givenLabel === $correctLabel;
                        }
                    } elseif ($q->type === 'IS') {
                        // Isian singkat
                        $givenText   = strtolower(trim((string) ($savedAns->answer_text ?? $savedAns->answer ?? '')));
                        $validAnswers = array_map(
                            fn($a) => strtolower(trim((string) $a)),
                            (array) ($q->short_answers ?? [])
                        );
                        $isCorrect = $givenText !== '' && in_array($givenText, $validAnswers);
                    }
                    // ES (Essay): dinilai manual, is_correct = false

                    if ($isCorrect) $earnedScore = (int) ($q->score ?? 10);

                    $savedAns->update([
                        'is_correct' => $isCorrect,
                        'score'      => $earnedScore,
                    ]);

                    $totalScore += $earnedScore;
                }
            }

            $finalScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;

            $attempt->update([
                'status'       => 'submitted',
                'score'        => $totalScore,
                'final_score'  => $finalScore,
                'submitted_at' => now(),
                'ended_at'     => now(),
            ]);

            // Update status participant di QuizSession
            $session = $quiz->activeSession;
            if ($session) {
                QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('student_id', Auth::id())
                    ->update(['status' => 'submitted', 'submitted_at' => now()]);

                if (method_exists($session, 'updateStats')) {
                    $session->updateStats();
                }
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Quiz berhasil dikumpulkan!',
                'score'      => $finalScore,
                'attempt_id' => $attempt->id,
                'redirect'   => route('quiz.result', [$quiz->id, $attempt->id]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('submitQuiz error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Gagal mengumpulkan quiz: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // RESULTS
    // ==========================================

    public function quizResult($quiz, $attempt)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->with('questions.choices')->findOrFail($quiz);
        $student = $this->getStudent();

        $attempt = ExamAttempt::where('id', $attempt)
            ->where('student_id', Auth::id())
            ->where('exam_id', $quiz->id)
            ->firstOrFail();

        $attempt->load('answers');
        $questions = $quiz->questions()->with('choices')->orderBy('order')->get();

        // ===== Hitung statistik untuk view =====
        $totalQuestions    = $questions->count();
        $answers           = $attempt->answers->keyBy('question_id');
        $correctAnswers    = $attempt->answers->where('is_correct', true)->count();
        $answeredQuestions = $attempt->answers->count();
        $incorrectAnswers  = $attempt->answers
            ->filter(fn($a) => !$a->is_correct && ($a->choice_id || $a->answer_text))
            ->count();

        // Persentase dari final_score (sudah 0-100)
        $percentage = (float) ($attempt->final_score > 0
            ? $attempt->final_score
            : ($totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0));

        // Quiz stats dari exam_settings
        $examSettings = is_array($attempt->exam_settings) ? $attempt->exam_settings : [];
        $quizStats = !empty($examSettings) ? [
            'streak_count' => $examSettings['streak_count'] ?? 0,
            'bonus_points' => $examSettings['bonus_points'] ?? 0,
            'time_bonus'   => $examSettings['time_bonus'] ?? 0,
            'streak_bonus' => $examSettings['streak_bonus'] ?? 0,
        ] : null;

        // Leaderboard
        $showLeaderboard = (bool) ($quiz->show_leaderboard ?? false);
        $leaderboard     = [];
        $userPosition    = 0;

        if ($showLeaderboard) {
            $allAttempts = ExamAttempt::where('exam_id', $quiz->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->orderByDesc('final_score')
                ->orderBy('ended_at')
                ->get();

            $position = 1;
            foreach ($allAttempts as $a) {
                $user = \App\Models\User::find($a->student_id);
                $leaderboard[] = [
                    'position'     => $position,
                    'student_id'   => $a->student_id,
                    'student_name' => $user?->name ?? 'Peserta',
                    'score'        => (float) $a->final_score,
                    'time_taken'   => ($a->ended_at && $a->started_at)
                        ? (int) $a->started_at->diffInSeconds($a->ended_at)
                        : 0,
                ];
                if ($a->student_id == Auth::id()) {
                    $userPosition = $position;
                }
                $position++;
            }
        }

        // Bisa retake?
        $attemptCount = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        $canRetake = (bool) $quiz->enable_retake &&
            ($quiz->limit_attempts <= 0 || $attemptCount < $quiz->limit_attempts);

        return view('murid.quiz.hasil', compact(
            'quiz',
            'attempt',
            'questions',
            'answers',
            'percentage',
            'correctAnswers',
            'incorrectAnswers',
            'answeredQuestions',
            'totalQuestions',
            'quizStats',
            'showLeaderboard',
            'leaderboard',
            'userPosition',
            'canRetake'
        ));
    }

    public function quizLeaderboard($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        $leaderboard = ExamAttempt::where('exam_id', $quiz->id)
            ->whereNotNull('score')
            ->orderByDesc('score')
            ->orderBy('submitted_at')
            ->take(50)
            ->get()
            ->map(function ($a, $i) {
                $user = \App\Models\User::find($a->student_id);
                return [
                    'rank'  => $i + 1,
                    'name'  => $user?->name ?? 'Peserta ' . $a->student_id,
                    'score' => $a->score,
                    'time'  => $a->submitted_at,
                ];
            });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
        }

        return view('murid.quiz.leaderboard', compact('quiz', 'leaderboard'));
    }

    public function leaderboardTop5($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        $leaderboard = ExamAttempt::where('exam_id', $quiz->id)
            ->whereNotNull('score')
            ->orderByDesc('score')
            ->orderBy('submitted_at')
            ->take(5)
            ->get()
            ->map(function ($a, $i) {
                $user = \App\Models\User::find($a->student_id);
                return [
                    'rank'  => $i + 1,
                    'name'  => $user?->name ?? 'Peserta ' . $a->student_id,
                    'score' => $a->score,
                ];
            });

        return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
    }

    // ==========================================
    // SECURITY & VIOLATIONS
    // ==========================================

    public function logViolation(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();

        $request->validate([
            'type'   => 'required|string|max:100',
            'detail' => 'nullable|string|max:500',
        ]);

        $session = $quiz->activeSession;
        if ($session) {
            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();

            if ($participant) {
                $count = ($participant->violation_count ?? 0) + 1;
                $participant->update(['violation_count' => $count]);

                $maxViolations = $quiz->violation_limit ?? 3;
                $autoSubmit    = !$quiz->disable_violations && ($count >= $maxViolations);

                if ($autoSubmit) {
                    $participant->update(['status' => 'disqualified']);
                }

                return response()->json([
                    'success'         => true,
                    'violation_count' => $count,
                    'auto_submit'     => $autoSubmit,
                    'message'         => $autoSubmit
                        ? 'Terlalu banyak pelanggaran, quiz otomatis dikumpulkan.'
                        : 'Pelanggaran dicatat (' . $count . '/' . $maxViolations . ')',
                ]);
            }
        }

        return response()->json(['success' => true, 'violation_count' => 1, 'auto_submit' => false]);
    }

    public function reportViolation(Request $request, $quiz)
    {
        return $this->logViolation($request, $quiz);
    }

    public function checkProctoring(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();
        $session = $quiz->activeSession;

        $allow  = true;
        $reason = null;

        if ($session) {
            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();

            if ($participant && in_array($participant->status, ['kicked', 'disqualified'])) {
                $allow  = false;
                $reason = 'Anda tidak diizinkan melanjutkan quiz ini.';
            }
        }

        return response()->json([
            'success'     => true,
            'allow'       => $allow,
            'reason'      => $reason,
            'quiz_status' => $quiz->is_quiz_started ? 'started' : ($quiz->is_room_open ? 'open' : 'closed'),
        ]);
    }

    // ==========================================
    // POWERUPS
    // ==========================================

    public function usePowerup(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();

        if (!$quiz->enable_powerups) {
            return response()->json(['success' => false, 'message' => 'Powerup tidak diaktifkan di quiz ini'], 422);
        }

        $request->validate(['type' => 'required|string|in:skip,fifty_fifty,extra_time,hint']);

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
        }

        $type       = $request->type;
        $questionId = $request->question_id;
        $powerups   = json_decode($attempt->powerups_used ?? '[]', true);

        $used = collect($powerups)->where('type', $type)->where('question_id', $questionId)->count();
        if ($used > 0 && in_array($type, ['fifty_fifty', 'hint'])) {
            return response()->json(['success' => false, 'message' => 'Powerup ini sudah digunakan untuk soal ini'], 422);
        }

        $powerups[] = ['type' => $type, 'question_id' => $questionId, 'at' => now()->toISOString()];
        $attempt->update(['powerups_used' => json_encode($powerups)]);

        $result = ['success' => true, 'type' => $type];

        if ($type === 'fifty_fifty' && $questionId) {
            $question = ExamQuestion::where('id', $questionId)->where('exam_id', $quiz->id)->first();
            if ($question) {
                $choices = is_string($question->choices) ? json_decode($question->choices, true) : ($question->choices ?? []);
                $correct = strtoupper(trim($question->answer ?? $question->correct_answer ?? ''));
                $wrong   = collect($choices)
                    ->filter(fn($c) => strtoupper($c['label'] ?? '') !== $correct)
                    ->shuffle()->take(2)->pluck('label')->toArray();
                $result['eliminate'] = $wrong;
            }
        }

        if ($type === 'extra_time') $result['extra_seconds'] = 30;

        if ($type === 'hint' && $questionId) {
            $question    = ExamQuestion::where('id', $questionId)->where('exam_id', $quiz->id)->first();
            $result['hint'] = $question?->explanation
                ? mb_substr($question->explanation, 0, 100) . '...'
                : 'Tidak ada petunjuk tersedia.';
        }

        return response()->json($result);
    }

    public function claimBonus(Request $request, $quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);
        $student = $this->getStudent();

        $request->validate(['bonus_type' => 'required|string']);

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
        }

        $bonusPoints = match ($request->bonus_type) {
            'streak_3'  => 5,
            'streak_5'  => 10,
            'streak_10' => 25,
            'perfect'   => 50,
            default     => 0,
        };

        if ($bonusPoints > 0) $attempt->increment('bonus_score', $bonusPoints);

        return response()->json([
            'success'      => true,
            'bonus_points' => $bonusPoints,
            'message'      => "Bonus +{$bonusPoints} poin diterima!",
        ]);
    }

    /**
     * Helper: perkaya data quiz dengan status user
     */
    private function enrichQuizData($quiz, $userId)
    {
        $now = now();
        $timeStatus = 'available';

        if ($quiz->start_at && $now < $quiz->start_at) {
            $timeStatus = 'upcoming';
        } elseif ($quiz->end_at && $now > $quiz->end_at) {
            $timeStatus = 'finished';
        }

        $quiz->time_status = $timeStatus;

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->latest()
            ->first();

        $quiz->attempt_count = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        $displayStatus = 'available';

        if ($quiz->time_status === 'finished') {
            $displayStatus = 'finished';
        } elseif ($quiz->time_status === 'upcoming') {
            $displayStatus = 'upcoming';
        } elseif ($attempt) {
            if ($attempt->status === 'submitted' || $attempt->status === 'timeout') {
                $displayStatus = 'completed';
            } elseif ($attempt->status === 'in_progress') {
                $displayStatus = 'ongoing';
            }
        }

        $quiz->display_status = $displayStatus;
        $quiz->last_attempt = $attempt;
        $quiz->questions_count = $quiz->questions->count();

        $quiz->can_retake = $quiz->enable_retake;
        if ($quiz->limit_attempts > 0 && $quiz->attempt_count >= $quiz->limit_attempts) {
            $quiz->can_retake = false;
        }

        $quiz->room_status = $quiz->is_room_open ? ($quiz->is_quiz_started ? 'started' : 'open') : 'closed';
    }

    /**
     * Helper: ambil leaderboard quiz
     */
    private function getQuizLeaderboard($quiz)
    {
        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->where('status', 'submitted')
            ->with('student')
            ->orderBy('final_score', 'desc')
            ->orderBy('ended_at', 'asc')
            ->limit(10)
            ->get();

        $leaderboard = [];
        $position = 1;

        foreach ($attempts as $attempt) {
            $leaderboard[] = [
                'position' => $position++,
                'student_id' => $attempt->student_id,
                'student_name' => $attempt->student->name ?? 'Unknown',
                'score' => $attempt->final_score,
                'time_taken' => $attempt->ended_at ? $attempt->started_at->diffInSeconds($attempt->ended_at) : 0,
                'attempt_id' => $attempt->id,
            ];
        }

        return $leaderboard;
    }

    /**
     * Force submit karena timeout
     */
    private function forceSubmitDueToTimeout($attempt)
    {
        try {
            DB::beginTransaction();

            $attempt->status = 'timeout';
            $attempt->ended_at = now();
            $attempt->save();

            $quiz = Exam::find($attempt->exam_id);
            if ($quiz && $quiz->type === 'QUIZ') {
                $session = $quiz->activeSession;
                if ($session) {
                    $participant = QuizParticipant::where([
                        'quiz_session_id' => $session->id,
                        'student_id' => $attempt->student_id
                    ])->first();

                    if ($participant) {
                        $participant->update([
                            'status' => 'submitted',
                            'submitted_at' => now(),
                        ]);

                        $session->updateStats();
                    }
                }
            }

            session()->forget([
                'current_quiz_attempt_' . $attempt->exam_id,
                'quiz_started_' . $attempt->exam_id,
                'quiz_mode',
                'difficulty_level',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in forceSubmitDueToTimeout: ' . $e->getMessage());
        }
    }
}
