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
        $userId  = $this->getStudentUserId();

        $quizzes = $this->quizQuery()
            ->whereIn('status', ['active', 'finished'])
            ->with(['subject', 'class', 'questions'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Enrich each quiz with display status and user-specific data
        foreach ($quizzes as $quiz) {
            $this->enrichQuizData($quiz, $userId);
        }

        $myAttempts = ExamAttempt::where('student_id', $userId)
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

    // ==========================================
    // DETAIL PAGE (mandiri / all modes)
    // ==========================================

    /**
     * Halaman detail quiz sebelum dikerjakan.
     * - Mode mandiri/homework: langsung muncul tombol mulai/lanjut
     * - Mode live: tetap tampilkan status ruangan & arahkan ke room
     */
    public function quizDetail($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->with(['subject', 'class', 'questions'])
            ->withCount('questions')
            ->findOrFail($quiz);

        if ($quiz->status === 'draft') {
            return redirect()->route('quiz.index')
                ->with('error', 'Quiz ini belum tersedia.');
        }

        $student     = $this->getStudent();
        $userId      = $this->getStudentUserId();

        // Attempt terakhir
        $lastAttempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->latest()
            ->first();

        $attemptCount = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        $canRetake = (bool) $quiz->enable_retake &&
            ($quiz->limit_attempts <= 0 || $attemptCount < $quiz->limit_attempts);

        // Leaderboard
        $showLeaderboard = (bool) ($quiz->show_leaderboard ?? false);
        $leaderboard     = $showLeaderboard ? collect($this->getQuizLeaderboard($quiz)) : collect();

        // Statistik ruangan (jika live)
        $stats = ['total' => 0, 'joined' => 0, 'submitted' => 0];
        if ($quiz->quiz_mode !== 'homework') {
            $session = $quiz->activeSession;
            if ($session) {
                $stats['total']     = optional($quiz->class)->students()->count() ?? 0;
                $stats['joined']    = QuizParticipant::where('quiz_session_id', $session->id)->count();
                $stats['submitted'] = QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('status', 'submitted')->count();
            }
        }

        // Fitur quiz
        $quizFeatures = [
            'enable_music'    => (bool) ($quiz->enable_music    ?? false),
            'enable_memes'    => (bool) ($quiz->enable_memes    ?? false),
            'enable_powerups' => (bool) ($quiz->enable_powerups ?? false),
            'instant_feedback' => (bool) ($quiz->instant_feedback ?? false),
            'streak_bonus'    => (bool) ($quiz->streak_bonus    ?? false),
            'time_bonus'      => (bool) ($quiz->time_bonus      ?? false),
        ];

        $duration        = $quiz->duration ?? 0;
        $timePerQuestion = $quiz->time_per_question ?? 0;

        return view('murid.quiz.detail', compact(
            'quiz',
            'lastAttempt',
            'attemptCount',
            'canRetake',
            'showLeaderboard',
            'leaderboard',
            'stats',
            'quizFeatures',
            'duration',
            'timePerQuestion'
        ));
    }

    /**
     * Show the waiting room page for student
     * Pakai ID manual, bukan route model binding — untuk bypass scope
     */
    public function joinQuizRoomPage($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->findOrFail($quiz);

        // Mode mandiri/homework: redirect ke detail page, tidak perlu room
        if ($quiz->quiz_mode === 'homework') {
            return redirect()->route('quiz.detail', $quiz->id);
        }

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
            'success'          => true,
            'message'          => 'Berhasil bergabung ke room quiz!',
            'participant_id'   => $participant->id,
            'status'           => $participant->status,
            'participant_status' => $participant->status,
            'quiz_status'      => $quiz->room_status ?? 'open',
        ]);
    }

    /**
     * Get current room status (polling endpoint for murid)
     * Returns full participant list and stats for room.blade.php compatibility
     */
    public function getQuizRoomStatus($quiz)
    {
        $quiz    = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        // ── Auto-close jika durasi sudah habis ───────────────────────────────
        $wasStarted  = (bool) $quiz->is_quiz_started;
        $justExpired = false;
        if ($wasStarted) {
            $justExpired = $quiz->autoCloseIfExpired();
            if ($justExpired) $quiz->refresh();
        }
        // ────────────────────────────────────────────────────────────────────

        $session = $quiz->activeSession;

        $participantStatus = 'not_joined';
        $myParticipant     = null;

        // Build participants list
        $participants = [];
        $stats = [
            'total'     => optional($quiz->class)->students()->count() ?? 0,
            'joined'    => 0,
            'ready'     => 0,
            'started'   => 0,
            'submitted' => 0,
        ];

        if ($session) {
            $allParticipants = QuizParticipant::where('quiz_session_id', $session->id)
                ->with('student')
                ->get();

            foreach ($allParticipants as $p) {
                $name = $p->student->name ?? 'Unknown';
                $vc   = (int) ($p->violation_count ?? 0);

                // Buat array violations dari violation_log
                $logs       = $p->violation_log ?? [];  // sudah di-cast ke array oleh model
                $violations = array_values(array_filter(array_map(function ($item) {
                    // Hanya sertakan entri pelanggaran siswa (bukan teacher_warning)
                    if (($item['type'] ?? '') === 'teacher_warning') return null;
                    return [
                        'type'      => $item['type']      ?? 'unknown',
                        'details'   => $item['details']   ?? $item['detail'] ?? '',
                        'timestamp' => $item['timestamp'] ?? '',
                    ];
                }, (array) $logs)));

                $participants[] = [
                    'id'              => $p->id,
                    'student_id'      => $p->student_id,
                    'student_name'    => $name,
                    'student_email'   => $p->student->email ?? '',
                    'status'          => $p->status,
                    'joined_at'       => optional($p->joined_at)->format('H:i'),
                    'violation_count' => $vc,
                    'violations'      => $violations,  // detail pelanggaran untuk modal guru
                    'has_violation'   => $vc > 0,
                ];

                // Count stats
                if ($p->status === 'ready')     $stats['ready']++;
                if ($p->status === 'started')   $stats['started']++;
                if ($p->status === 'submitted') $stats['submitted']++;
                $stats['joined']++;

                // Identify current user's participant
                if ($p->student_id === Auth::id()) {
                    $myParticipant     = $p;
                    $participantStatus = $p->status;
                }
            }

            if (!$myParticipant) {
                $participantStatus = 'not_joined';
            }
        }

        // ── Hitung time_remaining real-time dari quiz_started_at ─────────────
        // TIDAK pakai getQuizTimeRemaining() dari model karena bisa return nilai stale
        $timeRemaining = null;
        if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
            $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
            $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
        } elseif (!$quiz->is_quiz_started && $quiz->duration > 0) {
            // Quiz belum mulai — tampilkan durasi penuh (hanya info)
            $timeRemaining = $quiz->duration * 60;
        }
        // ─────────────────────────────────────────────────────────────────────

        return response()->json([
            'success'          => true,
            'is_room_open'     => (bool) $quiz->is_room_open,
            'is_quiz_started'  => (bool) $quiz->is_quiz_started,
            'quiz_status'      => $quiz->status,
            'quiz_expired'     => $justExpired,
            'room_status'      => $quiz->room_status ?? ($quiz->is_room_open ? 'open' : 'closed'),
            'my_status'        => $participantStatus,
            'participant'      => $myParticipant ? [
                'id'     => $myParticipant->id,
                'status' => $participantStatus,
            ] : null,
            'participants'     => $participants,
            'stats'            => $stats,
            'session_code'     => $session?->session_code,
            'time_remaining'   => $timeRemaining,   // ← real-time, tidak pernah naik
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

    /**
     * Endpoint polling untuk play_simple — siswa cek sisa waktu dari server secara berkala.
     * Digunakan untuk sinkronisasi timer durasi agar tidak drift.
     * Route: GET /quiz/{quiz}/status
     */
    public function checkQuizStatus($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        // Auto-close jika sudah expired
        if ($quiz->is_quiz_started) {
            $expired = $quiz->autoCloseIfExpired();
            if ($expired) $quiz->refresh();
        }

        // ── Hitung time_remaining real-time ──────────────────────────────────
        // Untuk mode live/guided: dari quiz_started_at (berlaku untuk semua siswa)
        // Untuk mode homework: dari attempt->started_at siswa masing-masing
        $timeRemaining = null;
        $quizExpired   = false;

        if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
            // Live/Guided — satu timer untuk semua
            $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
            $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            $quizExpired   = ($timeRemaining <= 0);
        } elseif ($quiz->quiz_mode === 'homework' && $quiz->duration > 0) {
            // Homework — timer per siswa dari attempt->started_at
            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', Auth::id())
                ->whereNull('submitted_at')
                ->latest()
                ->first();

            if ($attempt && $attempt->started_at) {
                $elapsed       = now()->diffInSeconds($attempt->started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
                $quizExpired   = ($timeRemaining <= 0);
            }
        }

        // Jika expired flag dari auto-close juga benar
        if ($quiz->status === 'finished' && !$quiz->is_quiz_started) {
            $quizExpired = true;
        }

        return response()->json([
            'success'         => true,
            'is_quiz_started' => (bool) $quiz->is_quiz_started,
            'is_room_open'    => (bool) $quiz->is_room_open,
            'quiz_status'     => $quiz->status,
            'quiz_expired'    => $quizExpired,
            'time_remaining'  => $timeRemaining,  // real-time, tidak pernah naik
        ]);
    }

    // ==========================================
    // PLAY QUIZ
    // ==========================================

    public function playQuiz($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->with('questions')
            ->findOrFail($quiz);

        $student = $this->getStudent();
        $isMandiri = $quiz->quiz_mode === 'homework';

        // Validasi akses
        if ($isMandiri) {
            if ($quiz->status !== 'active' && $quiz->status !== 'finished') {
                return redirect()->route('quiz.detail', $quiz->id)
                    ->with('error', 'Quiz belum tersedia.');
            }
        } else {
            if (!$quiz->is_quiz_started && !$quiz->is_room_open) {
                return redirect()->route('quiz.room', $quiz->id)
                    ->with('error', 'Quiz belum dimulai.');
            }
        }

        $session = $quiz->activeSession;
        $participant = null;

        if (!$isMandiri && $session) {
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

            // Update status menjadi started (untuk live/guided)
            if ($participant->status !== 'started') {
                $participant->update(['status' => 'started', 'started_at' => now()]);
            }
        }

        // ── Buat atau ambil attempt yang aktif ──────────────────────────────
        // firstOrCreate: pastikan quiz_session_id dan remaining_time juga tersimpan
        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('submitted_at')
            ->latest()
            ->first();

        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'exam_id'          => $quiz->id,
                'student_id'       => Auth::id(),
                'quiz_session_id'  => $session?->id,
                'status'           => 'in_progress',
                'started_at'       => now(),
                'remaining_time'   => $quiz->duration > 0 ? $quiz->duration * 60 : null,
            ]);
        } elseif (!$attempt->quiz_session_id && $session) {
            // Update session_id jika belum ada
            $attempt->update(['quiz_session_id' => $session->id]);
        }
        // ────────────────────────────────────────────────────────────────────

        if (in_array($attempt->status, ['submitted', 'timeout'])) {
            return redirect()->route('quiz.result', [$quiz->id, $attempt->id])
                ->with('info', 'Anda sudah menyelesaikan quiz ini.');
        }

        // ========== GUIDED MODE ==========
        if ($quiz->quiz_mode === 'guided') {
            $totalQuestions = $quiz->questions()->count();
            $sessionCode = $session ? $session->session_code : null;

            return view('murid.quiz.play-guide', compact(
                'quiz',
                'attempt',
                'totalQuestions',
                'sessionCode',
                'session',
                'participant'
            ));
        }

        // ========== MODE LAIN (LIVE / HOMEWORK) ==========
        $questions = $quiz->questions()->orderBy('order')->get();
        if ($quiz->shuffle_question) {
            $questions = $questions->shuffle();
        }

        $savedAnswers = ExamAnswer::where('attempt_id', $attempt->id)
            ->pluck('answer', 'question_id')
            ->toArray();

        // ── Hitung sisa waktu REAL-TIME dari server ──────────────────────────
        // Penting: TIDAK boleh pakai attempt->remaining_time dari DB (stale).
        // Selalu hitung ulang dari timestamp mulai agar timer tidak pernah naik.
        $quizTimeRemaining = 0; // default = tanpa batas

        if ($quiz->duration > 0) {
            if ($quiz->quiz_mode === 'homework') {
                // Homework: timer per-siswa dihitung dari attempt->started_at
                $elapsed           = now()->diffInSeconds($attempt->started_at ?? now());
                $quizTimeRemaining = max(0, ($quiz->duration * 60) - $elapsed);

                // Sudah timeout sebelum halaman terbuka → force submit & redirect
                if ($quizTimeRemaining <= 0) {
                    $attempt->update([
                        'status'       => 'timeout',
                        'ended_at'     => now(),
                        'submitted_at' => now(),
                    ]);
                    return redirect()->route('quiz.result', [$quiz->id, $attempt->id])
                        ->with('info', 'Waktu quiz telah habis.');
                }
            } else {
                // Live/Guided: timer global dari quiz_started_at (sama untuk semua)
                if ($quiz->quiz_started_at && $quiz->is_quiz_started) {
                    $elapsed           = now()->diffInSeconds($quiz->quiz_started_at);
                    $quizTimeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
                } else {
                    $quizTimeRemaining = $quiz->duration * 60;
                }
            }
        }
        // ────────────────────────────────────────────────────────────────────

        return view('murid.quiz.play_simple', compact(
            'quiz',
            'attempt',
            'questions',
            'savedAnswers',
            'session',
            'participant',
            'quizTimeRemaining'
        ));
    }

    /**
     * Save progress (POST) — menyimpan semua jawaban + posisi soal + skor
     * Dipanggil otomatis setiap jawab soal / pindah soal oleh play_simple.
     * Body: { answers:[{question_id,choice_id,text_answer}], current_question, total_score, streak_count, time_remaining }
     *
     * GET versi endpoint yang sama → kembalikan progress tersimpan untuk reload saat refresh.
     */
    public function saveQuizProgress(Request $request, $quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', Auth::id())
            ->whereNull('submitted_at')
            ->latest()
            ->first();

        // ── GET: kembalikan progress tersimpan ───────────────────────────────
        if ($request->isMethod('GET')) {
            if (!$attempt) {
                return response()->json(['success' => false, 'progress' => null]);
            }

            $savedAnswers = ExamAnswer::where('attempt_id', $attempt->id)
                ->get()
                ->map(fn($a) => [
                    'question_id' => $a->question_id,
                    'choice_id'   => $a->choice_id,
                    'text_answer' => $a->answer_text ?? $a->answer,
                ]);

            // Hitung time_remaining real-time dari server
            $timeRemaining = null;
            if ($quiz->duration > 0) {
                if ($quiz->quiz_mode === 'homework') {
                    $elapsed       = now()->diffInSeconds($attempt->started_at ?? now());
                    $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
                } elseif ($quiz->quiz_started_at && $quiz->is_quiz_started) {
                    $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
                    $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
                }
            }

            return response()->json([
                'success'  => true,
                'progress' => [
                    'answers'          => $savedAnswers,
                    'current_question' => (int) ($attempt->current_question ?? 0),
                    'total_score'      => (float) ($attempt->score ?? 0),
                    'streak_count'     => (int) ($attempt->streak_count ?? 0),
                    'time_remaining'   => $timeRemaining,
                ],
            ]);
        }

        // ── POST: simpan semua jawaban + state ───────────────────────────────
        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
        }

        // Validasi ringan — answers boleh kosong (hanya state update)
        $answers         = $request->input('answers', []);
        $currentQuestion = $request->input('current_question');
        $totalScore      = $request->input('total_score');
        $streakCount     = $request->input('streak_count');

        // Simpan setiap jawaban ke exam_answers (upsert per question)
        foreach ((array) $answers as $ans) {
            if (empty($ans['question_id'])) continue;

            $qId      = (int) $ans['question_id'];
            $choiceId = !empty($ans['choice_id']) ? (int) $ans['choice_id'] : null;
            $text     = isset($ans['text_answer']) ? (string) $ans['text_answer'] : null;

            // Hanya simpan jika ada jawaban (choice atau teks)
            if ($choiceId === null && ($text === null || $text === '')) continue;

            // Pastikan soal milik quiz ini
            $question = ExamQuestion::where('id', $qId)->where('exam_id', $quiz->id)->first();
            if (!$question) continue;

            $answerLabel = null;
            if ($choiceId) {
                $choice      = ExamChoice::find($choiceId);
                $answerLabel = $choice ? (string) $choice->label : null;
            }

            ExamAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $qId],
                [
                    'exam_id'     => $quiz->id,
                    'student_id'  => Auth::id(),
                    'choice_id'   => $choiceId,
                    'answer'      => $answerLabel ?? $text,
                    'answer_text' => $text,
                    'answered_at' => now(),
                ]
            );
        }

        // Hitung time_remaining real-time
        $timeRemaining = null;
        if ($quiz->duration > 0) {
            if ($quiz->quiz_mode === 'homework') {
                $elapsed       = now()->diffInSeconds($attempt->started_at ?? now());
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            } elseif ($quiz->quiz_started_at && $quiz->is_quiz_started) {
                $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            }
        }

        // Update state di attempt
        $updateData = ['remaining_time' => $timeRemaining];
        if ($currentQuestion !== null) $updateData['current_question'] = (int) $currentQuestion;
        if ($totalScore      !== null) $updateData['score']            = (float) $totalScore;
        if ($streakCount     !== null) $updateData['streak_count']     = (int) $streakCount;

        $attempt->update($updateData);

        return response()->json([
            'success'        => true,
            'message'        => 'Progress tersimpan',
            'time_remaining' => $timeRemaining,
        ]);
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
            'type'    => 'required|string|max:100',
            'detail'  => 'nullable|string|max:500',   // singular (lama)
            'details' => 'nullable|string|max:500',   // plural (dari JS frontend)
        ]);

        // Frontend mengirim 'details' (plural), support keduanya
        $detailText = $request->input('details') ?? $request->input('detail') ?? '';

        $maxViolations = (int) ($quiz->violation_limit ?? 3);
        $autoSubmit    = false;

        // ── MODE MANDIRI (homework) — tidak ada QuizParticipant ──────────────
        // Simpan pelanggaran langsung ke ExamAttempt
        if ($quiz->quiz_mode === 'homework') {
            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', Auth::id())
                ->whereNull('submitted_at')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
            }

            // Simpan log pelanggaran di kolom exam_settings (JSON)
            $settings = is_array($attempt->exam_settings) ? $attempt->exam_settings : [];
            $vLogs    = $settings['violation_log'] ?? [];
            $vLogs[]  = [
                'type'      => $request->input('type'),
                'details'   => $detailText,
                'timestamp' => now()->toISOString(),
            ];
            $count = count($vLogs);

            $settings['violation_log']   = $vLogs;
            $settings['violation_count'] = $count;
            $attempt->update(['exam_settings' => $settings]);

            // Auto-submit jika pelanggaran diaktifkan dan batas terlampaui
            $autoSubmit = !$quiz->disable_violations && $maxViolations > 0 && ($count >= $maxViolations);

            if ($autoSubmit) {
                // Tandai attempt sebagai disqualified (akan diproses submitQuiz dari frontend)
                $attempt->update(['status' => 'disqualified']);
            }

            return response()->json([
                'success'         => true,
                'violation_count' => $count,
                'max_violations'  => $maxViolations,
                'auto_submit'     => $autoSubmit,
                'message'         => $autoSubmit
                    ? 'Terlalu banyak pelanggaran, quiz otomatis dikumpulkan.'
                    : 'Pelanggaran dicatat (' . $count . '/' . $maxViolations . ')',
            ]);
        }

        // ── MODE LIVE / GUIDED — pakai QuizParticipant ───────────────────────
        $session = $quiz->activeSession;
        if ($session) {
            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', Auth::id())
                ->first();

            if ($participant) {
                // Ambil log pelanggaran lama, tambahkan entri baru
                $logs   = is_array($participant->violation_log) ? $participant->violation_log : [];
                $logs[] = [
                    'type'      => $request->input('type'),
                    'details'   => $detailText,
                    'timestamp' => now()->toISOString(),
                ];

                $count = ($participant->violation_count ?? 0) + 1;

                $participant->update([
                    'violation_count' => $count,
                    'violation_log'   => $logs,
                ]);

                $autoSubmit = !$quiz->disable_violations && $maxViolations > 0 && ($count >= $maxViolations);

                if ($autoSubmit) {
                    $participant->update(['status' => 'disqualified']);
                }

                return response()->json([
                    'success'         => true,
                    'violation_count' => $count,
                    'max_violations'  => $maxViolations,
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

    /**
     * Guru mengubah waktu per soal secara live + reset deadline soal aktif
     * Route: POST /guru/quiz/{quiz}/room/guided/set-time
     */
    public function guidedSetTime(Request $request, $quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        $request->validate([
            'time_per_question' => 'required|integer|min:0|max:300',
        ]);

        $seconds = (int) $request->input('time_per_question');

        // Hitung deadline baru dari sekarang (hanya jika timer aktif)
        $deadline = $seconds > 0 ? now()->addSeconds($seconds)->timestamp : null;

        $quiz->update([
            'time_per_question'       => $seconds,
            'guided_question_deadline' => $deadline,
            'guided_show_answer'       => false, // reset fase jawaban saat timer diubah
        ]);

        return response()->json([
            'success'           => true,
            'time_per_question' => $seconds,
            'question_deadline' => $deadline,
            'message'           => $seconds > 0
                ? "Timer {$seconds} detik/soal aktif!"
                : 'Mode manual aktif (tanpa timer)',
        ]);
    }

    public function guidedCurrentQuestion($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        if ($quiz->quiz_mode !== 'guided') {
            return response()->json(['error' => 'Bukan mode Quiz Terpandu'], 422);
        }

        if (!$quiz->is_quiz_started) {
            return response()->json(['waiting' => true, 'message' => 'Menunggu guru memulai quiz...']);
        }

        $currentIndex    = (int) ($quiz->guided_current_index ?? 0);
        $totalQuestions  = $quiz->questions()->count();
        $currentQuestion = $quiz->questions()->with('choices')->orderBy('order')->skip($currentIndex)->first();
        $isLastQuestion  = ($currentIndex >= $totalQuestions - 1);

        return response()->json([
            'success'           => true,
            'current_index'     => $currentIndex,
            'total'             => $totalQuestions,
            'is_last_question'  => $isLastQuestion,
            'time_per_question' => (int) ($quiz->time_per_question ?? 0),
            'question_deadline' => $quiz->guided_question_deadline ?? null,
            'show_answer'       => (bool) ($quiz->guided_show_answer ?? false),
            'question' => $currentQuestion ? [
                'id'      => $currentQuestion->id,
                'question'=> $currentQuestion->question,
                'type'    => $currentQuestion->type,
                'score'   => $currentQuestion->score,
                'choices' => $currentQuestion->choices->map(fn($c) => [
                    'id'    => $c->id,
                    'label' => $c->label,
                    'text'  => $c->text,
                ])->values(),
            ] : null,
        ]);
    }

    /**
     * Guru mengirim peringatan ke siswa yang melanggar
     * Route: POST /guru/quiz/{quiz}/room/warn/{participant}
     */
    public function warnParticipant(Request $request, $quiz, $participantId)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        // Pastikan yang memanggil adalah guru
        if (!auth()->user()->hasRole('Guru')) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:300',
        ]);

        $session = $quiz->activeSession;
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak aktif'], 422);
        }

        $participant = QuizParticipant::where('id', $participantId)
            ->where('quiz_session_id', $session->id)
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
        }

        $message = $request->input('message') ?: 'Guru mengingatkanmu untuk tidak melakukan kecurangan selama quiz berlangsung!';

        // Simpan warning ke dalam JSON field di participant (atau cache)
        $warnings = json_decode($participant->warnings ?? '[]', true) ?: [];
        $warnings[] = [
            'id'         => uniqid('warn_'),
            'message'    => $message,
            'sent_at'    => now()->toISOString(),
            'seen'       => false,
        ];
        // Simpan hanya 5 warning terakhir
        $warnings = array_slice($warnings, -5);
        $participant->update(['warnings' => json_encode($warnings)]);

        return response()->json([
            'success' => true,
            'message' => 'Peringatan berhasil dikirim ke ' . ($participant->student_name ?? 'siswa'),
        ]);
    }

    /**
     * Siswa cek apakah ada peringatan baru dari guru
     * Route: GET /quiz/{quiz}/room/check-warning
     */
    public function checkWarning($quiz)
    {
        $quiz = Exam::withoutGlobalScopes()->where('type', 'QUIZ')->findOrFail($quiz);

        $session = $quiz->activeSession;
        if (!$session) {
            return response()->json(['warning' => null]);
        }

        $participant = QuizParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', Auth::id())
            ->first();

        if (!$participant || !$participant->warnings) {
            return response()->json(['warning' => null]);
        }

        $warnings = json_decode($participant->warnings, true) ?: [];

        // Cari warning yang belum dilihat
        $unseen = collect($warnings)->where('seen', false)->last();

        if ($unseen) {
            // Tandai semua sebagai sudah dilihat
            $updated = collect($warnings)->map(function ($w) {
                $w['seen'] = true;
                return $w;
            })->toArray();
            $participant->update(['warnings' => json_encode($updated)]);

            return response()->json(['warning' => $unseen]);
        }

        return response()->json(['warning' => null]);
    }
}
