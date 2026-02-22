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
    /**
     * Display a listing of quizzes (interactive quizzes)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return view('murid.quiz.index', [
                    'quizzes' => collect(),
                    'error' => 'Data siswa tidak ditemukan.'
                ]);
            }

            if (!$student->class_id) {
                return view('murid.quiz.index', [
                    'quizzes' => collect(),
                    'error' => 'Anda belum memiliki kelas.'
                ]);
            }

            $classId = $student->class_id;
            $search = $request->input('search');
            $status = $request->input('status', 'all');

            $query = Exam::with(['subject', 'teacher.user', 'questions'])
                ->where('class_id', $classId)
                ->where('type', 'QUIZ')
                ->where('status', 'active');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('difficulty_level', 'like', '%' . $search . '%')
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name_subject', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $quizzes = $query->orderBy('created_at', 'desc')->paginate(12);

            foreach ($quizzes as $quiz) {
                $this->enrichQuizData($quiz, $user->id);
            }

            if ($status && $status !== 'all') {
                $filteredQuizzes = $quizzes->filter(function ($quiz) use ($status) {
                    return $quiz->display_status === $status;
                });

                $page = $request->input('page', 1);
                $perPage = 12;
                $paginatedQuizzes = new \Illuminate\Pagination\LengthAwarePaginator(
                    $filteredQuizzes->forPage($page, $perPage),
                    $filteredQuizzes->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                $quizzes = $paginatedQuizzes;
            }

            return view('murid.quiz.index', compact('quizzes'));
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            return view('murid.quiz.index', [
                'quizzes' => collect(),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show active quizzes
     */
    public function activeQuiz()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('murid.quiz.active', [
                'quizzes' => collect(),
                'error' => 'Data siswa tidak ditemukan.'
            ]);
        }

        $quizzes = Exam::where('class_id', $student->class_id)
            ->where('type', 'QUIZ')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with(['subject', 'class'])
            ->orderBy('start_at', 'asc')
            ->paginate(10);

        foreach ($quizzes as $quiz) {
            $this->enrichQuizData($quiz, $user->id);
        }

        return view('murid.quiz.active', compact('quizzes'));
    }

    /**
     * Show upcoming quizzes
     */
    public function upcomingQuiz()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('murid.quiz.upcoming', [
                'quizzes' => collect(),
                'error' => 'Data siswa tidak ditemukan.'
            ]);
        }

        $quizzes = Exam::where('class_id', $student->class_id)
            ->where('type', 'QUIZ')
            ->where('status', 'active')
            ->where('start_at', '>', now())
            ->with(['subject', 'class'])
            ->orderBy('start_at', 'asc')
            ->paginate(10);

        foreach ($quizzes as $quiz) {
            $this->enrichQuizData($quiz, $user->id);
        }

        return view('murid.quiz.upcoming', compact('quizzes'));
    }

    /**
     * Show completed quizzes
     */
    public function completedQuiz()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('murid.quiz.completed', [
                'quizzes' => collect(),
                'error' => 'Data siswa tidak ditemukan.'
            ]);
        }

        $quizzes = Exam::where('class_id', $student->class_id)
            ->where('type', 'QUIZ')
            ->where(function ($query) {
                $query->where('status', 'finished')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                            ->whereNotNull('end_at')
                            ->where('end_at', '<', now());
                    });
            })
            ->with(['subject', 'class'])
            ->orderBy('end_at', 'desc')
            ->paginate(10);

        foreach ($quizzes as $quiz) {
            $this->enrichQuizData($quiz, $user->id);
        }

        return view('murid.quiz.completed', compact('quizzes'));
    }

    /**
     * GET /quiz/{quiz}/room – halaman join ruang quiz
     */
    public function joinQuizRoomPage($quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with([
                'activeSession.participants' => function ($query) use ($user) {
                    $query->where('student_id', $user->id);
                }
            ])->findOrFail($quizId);

            if ($quiz->type !== 'QUIZ') {
                abort(404, 'Bukan quiz');
            }

            $participant = null;
            if ($quiz->activeSession) {
                $participant = $quiz->activeSession->participants->first();
            }

            return view('quiz.room', compact('quiz', 'participant'));
        } catch (\Exception $e) {
            Log::error('Error in joinQuizRoomPage: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Terjadi kesalahan');
        }
    }

    /**
     * POST /quiz/{quiz}/room/join – ajax join ruang quiz
     */
    public function joinQuizRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if (!$quiz->is_room_open) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan belum dibuka'
                ], 422);
            }

            if ($quiz->is_quiz_started) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz sudah dimulai'
                ], 422);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan'
                ], 422);
            }

            $participant = QuizParticipant::where([
                'quiz_session_id' => $session->id,
                'student_id' => $user->id
            ])->first();

            if ($participant) {
                $participant->update([
                    'is_present' => true,
                    'status' => 'waiting',
                    'joined_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ]);
            } else {
                $participant = QuizParticipant::create([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id,
                    'exam_id' => $quiz->id,
                    'status' => 'waiting',
                    'joined_at' => now(),
                    'is_present' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'violation_count' => 0,
                ]);
            }

            $session->updateStats();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil bergabung ke ruangan!',
                'participant_status' => 'waiting'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in joinQuizRoom: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * GET /quiz/{quiz}/room/status – cek status ruang (ajax)
     */
    public function getQuizRoomStatus($quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with(['activeSession', 'class'])->findOrFail($quizId);

            $session = $quiz->activeSession;
            $participant = null;
            $stats = null;
            $participants = [];

            if ($session) {
                $participant = QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('student_id', $user->id)
                    ->first();

                $allParticipants = QuizParticipant::where('quiz_session_id', $session->id)
                    ->with(['student:id,name,email'])
                    ->where('is_present', true)
                    ->orderBy('joined_at', 'asc')
                    ->get();

                $participants = $allParticipants->map(function ($p) {
                    $attempt = ExamAttempt::where('exam_id', $p->exam_id)
                        ->where('student_id', $p->student_id)
                        ->where('status', 'in_progress')
                        ->first();
                    $violationCount = $attempt ? (int) ($attempt->violation_count ?? 0) : 0;

                    return [
                        'id'               => $p->id,
                        'student_id'       => $p->student_id,
                        'name'             => $p->student->name ?? 'Unknown',
                        'student_name'     => $p->student->name ?? 'Unknown',
                        'email'            => $p->student->email ?? '',
                        'student_email'    => $p->student->email ?? '',
                        'status'           => $p->status,
                        'joined_time'      => $p->joined_at ? $p->joined_at->format('H:i') : '-',
                        'joined_at'        => $p->joined_at ? $p->joined_at->format('H:i') : '-',
                        'initial'          => strtoupper(($p->student->name ?? '?')[0] ?? '?'),
                        'violation_count'  => $violationCount,
                        'has_violation'    => $violationCount > 0,
                    ];
                })->values()->toArray();

                $stats = [
                    'total_students' => $quiz->class ? $quiz->class->students()->count() : 0,
                    'joined'         => $allParticipants->count(),
                    'ready'          => $allParticipants->where('status', 'ready')->count(),
                    'started'        => $allParticipants->where('status', 'started')->count(),
                    'submitted'      => $allParticipants->where('status', 'submitted')->count(),
                ];

                // Log untuk debugging
                Log::info('Room Stats', [
                    'quiz_id' => $quiz->id,
                    'total_joined' => $allParticipants->count(),
                    'submitted' => $stats['submitted'],
                ]);
            }

            $timeRemaining = 0;
            if ($session && $quiz->is_quiz_started) {
                $startedAt = $quiz->quiz_started_at ?? now();
                $elapsed = (int) now()->diffInSeconds($startedAt);
                $totalSeconds = (int) ($quiz->duration * 60);
                $timeRemaining = max(0, $totalSeconds - $elapsed);
            } else {
                $timeRemaining = $quiz->duration * 60;
            }

            return response()->json([
                'success'          => true,
                'is_room_open'     => (bool) $quiz->is_room_open,
                'is_quiz_started'  => (bool) $quiz->is_quiz_started,
                'participant'      => $participant ? [
                    'id'     => $participant->id,
                    'status' => $participant->status
                ] : null,
                'stats'           => $stats ?: [
                    'total_students' => 0,
                    'joined'   => 0,
                    'ready'    => 0,
                    'started'  => 0,
                    'submitted' => 0,
                ],
                'participants'    => $participants,
                'time_remaining'  => $timeRemaining,
                'should_redirect' => $participant && $participant->status === 'started' && $quiz->is_quiz_started,
                'show_leaderboard' => (bool) ($quiz->show_leaderboard ?? false),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting quiz room status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /quiz/{quiz}/room/mark-ready – tandai siap
     */
    public function markAsReady(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan'
                ], 422);
            }

            $participant = QuizParticipant::where([
                'quiz_session_id' => $session->id,
                'student_id' => $user->id
            ])->first();

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum bergabung'
                ], 422);
            }

            $participant->update([
                'status' => 'ready',
                'ready_at' => now()
            ]);

            $session->updateStats();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah menjadi siap!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in markAsReady: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    // =========================================================================
    // ✅ FIX: Pengecekan retake & batas percobaan di playQuiz()
    // =========================================================================
    /**
     * GET /quiz/{quiz}/play – halaman mengerjakan quiz (dengan pengacakan)
     */
    public function playQuiz($quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with('questions.choices')->findOrFail($quizId);

            // Cek akses (kelas, role, dll)
            if (!$this->checkQuizAccess($quiz, $user)) {
                abort(403, 'Anda tidak memiliki akses ke quiz ini.');
            }

            // =================================================================
            // ✅ CEK APAKAH SUDAH PERNAH MENGERJAKAN DAN TIDAK BOLEH MENGULANG
            // =================================================================
            $attemptCount = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            if ($attemptCount > 0) {
                // Jika quiz tidak mengizinkan pengulangan
                if (!$quiz->enable_retake) {
                    $lastAttempt = ExamAttempt::where('exam_id', $quiz->id)
                        ->where('student_id', $user->id)
                        ->whereIn('status', ['submitted', 'timeout'])
                        ->latest()
                        ->first();

                    if ($lastAttempt) {
                        return redirect()->route('quiz.result', [
                            'quiz' => $quiz->id,
                            'attempt' => $lastAttempt->id
                        ])->with('info', 'Anda sudah mengerjakan quiz ini dan tidak dapat mengulang.');
                    }

                    return redirect()->route('quiz.index')
                        ->with('error', 'Anda sudah mengerjakan quiz ini dan tidak dapat mengulang.');
                }

                // Jika ada batasan jumlah percobaan dan sudah mencapai batas
                if ($quiz->limit_attempts > 0 && $attemptCount >= $quiz->limit_attempts) {
                    return redirect()->route('quiz.index')
                        ->with('error', 'Anda telah mencapai batas maksimal pengerjaan quiz ini (maksimal ' . $quiz->limit_attempts . ' kali).');
                }
            }

            // =================================================================
            // LANJUTKAN PROSES MEMBUAT / MENGAMBIL ATTEMPT
            // =================================================================
            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                $totalQuestions = $quiz->questions()->count();
                $defaultExamSettings = [
                    'show_leaderboard' => (bool) ($quiz->show_leaderboard ?? false),
                    'instant_feedback' => (bool) ($quiz->instant_feedback ?? false),
                    'streak_bonus' => (bool) ($quiz->streak_bonus ?? false),
                    'time_bonus' => (bool) ($quiz->time_bonus ?? false),
                    'time_per_question' => (int) ($quiz->time_per_question ?? 30),
                    'total_questions' => $totalQuestions,
                    'fullscreen_mode' => (bool) ($quiz->fullscreen_mode ?? false),
                    'block_new_tab' => (bool) ($quiz->block_new_tab ?? false),
                    'prevent_copy_paste' => (bool) ($quiz->prevent_copy_paste ?? false),
                    'disable_violations' => (bool) ($quiz->disable_violations ?? false),
                    'enable_proctoring' => (bool) ($quiz->enable_proctoring ?? false),
                    'require_camera' => (bool) ($quiz->require_camera ?? false),
                    'require_mic' => (bool) ($quiz->require_mic ?? false),
                    'quiz_stats' => [
                        'streak_count' => 0,
                        'time_spent' => 0,
                        'bonus_points' => 0,
                    ],
                ];

                $attempt = ExamAttempt::create([
                    'exam_id'        => $quiz->id,
                    'student_id'     => $user->id,
                    'status'         => 'in_progress',
                    'started_at'     => now(),
                    'ip_address'     => request()->ip(),
                    'user_agent'     => request()->userAgent(),
                    'exam_settings'  => $defaultExamSettings,
                    'remaining_time' => $quiz->duration * 60,
                    'violation_count' => 0,
                    'violation_log'   => [],
                    'score'           => 0,
                    'final_score'     => 0,
                    'is_cheating_detected' => false,
                ]);
            }

            $questionsQuery = $quiz->questions()->with('choices')->orderBy('order');
            $questions = $questionsQuery->get();

            if ($questions->isEmpty()) {
                return redirect()->route('quiz.index')->with('error', 'Quiz ini belum memiliki soal.');
            }

            if ($quiz->shuffle_question) {
                $questions = $questions->shuffle();
            }

            $formattedQuestions = $questions->map(function ($q) use ($quiz) {
                $choices = $q->choices->map(function ($c) {
                    return [
                        'id'         => $c->id,
                        'text'       => $c->text,
                        'is_correct' => (bool) $c->is_correct,
                        'disabled'   => false,
                    ];
                });
                if ($quiz->shuffle_answer) {
                    $choices = $choices->shuffle();
                }
                return [
                    'id'             => $q->id,
                    'question'       => $q->question,
                    'type'           => $q->type,
                    'score'          => $q->score,
                    'explanation'    => $q->explanation ?? '',
                    'choices'        => $q->type === 'PG' ? $choices->values() : [],
                    'short_answers'  => $q->type === 'IS' ? (json_decode($q->short_answers ?? '[]') ?? []) : [],
                    'selectedAnswer' => null,
                    'textAnswer'     => null,
                ];
            })->values();

            $timeRemaining = $attempt->remaining_time > 0
                ? $attempt->remaining_time
                : ($quiz->duration * 60);

            return view('murid.quiz.play_simple', compact(
                'quiz',
                'questions',
                'formattedQuestions',
                'attempt',
                'timeRemaining'
            ));
        } catch (\Exception $e) {
            Log::error('Error in playQuiz: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * POST /quiz/{quiz}/powerup – gunakan power‑up
     */
    public function usePowerup(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'powerup_type' => 'required|string|in:double_up,triple_up,multiplier_2x,immunity,streak_saver,eraser,supersonic,gift,freeze,glitch',
                'question_id'  => 'nullable|exists:exam_questions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe power‑up tidak valid'
                ], 422);
            }

            $quiz = Exam::findOrFail($quizId);

            if (!$quiz->enable_powerups) {
                return response()->json([
                    'success' => false,
                    'message' => 'Power‑ups tidak diaktifkan untuk quiz ini'
                ], 403);
            }

            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
            }

            $settings = $attempt->exam_settings;
            if (is_string($settings)) {
                $settings = json_decode($settings, true) ?? [];
            } elseif (!is_array($settings)) {
                $settings = [];
            }

            $powerupType = $request->powerup_type;
            $questionId  = $request->question_id;
            $responseData = [
                'success'  => true,
                'cooldown' => 10,
            ];

            switch ($powerupType) {
                case 'double_up':
                    $responseData['next_question_multiplier'] = 2;
                    $responseData['message']  = '2x Skor untuk soal berikutnya!';
                    $responseData['cooldown'] = 15;
                    break;
                case 'triple_up':
                    $responseData['next_question_multiplier'] = 3;
                    $responseData['message']  = '3x Skor untuk soal berikutnya!';
                    $responseData['cooldown'] = 20;
                    break;
                case 'multiplier_2x':
                    $attempt->setActiveMultiplier(2, 30);
                    $responseData['active_multiplier'] = 2;
                    $responseData['duration']          = 30;
                    $responseData['message']           = '2x Multiplier aktif selama 30 detik!';
                    $responseData['cooldown']          = 45;
                    break;
                case 'immunity':
                    $settings['immunity_active'] = true;
                    $attempt->exam_settings = $settings;
                    $attempt->save();
                    $responseData['immunity_active'] = true;
                    $responseData['message']         = '🛡️ Immunity aktif! Jawaban salah berikutnya tidak menghilangkan streak.';
                    $responseData['cooldown']        = 30;
                    break;
                case 'streak_saver':
                    $settings['streak_saver_active'] = true;
                    $attempt->exam_settings = $settings;
                    $attempt->save();
                    $responseData['streak_saver_active'] = true;
                    $responseData['message']              = '🔄 Streak Saver aktif!';
                    $responseData['cooldown']             = 25;
                    break;
                case 'eraser':
                    if ($questionId) {
                        $question = ExamQuestion::with('choices')->find($questionId);
                        if ($question) {
                            $wrongChoices = $question->choices->where('is_correct', false)->values();
                            if ($wrongChoices->count() > 0) {
                                $randomWrong = $wrongChoices->random();
                                $responseData['eraser_choice_index'] = $question->choices
                                    ->search(fn($c) => $c->id === $randomWrong->id);
                                $responseData['message'] = '🗑️ Satu pilihan salah dihapus!';
                            } else {
                                $responseData['message'] = 'Tidak ada pilihan yang bisa dihapus';
                            }
                        }
                    }
                    $responseData['cooldown'] = 20;
                    break;
                case 'supersonic':
                    $settings['supersonic_active'] = true;
                    $attempt->exam_settings = $settings;
                    $attempt->save();
                    $responseData['supersonic_active'] = true;
                    $responseData['message']            = '🚀 Supersonic! Skor soal berikutnya 2x jika benar!';
                    $responseData['cooldown']           = 20;
                    break;
                case 'gift':
                    $bonusPoints = rand(5, 15);
                    $attempt->score = ($attempt->score ?? 0) + $bonusPoints;
                    $attempt->save();
                    $responseData['bonus']   = $bonusPoints;
                    $responseData['message'] = "🎁 Bonus {$bonusPoints} poin!";
                    $responseData['cooldown'] = 30;
                    break;
                case 'freeze':
                    $responseData['freeze_timer'] = true;
                    $responseData['message']      = '❄️ Timer soal di-reset!';
                    $responseData['cooldown']     = 25;
                    break;
                case 'glitch':
                    $responseData['glitch']   = true;
                    $responseData['message']  = '👁️ Jawaban diacak!';
                    $responseData['cooldown'] = 15;
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Tipe power‑up tidak dikenal'], 422);
            }

            $attempt->addPowerupUsage($powerupType, [
                'question_id' => $questionId,
                'result'      => $responseData,
            ]);

            $attempt->exam_settings = $settings;
            $attempt->save();

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Error in usePowerup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // ✅ FIX #1 – submitQuiz() dengan student_id & kolom benar
    // =========================================================================
    /**
     * POST /quiz/{quiz}/submit – submit jawaban quiz
     */
    public function submitQuiz(Request $request, Exam $quiz)
    {
        try {
            $user = Auth::user();

            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->whereIn('status', ['in_progress', 'timeout'])
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
            }

            DB::beginTransaction();

            $answersRaw = $request->answers;
            $answers    = is_string($answersRaw) ? json_decode($answersRaw, true) : $answersRaw;
            $answers    = $answers ?? [];

            foreach ($answers as $questionId => $answerValue) {
                $question = ExamQuestion::with('choices')->find($questionId);
                if (!$question) continue;

                $isCorrect = false;
                $score     = 0;

                if ($question->type === 'PG') {
                    $choice = ExamChoice::find($answerValue);
                    if ($choice && $choice->is_correct) {
                        $isCorrect = true;
                        $score     = $question->score;
                    }

                    ExamAnswer::updateOrCreate(
                        [
                            'attempt_id'  => $attempt->id,
                            'question_id' => $questionId,
                        ],
                        [
                            'exam_id'     => $quiz->id,
                            'student_id'  => $user->id,
                            'choice_id'   => $answerValue,
                            'answer_text' => null,
                            'is_correct'  => $isCorrect,
                            'score'       => $score,
                            'answered_at' => now(),
                        ]
                    );
                } elseif ($question->type === 'IS') {
                    $shortAnswers = json_decode($question->short_answers ?? '[]', true) ?? [];
                    $textAnswer   = strtolower(trim($answerValue ?? ''));
                    foreach ($shortAnswers as $correct) {
                        if (strtolower(trim($correct)) === $textAnswer) {
                            $isCorrect = true;
                            $score     = $question->score;
                            break;
                        }
                    }

                    ExamAnswer::updateOrCreate(
                        [
                            'attempt_id'  => $attempt->id,
                            'question_id' => $questionId,
                        ],
                        [
                            'exam_id'     => $quiz->id,
                            'student_id'  => $user->id,
                            'choice_id'   => null,
                            'answer_text' => $answerValue,
                            'is_correct'  => $isCorrect,
                            'score'       => $score,
                            'answered_at' => now(),
                        ]
                    );
                }
            }

            $examSettings = $attempt->exam_settings;
            if (is_string($examSettings)) {
                $examSettings = json_decode($examSettings, true) ?? [];
            } elseif (!is_array($examSettings)) {
                $examSettings = [];
            }
            $examSettings['quiz_stats'] = [
                'streak_count' => $request->streak_count ?? 0,
                'time_spent'   => $request->time_spent   ?? 0,
                'bonus_points' => $request->bonus_points ?? 0,
            ];
            $attempt->exam_settings = $examSettings;

            $attempt->submit();

            if ($quiz->quiz_mode === 'live') {
                $session = $quiz->activeSession;
                if ($session) {
                    QuizParticipant::where('quiz_session_id', $session->id)
                        ->where('student_id', $user->id)
                        ->update([
                            'status'       => 'submitted',
                            'submitted_at' => now(),
                        ]);
                    $session->updateStats();
                }
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Quiz berhasil disubmit!',
                'final_score' => round($attempt->final_score, 2),
                'redirect'    => route('quiz.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in submitQuiz: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'quiz_id' => $quiz->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // ✅ FIX #2 – Fungsi BARU: reportViolation() (sync ke room guru + auto submit)
    // =========================================================================
    /**
     * POST /quiz/{quiz}/report-violation
     * Catat pelanggaran siswa DAN update QuizParticipant agar guru bisa melihat di room.
     */
    public function reportViolation(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'type'    => 'required|string|max:100',
                'details' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal'], 422);
            }

            $quiz = Exam::findOrFail($quizId);

            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if ($quiz->disable_violations) {
                return response()->json([
                    'success'         => true,
                    'violation_count' => 0,
                    'message'         => 'Violations disabled',
                ]);
            }

            $violationCount = 0;
            $autoSubmit     = false;

            if ($attempt) {
                $attempt->logViolation($request->type, $request->details);
                $violationCount = $attempt->violation_count;

                $limit = $quiz->violation_limit ?? 3;
                if ($violationCount >= $limit && !$attempt->is_cheating_detected) {
                    $attempt->is_cheating_detected = true;
                    $attempt->save();
                    $autoSubmit = true;
                }
            }

            $session = $quiz->activeSession;
            if ($session) {
                $participant = QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('student_id', $user->id)
                    ->first();

                if ($participant) {
                    $vLog   = $participant->violation_log ?? [];
                    $vLog[] = [
                        'type'      => $request->type,
                        'details'   => $request->details,
                        'timestamp' => now()->toDateTimeString(),
                    ];

                    $participant->update([
                        'violation_count' => ($participant->violation_count ?? 0) + 1,
                        'violation_log'   => $vLog,
                        'is_violation'    => true,
                        'violation_type'  => $request->type,
                    ]);

                    $session->updateStats();
                }
            }

            return response()->json([
                'success'         => true,
                'violation_count' => $violationCount,
                'auto_submit'     => $autoSubmit,
                'message'         => $autoSubmit
                    ? 'Batas pelanggaran tercapai, quiz akan disubmit otomatis.'
                    : 'Pelanggaran dicatat (#' . $violationCount . ')',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in reportViolation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /quiz/{quiz}/result/{attempt} – lihat hasil
     */
    public function quizResult($quizId, $attemptId)
    {
        try {
            $user = Auth::user();

            $quiz = Exam::with(['subject', 'class'])->findOrFail($quizId);

            $attempt = ExamAttempt::with(['answers.question.choices'])
                ->where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->findOrFail($attemptId);

            $totalQuestions = $quiz->questions()->count();
            $answeredQuestions = $attempt->answers()->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $incorrectAnswers = $answeredQuestions - $correctAnswers;

            $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

            $quizStats = $attempt->exam_settings['quiz_stats'] ?? [];

            $leaderboard = $this->getQuizLeaderboard($quiz);

            $userPosition = 0;
            foreach ($leaderboard as $index => $entry) {
                if ($entry['student_id'] == $user->id) {
                    $userPosition = $index + 1;
                    break;
                }
            }

            // Hitung jumlah attempt yang sudah submitted/timeout
            $attemptCount = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            // Tentukan apakah boleh mengulang
            $canRetake = $quiz->enable_retake && ($quiz->limit_attempts == 0 || $attemptCount < $quiz->limit_attempts);

            return view('murid.quiz.hasil', [
                'quiz' => $quiz,
                'attempt' => $attempt,
                'totalQuestions' => $totalQuestions,
                'answeredQuestions' => $answeredQuestions,
                'correctAnswers' => $correctAnswers,
                'incorrectAnswers' => $incorrectAnswers,
                'percentage' => $percentage,
                'quizStats' => $quizStats,
                'leaderboard' => $leaderboard,
                'userPosition' => $userPosition,
                'showLeaderboard' => $quiz->show_leaderboard,
                'attemptCount' => $attemptCount,
                'canRetake'    => $canRetake,

            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing quiz result: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Hasil quiz tidak ditemukan.');
        }
    }

    /**
     * GET /quiz/{quiz}/leaderboard – papan peringkat
     */
    public function quizLeaderboard($quizId)
    {
        try {
            $quiz = Exam::findOrFail($quizId);

            if (!$quiz->show_leaderboard) {
                abort(404, 'Leaderboard tidak tersedia untuk quiz ini.');
            }

            $leaderboard = $this->getQuizLeaderboard($quiz);

            return view('murid.quiz.leaderboard', [
                'quiz' => $quiz,
                'leaderboard' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting quiz leaderboard: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat leaderboard.');
        }
    }

    /**
     * POST /quiz/{quiz}/bonus – klaim bonus
     */
    public function claimBonus(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'bonus_type' => 'required|in:streak,time,perfect,first_place',
                'value' => 'required|integer|min:1',
            ]);

            $attempt = ExamAttempt::where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada quiz yang sedang berjalan.'
                ], 404);
            }

            $bonuses = $attempt->exam_settings['bonuses_claimed'] ?? [];
            $bonuses[] = [
                'type' => $request->bonus_type,
                'value' => $request->value,
                'timestamp' => now()->toDateTimeString(),
            ];

            $quizData = $attempt->exam_settings;
            $quizData['bonuses_claimed'] = $bonuses;
            $attempt->exam_settings = $quizData;
            $attempt->save();

            return response()->json([
                'success' => true,
                'message' => 'Bonus diterima!',
                'bonus_type' => $request->bonus_type,
                'value' => $request->value,
            ]);
        } catch (\Exception $e) {
            Log::error('Error claiming bonus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.'
            ], 500);
        }
    }

    /**
     * GET/POST /quiz/{quiz}/save-progress – simpan progres
     */
    public function saveQuizProgress(Request $request, $quizId)
    {
        $user = Auth::user();

        $quiz = Exam::findOrFail($quizId);

        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
        }

        if ($request->isMethod('GET')) {
            $settings = $attempt->exam_settings ?? [];
            $progress = $settings['progress'] ?? null;

            return response()->json([
                'success'  => true,
                'progress' => $progress,
            ]);
        }

        try {
            $settings = $attempt->exam_settings ?? [];
            $settings['progress'] = [
                'current_question' => $request->current_question ?? 0,
                'time_remaining'   => $request->time_remaining   ?? 0,
                'total_score'      => $request->total_score      ?? 0,
                'streak_count'     => $request->streak_count     ?? 0,
                'bonus_points'     => $request->bonus_points     ?? 0,
                'answers'          => $request->answers          ?? [],
                'last_saved'       => now()->toDateTimeString(),
            ];
            $attempt->exam_settings   = $settings;
            $attempt->remaining_time  = $request->time_remaining ?? $attempt->remaining_time;
            $attempt->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /quiz/{quiz}/log-violation – catat pelanggaran siswa
     */
    public function logViolation(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'type'    => 'required|string|max:100',
                'details' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal'], 422);
            }

            $quiz = Exam::findOrFail($quizId);
            $attempt = ExamAttempt::where('exam_id', $quiz->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan'], 404);
            }

            if ($quiz->disable_violations) {
                return response()->json([
                    'success'       => true,
                    'violation_count' => 0,
                    'message'       => 'Violations disabled',
                ]);
            }

            $attempt->logViolation($request->type, $request->details);

            return response()->json([
                'success'         => true,
                'violation_count' => $attempt->violation_count,
                'message'         => 'Pelanggaran dicatat (#' . $attempt->violation_count . ')',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in logViolation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // ✅ FIX #3 – leaderboardTop5() include status timeout & fallback nama
    // =========================================================================
    /**
     * GET /quiz/{quiz}/leaderboard-top5 – top 5 untuk live update
     */
    public function leaderboardTop5($quizId)
    {
        try {
            $quiz = Exam::findOrFail($quizId);

            if (!$quiz->show_leaderboard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leaderboard tidak aktif untuk quiz ini'
                ], 403);
            }

            Log::info('Leaderboard Top 5 requested for quiz: ' . $quiz->id);

            $attempts = ExamAttempt::where('exam_id', $quiz->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->with('student:id,name')
                ->orderByDesc('final_score')
                ->orderBy('ended_at', 'asc')
                ->limit(5)
                ->get();

            Log::info('Found ' . $attempts->count() . ' attempts for leaderboard quiz ' . $quiz->id);

            $leaderboard = $attempts->map(function ($attempt, $index) {
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
                    'name'         => $studentName,
                    'score'        => round($attempt->final_score ?? 0, 2),
                    'time_taken'   => $timeTaken,
                    'submitted_at' => $attempt->ended_at ? $attempt->ended_at->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success'     => true,
                'leaderboard' => $leaderboard,
                'count'       => $leaderboard->count(),
                'quiz_id'     => $quiz->id,
                'quiz_title'  => $quiz->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in leaderboardTop5: ' . $e->getMessage());
            return response()->json([
                'success'     => false,
                'leaderboard' => [],
                'message'     => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: cek akses quiz
     */
    private function checkQuizAccess($quiz, $user)
    {
        if (!$user->hasRole('Murid')) {
            return false;
        }

        $student = $user->student;
        if (!$student || !$student->class_id) {
            return false;
        }

        if ($quiz->class_id != $student->class_id) {
            return false;
        }

        if ($quiz->type !== 'QUIZ') {
            return false;
        }

        return true;
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
