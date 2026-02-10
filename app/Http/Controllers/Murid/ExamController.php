<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\QuizParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ExamController extends Controller
{
    // ==================== REGULAR EXAMS METHODS ====================

    /**
     * GET /soal
     * Tampilkan daftar soal untuk siswa (regular exams)
     */
    public function indexSoal(Request $request)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Data siswa tidak ditemukan.'
                ]);
            }

            if (!$student->class_id) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Anda belum memiliki kelas.'
                ]);
            }

            $classId = $student->class_id;
            $search = $request->input('search');
            $status = $request->input('status', 'all');

            // Query dasar - hanya exam aktif (bukan QUIZ) untuk kelas siswa
            $query = Exam::with([
                'subject',
                'teacher.user',
                'questions'
            ])
                ->where('class_id', $classId)
                ->where('status', 'active')
                ->where('type', '!=', 'QUIZ'); // Exclude quizzes

            // Filter pencarian
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%')
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name_subject', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            // Pagination
            $exams = $query->orderBy('created_at', 'desc')->paginate(12);

            // Tambahkan informasi untuk setiap exam
            foreach ($exams as $exam) {
                $this->enrichExamData($exam, $user->id);
            }

            // Filter berdasarkan status jika ada
            if ($status && $status !== 'all') {
                $filteredExams = $exams->filter(function ($exam) use ($status) {
                    return $exam->display_status === $status;
                });

                // Convert ke paginator
                $page = $request->input('page', 1);
                $perPage = 12;
                $paginatedExams = new \Illuminate\Pagination\LengthAwarePaginator(
                    $filteredExams->forPage($page, $perPage),
                    $filteredExams->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                $exams = $paginatedExams;
            }

            return view('Siswa.soal', compact('exams'));
        } catch (\Exception $e) {
            Log::error('Error in indexSoal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return view('Siswa.soal', [
                'exams' => collect(),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * GET /soal/{exam}/detail
     * Tampilkan detail soal sebelum mulai
     */
    public function showDetail($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // Debug: Log student data
            Log::info('Student data:', [
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'user_id' => $user->id
            ]);

            // Query exam dengan relasi
            $exam = Exam::with([
                'subject',
                'teacher.user',
                'questions' => function ($query) {
                    $query->with('choices');
                }
            ])
                ->where('id', $examId)
                ->first();

            if (!$exam) {
                Log::error('Exam not found', ['exam_id' => $examId]);
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }

            // Jika exam adalah QUIZ, redirect ke halaman quiz
            if ($exam->type === 'QUIZ') {
                return redirect()->route('quiz.detail', $exam->id);
            }

            // Debug: Log exam data
            Log::info('Exam data:', [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'class_id' => $exam->class_id,
                'student_class_id' => $student->class_id,
                'status' => $exam->status
            ]);

            // Cek apakah exam untuk kelas siswa
            if ($exam->class_id != $student->class_id) {
                Log::warning('Class mismatch', [
                    'exam_class' => $exam->class_id,
                    'student_class' => $student->class_id
                ]);
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak tersedia untuk kelas Anda.');
            }

            // Cek status exam
            if ($exam->status !== 'active') {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak aktif.');
            }

            // Cek waktu akses
            $now = now();
            $timeStatus = 'available';

            if ($exam->start_at && $now < $exam->start_at) {
                $timeStatus = 'upcoming';
            } elseif ($exam->end_at && $now > $exam->end_at) {
                $timeStatus = 'finished';
            }

            if ($timeStatus === 'upcoming') {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian akan dimulai pada ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($timeStatus === 'finished') {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian telah berakhir pada ' . $exam->end_at->format('d M Y H:i'));
            }

            // Cek attempt
            $lastAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->latest()
                ->first();

            $attemptCount = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            // Hitung jumlah soal
            $questionsCount = $exam->questions->count();

            // Cek apakah bisa retake
            $canRetake = true;
            if ($exam->limit_attempts > 0 && $attemptCount >= $exam->limit_attempts) {
                $canRetake = false;
            }

            // Tambahkan data ke exam object untuk view
            $exam->questions_count = $questionsCount;
            $exam->attempt_count = $attemptCount;
            $exam->time_status = $timeStatus;

            return view('Siswa.soal-detail', compact('exam', 'lastAttempt', 'attemptCount', 'canRetake'));
        } catch (\Exception $e) {
            Log::error('Error in showDetail: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * POST /soal/{exam}/start
     * Mulai attempt baru untuk regular exam
     */
    public function start(Request $request, $examId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            $exam = Exam::find($examId);

            if (!$exam) {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }

            // Debug log
            Log::info('Start exam attempt', [
                'exam_id' => $examId,
                'student_id' => $user->id,
                'exam_class' => $exam->class_id,
                'student_class' => $student->class_id
            ]);

            // Validasi akses
            if ($exam->class_id != $student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }

            // Validasi status
            if ($exam->status !== 'active') {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian tidak aktif.');
            }

            // Validasi waktu
            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian akan dimulai pada ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($exam->end_at && $now > $exam->end_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian telah berakhir pada ' . $exam->end_at->format('d M Y H:i'));
            }

            // Cek apakah sudah ada attempt yang sedang berjalan
            $ongoingAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($ongoingAttempt) {
                DB::commit();
                return redirect()->route('soal.kerjakan', $examId)
                    ->with('info', 'Mengalihkan ke ujian yang sedang berjalan.');
            }

            // Cek batas percobaan
            $attemptCount = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            if ($exam->limit_attempts > 0 && $attemptCount >= $exam->limit_attempts) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Anda telah mencapai batas maksimal percobaan.');
            }

            // Buat attempt baru
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $user->id,
                'started_at' => now(),
                'remaining_time' => $exam->duration * 60,
                'status' => 'in_progress',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'exam_settings' => $exam->getAllSettings(),
                'violation_count' => 0,
            ]);

            // Set session untuk tracking
            session([
                'current_attempt_' . $examId => $attempt->id,
                'fullscreen_required_' . $examId => $exam->fullscreen_mode,
                'exam_started_' . $examId => true,
            ]);

            DB::commit();

            Log::info('Exam attempt created', [
                'attempt_id' => $attempt->id,
                'exam_id' => $examId,
                'student_id' => $user->id
            ]);

            return redirect()->route('soal.kerjakan', $examId)
                ->with('success', 'Ujian dimulai! Anda memiliki ' . $exam->duration . ' menit.')
                ->with('require_fullscreen', $exam->fullscreen_mode);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting exam: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('soal.detail', $examId)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /soal/{exam}/kerjakan
     * Halaman mengerjakan soal regular exam
     */
    public function attemptFromSession($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student || !$student->class_id) {
                return redirect()->route('soal.index')->with('error', 'Data siswa tidak ditemukan.');
            }

            $exam = Exam::with(['questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
                ->where('id', $examId)
                ->where('class_id', $student->class_id)
                ->where('status', 'active')
                ->firstOrFail();

            // Cari attempt yang sedang berjalan
            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Tidak ada ujian yang sedang berjalan. Silakan mulai ulang.');
            }

            // Cek pelanggaran
            if ($exam->violation_limit && $attempt->violation_count >= $exam->violation_limit) {
                $this->forceSubmitDueToViolation($attempt);
                return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                    ->with('error', 'Ujian dihentikan karena terlalu banyak pelanggaran.');
            }

            // Hitung waktu
            $timeElapsed = 0;
            if ($attempt->started_at) {
                $timeElapsed = now()->diffInSeconds($attempt->started_at);
            }

            $totalTime = $exam->duration * 60;
            $timeRemaining = max(0, $totalTime - $timeElapsed);

            if ($timeRemaining <= 0) {
                $this->forceSubmitDueToTimeout($attempt);
                return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                    ->with('error', 'Waktu ujian telah habis.');
            }

            // Format Pertanyaan
            $questions = $exam->questions->map(function ($question) {
                $options = [];
                if (($question->type === 'PG' || $question->type === 'multiple_choice') && $question->choices->isNotEmpty()) {
                    foreach ($question->choices->sortBy('order') as $choice) {
                        $options[$choice->label] = $choice->text;
                    }
                }

                return [
                    'id' => $question->id,
                    'question_text' => $question->question,
                    'question_image' => $question->question_image,
                    'type' => $question->type,
                    'score' => $question->score,
                    'options' => $options,
                    'order' => $question->order
                ];
            });

            // Acak soal jika diaktifkan
            if ($exam->shuffle_question) {
                $questions = $questions->shuffle();
            }

            // Reset keys agar jadi array urut [0, 1, 2] untuk JavaScript
            $questions = $questions->values();

            // Ambil jawaban tersimpan
            $savedAnswers = ExamAnswer::where('attempt_id', $attempt->id)->get();

            $answers = $savedAnswers->whereNotNull('choice_id')
                ->mapWithKeys(function ($item) use ($exam) {
                    $q = $exam->questions->find($item->question_id);
                    if ($q && $q->choices) {
                        $choice = $q->choices->find($item->choice_id);
                        return [$item->question_id => $choice ? $choice->label : null];
                    }
                    return [$item->question_id => null];
                })->toArray();

            $essayAnswers = $savedAnswers->whereNotNull('answer_text')
                ->whereNull('choice_id')
                ->pluck('answer_text', 'question_id')
                ->toArray();

            // Security Settings
            $securitySettings = [
                'fullscreen_mode' => (bool) $exam->fullscreen_mode,
                'block_new_tab' => (bool) $exam->block_new_tab,
                'prevent_copy_paste' => (bool) $exam->prevent_copy_paste,
                'violation_limit' => $exam->violation_limit ?? 3,
                'disable_violations' => (bool) $exam->disable_violations,
                'shuffle_question' => (bool) $exam->shuffle_question,
                'shuffle_answer' => (bool) $exam->shuffle_answer,
            ];

            // Marked for review
            $markedForReview = [];

            return view('murid.exams.attempt', [
                'exam' => $exam,
                'questions' => $questions,
                'attempt' => $attempt,
                'timeRemaining' => $timeRemaining,
                'answers' => $answers,
                'essayAnswers' => $essayAnswers,
                'markedForReview' => $markedForReview,
                'securitySettings' => $securitySettings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in attemptFromSession: ' . $e->getMessage());
            return redirect()->route('soal.index')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * POST /soal/{exam}/submit
     * Submit jawaban regular exam
     */
    public function submit(Request $request, $examId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $request->validate([
                'answers' => 'required|json',
            ]);

            $answers = json_decode($request->answers, true);

            // Cek apakah ini submit paksa karena pelanggaran
            $isForcedViolation = $request->input('force_submit_violation') == 'true';
            $violationCount = $request->input('violation_count', 0);
            $showResult = $request->input('show_result', 0);

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.index')
                    ->with('error', 'Tidak ada ujian yang sedang berjalan.');
            }

            $exam = Exam::with(['questions.choices'])->findOrFail($examId);

            // Simpan semua jawaban
            foreach ($answers as $questionId => $answerValue) {
                $question = $exam->questions->where('id', $questionId)->first();
                if (!$question) continue;

                $examAnswer = ExamAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $questionId)
                    ->first();

                if (!$examAnswer) {
                    $examAnswer = new ExamAnswer();
                    $examAnswer->exam_id = $examId;
                    $examAnswer->question_id = $questionId;
                    $examAnswer->student_id = $user->id;
                    $examAnswer->attempt_id = $attempt->id;
                }

                // Untuk PG
                if ($question->type === 'PG') {
                    if (is_string($answerValue) && strlen($answerValue) === 1) {
                        $choice = $question->choices->firstWhere('label', $answerValue);
                    } else {
                        $choice = $question->choices->firstWhere('id', $answerValue);
                    }

                    if ($choice) {
                        $examAnswer->choice_id = $choice->id;
                        $examAnswer->answer_text = $choice->text;
                        $examAnswer->is_correct = $choice->is_correct;
                        $examAnswer->score = $choice->is_correct ? $question->score : 0;
                    }
                } else {
                    // Untuk Essay
                    $examAnswer->answer_text = $answerValue;
                    $examAnswer->score = 0;
                }

                $examAnswer->answered_at = now();
                $examAnswer->save();
            }

            // Hitung total score
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Update attempt
            $attempt->status = 'submitted';
            $attempt->ended_at = now();
            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;

            // Tandai kecurangan jika submit dipaksa
            if ($isForcedViolation) {
                $attempt->is_cheating_detected = true;
                $attempt->violation_count = $violationCount;
            }

            $attempt->save();

            // Bersihkan session
            session()->forget([
                'current_attempt_' . $examId,
                'fullscreen_required_' . $examId,
                'exam_started_' . $examId,
            ]);

            session()->regenerate();

            DB::commit();

            // Tentukan redirect berdasarkan setting tampil hasil
            if ($showResult || $exam->show_result_after === 'immediately') {
                return redirect()->route('soal.hasil', [
                    'exam' => $examId,
                    'attempt' => $attempt->id
                ])->with('success', $isForcedViolation ?
                    'Ujian dihentikan karena pelanggaran.' :
                    'Jawaban berhasil dikumpulkan!');
            } else {
                return redirect()->route('soal.index')
                    ->with('success', 'Jawaban berhasil dikumpulkan! Hasil akan ditampilkan kemudian.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting exam: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /soal/{exam}/hasil/{attempt}
     * Tampilkan hasil ujian regular
     */
    public function result($examId, $attemptId)
    {
        try {
            $user = Auth::user();

            $exam = Exam::findOrFail($examId);

            $attempt = ExamAttempt::with(['answers.question.choices'])
                ->where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->findOrFail($attemptId);

            // Hitung statistik
            $totalQuestions = $exam->questions()->count();
            $answeredQuestions = $attempt->answers()->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $incorrectAnswers = $answeredQuestions - $correctAnswers;

            $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

            // Cek apakah lulus
            $isPassed = false;
            if ($exam->min_pass_grade) {
                $isPassed = $attempt->final_score >= $exam->min_pass_grade;
            }

            // Cek apakah bisa mengulang
            $allow_retake = false;
            if ($exam->limit_attempts > 1) {
                $attemptCount = ExamAttempt::where('exam_id', $examId)
                    ->where('student_id', $user->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                $allow_retake = $attemptCount < $exam->limit_attempts;
            }

            return view('murid.exams.result', [
                'exam' => $exam,
                'attempt' => $attempt,
                'allow_retake' => $allow_retake,
                'totalQuestions' => $totalQuestions,
                'answeredQuestions' => $answeredQuestions,
                'correctAnswers' => $correctAnswers,
                'incorrectAnswers' => $incorrectAnswers,
                'percentage' => $percentage,
                'isPassed' => $isPassed,
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing result: ' . $e->getMessage());
            return redirect()->route('soal.index')
                ->with('error', 'Hasil ujian tidak ditemukan.');
        }
    }

    // ==================== QUIZ METHODS ====================

    /**
     * Display a listing of quizzes (interactive quizzes)
     */
    public function indexQuiz(Request $request)
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

            // Query dasar - hanya quiz aktif untuk kelas siswa
            $query = Exam::with([
                'subject',
                'teacher.user',
                'questions'
            ])
                ->where('class_id', $classId)
                ->where('type', 'QUIZ')
                ->where('status', 'active');

            // Filter pencarian
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

            // Pagination
            $quizzes = $query->orderBy('created_at', 'desc')->paginate(12);

            // Tambahkan informasi untuk setiap quiz
            foreach ($quizzes as $quiz) {
                $this->enrichQuizData($quiz, $user->id);

                // Tambahkan logika auto-redirect
                if ($quiz->is_room_open && $quiz->is_quiz_started) {
                    // Cek apakah sudah ada attempt yang sedang berjalan
                    $ongoingAttempt = ExamAttempt::where('exam_id', $quiz->id)
                        ->where('student_id', $user->id)
                        ->where('status', 'in_progress')
                        ->first();

                    if ($ongoingAttempt) {
                        // Redirect langsung ke halaman pengerjaan
                        return redirect()->route('quiz.play', $quiz->id);
                    }

                    // Cek apakah sudah join sebagai participant
                    $session = $quiz->activeSession;
                    if ($session) {
                        $participant = QuizParticipant::where([
                            'quiz_session_id' => $session->id,
                            'student_id' => $user->id,
                            'status' => 'started'
                        ])->first();

                        if ($participant) {
                            // Redirect ke halaman pengerjaan
                            return redirect()->route('quiz.play', $quiz->id);
                        }
                    }
                }
            }

            // Filter berdasarkan status jika ada
            if ($status && $status !== 'all') {
                $filteredQuizzes = $quizzes->filter(function ($quiz) use ($status) {
                    return $quiz->display_status === $status;
                });

                // Convert ke paginator
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
            Log::error('Error in indexQuiz: ' . $e->getMessage());
            return view('murid.quiz.index', [
                'quizzes' => collect(),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Play quiz page
     */
    public function playQuiz($quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('quiz.index')->with('error', 'Data siswa tidak ditemukan.');
            }

            // Ambil data quiz
            $quiz = Exam::with(['questions.choices', 'activeSession'])
                ->where('type', 'QUIZ')
                ->findOrFail($quizId);

            $session = $quiz->activeSession;

            if (!$session) {
                return view('Siswa.error_quiz', [
                    'title' => 'Sesi Tidak Ditemukan',
                    'message' => 'Sesi quiz tidak ditemukan atau sudah berakhir.',
                    'backRoute' => route('quiz.index')
                ]);
            }

            // Cari participant
            $participant = QuizParticipant::where([
                'quiz_session_id' => $session->id,
                'student_id' => $user->id
            ])->first();

            // JIKA BELUM JADI PARTICIPANT
            if (!$participant) {
                if ($quiz->is_room_open && !$quiz->is_quiz_started) {
                    // Buat participant baru
                    $participant = QuizParticipant::create([
                        'quiz_session_id' => $session->id,
                        'student_id' => $user->id,
                        'exam_id' => $quiz->id,
                        'status' => 'waiting',
                        'joined_at' => now(),
                        'is_present' => true,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->header('User-Agent'),
                    ]);

                    // Redirect ke room TANPA LOOP
                    return redirect()->route('quiz.room', $quiz->id)
                        ->with('success', 'Anda telah bergabung ke ruangan quiz.');
                } else {
                    return redirect()->route('quiz.room', $quiz->id)
                        ->with('error', 'Ruangan quiz sudah ditutup atau quiz sudah dimulai.');
                }
            }

            // JIKA QUIZ BELUM DIMULAI - TIDAK REDIRECT, TAMPILKAN INFORMASI
            if (!$quiz->is_quiz_started) {
                // Tampilkan halaman waiting, jangan redirect loop
                return view('Siswa.quiz_waiting', [
                    'quiz' => $quiz,
                    'session' => $session,
                    'participant' => $participant,
                    'message' => 'Quiz belum dimulai oleh guru. Silakan tunggu...'
                ]);
            }

            // JIKA QUIZ SUDAH DIMULAI, TAPI STATUS SISWA BELUM STARTED
            if (in_array($participant->status, ['waiting', 'ready'])) {
                $participant->update([
                    'status' => 'started',
                    'started_at' => now()
                ]);
            }

            // JIKA SUDAH SELESAI
            if ($participant->status === 'submitted') {
                return redirect()->route('quiz.result', [
                    'quiz' => $quizId,
                    'attempt' => $participant->attempt()->first()->id ?? null
                ])->with('info', 'Anda sudah mengerjakan quiz ini.');
            }

            // Buat atau ambil Attempt
            $attempt = ExamAttempt::firstOrCreate(
                [
                    'exam_id' => $quiz->id,
                    'student_id' => $user->id,
                    'quiz_session_id' => $session->id,
                ],
                [
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'remaining_time' => $quiz->duration * 60,
                ]
            );

            // Load questions
            $questions = $quiz->questions()->with('choices')->get();

            // Shuffle jika diaktifkan
            if ($quiz->shuffle_question) {
                $questions = $questions->shuffle();
            }

            return view('Siswa.play', compact('quiz', 'session', 'participant', 'attempt', 'questions'));
        } catch (\Exception $e) {
            Log::error('Error in playQuiz: ' . $e->getMessage());
            return redirect()->route('quiz.index')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Submit quiz answers
     */
    public function submitQuiz(Request $request, $quizId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $request->validate([
                'answers' => 'required|json',
                'score' => 'nullable|numeric',
                'time_spent' => 'nullable|integer',
                'streak_count' => 'nullable|integer',
                'bonus_points' => 'nullable|integer',
            ]);

            $answers = json_decode($request->answers, true);

            $attempt = ExamAttempt::where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada quiz yang sedang berjalan.'
                ], 404);
            }

            $quiz = Exam::with(['questions.choices'])->findOrFail($quizId);

            // Simpan semua jawaban
            foreach ($answers as $questionId => $answerValue) {
                $question = $quiz->questions->where('id', $questionId)->first();
                if (!$question) continue;

                $examAnswer = ExamAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $questionId)
                    ->first();

                if (!$examAnswer) {
                    $examAnswer = new ExamAnswer();
                    $examAnswer->exam_id = $quizId;
                    $examAnswer->question_id = $questionId;
                    $examAnswer->student_id = $user->id;
                    $examAnswer->attempt_id = $attempt->id;
                }

                // Untuk PG
                if ($question->type === 'PG') {
                    if (is_string($answerValue) && strlen($answerValue) === 1) {
                        $choice = $question->choices->firstWhere('label', $answerValue);
                    } else {
                        $choice = $question->choices->firstWhere('id', $answerValue);
                    }

                    if ($choice) {
                        $examAnswer->choice_id = $choice->id;
                        $examAnswer->answer_text = $choice->text;
                        $examAnswer->is_correct = $choice->is_correct;
                        $examAnswer->score = $choice->is_correct ? $question->score : 0;
                    }
                } else {
                    // Untuk Essay
                    $examAnswer->answer_text = $answerValue;
                    $examAnswer->score = 0;
                }

                $examAnswer->answered_at = now();
                $examAnswer->save();
            }

            // Hitung total score dengan bonus
            $baseScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $quiz->questions->sum('score');

            // Tambahkan bonus jika ada
            $bonusPoints = $request->bonus_points ?? 0;
            $streakBonus = $request->streak_count ?? 0;
            $timeBonus = $request->time_spent ? max(0, 100 - ($request->time_spent / 60)) : 0;

            $totalScore = $baseScore + $bonusPoints + $streakBonus + $timeBonus;
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Update attempt
            $attempt->status = 'submitted';
            $attempt->ended_at = now();
            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;

            // Simpan data quiz khusus
            $quizData = $attempt->exam_settings ?? [];
            $quizData['quiz_stats'] = [
                'streak_count' => $request->streak_count ?? 0,
                'time_spent' => $request->time_spent ?? 0,
                'bonus_points' => $bonusPoints,
                'time_bonus' => $timeBonus,
                'streak_bonus' => $streakBonus,
            ];
            $attempt->exam_settings = $quizData;

            $attempt->save();

            // Update participant status
            $session = $quiz->activeSession;
            if ($session) {
                $participant = QuizParticipant::where([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id
                ])->first();

                if ($participant) {
                    $participant->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    // Update session stats
                    $session->updateStats();
                }
            }

            // Bersihkan session
            session()->forget([
                'current_quiz_attempt_' . $quizId,
                'quiz_started_' . $quizId,
                'quiz_mode',
                'difficulty_level',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil diselesaikan!',
                'redirect' => route('quiz.result', ['quiz' => $quizId, 'attempt' => $attempt->id]),
                'score' => $finalScore,
                'show_leaderboard' => $quiz->show_leaderboard
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting quiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display quiz detail
     */
    public function showQuizDetail($quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            $quiz = Exam::with([
                'subject',
                'teacher.user',
                'questions' => function ($query) {
                    $query->with('choices');
                }
            ])
                ->where('id', $quizId)
                ->where('type', 'QUIZ')
                ->first();

            if (!$quiz) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz tidak ditemukan.');
            }

            // Cek apakah quiz untuk kelas siswa
            if ($quiz->class_id != $student->class_id) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz tidak tersedia untuk kelas Anda.');
            }

            // Cek status quiz
            if ($quiz->status !== 'active') {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz tidak aktif.');
            }

            // Cek waktu akses
            $now = now();
            $timeStatus = 'available';

            if ($quiz->start_at && $now < $quiz->start_at) {
                $timeStatus = 'upcoming';
            } elseif ($quiz->end_at && $now > $quiz->end_at) {
                $timeStatus = 'finished';
            }

            if ($timeStatus === 'upcoming') {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz akan dimulai pada ' . $quiz->start_at->format('d M Y H:i'));
            }

            if ($timeStatus === 'finished') {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz telah berakhir pada ' . $quiz->end_at->format('d M Y H:i'));
            }

            // Cek attempt
            $lastAttempt = ExamAttempt::where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->latest()
                ->first();

            $attemptCount = ExamAttempt::where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            // Hitung jumlah soal
            $questionsCount = $quiz->questions->count();

            // Cek apakah bisa retake
            $canRetake = $quiz->enable_retake;
            if ($quiz->limit_attempts > 0 && $attemptCount >= $quiz->limit_attempts) {
                $canRetake = false;
            }

            // Tambahkan data ke quiz object untuk view
            $quiz->questions_count = $questionsCount;
            $quiz->attempt_count = $attemptCount;
            $quiz->time_status = $timeStatus;

            // Get quiz statistics
            $duration = $quiz->duration;
            $timePerQuestion = $quiz->time_per_question;

            // Get leaderboard jika diaktifkan
            $showLeaderboard = $quiz->show_leaderboard;
            $leaderboard = $showLeaderboard ? $this->getQuizLeaderboard($quiz) : null;

            // Get quiz features
            $quizFeatures = [
                'enable_music' => $quiz->enable_music,
                'enable_memes' => $quiz->enable_memes,
                'enable_powerups' => $quiz->enable_powerups,
                'instant_feedback' => $quiz->instant_feedback,
                'streak_bonus' => $quiz->streak_bonus,
                'time_bonus' => $quiz->time_bonus,
            ];

            return view('murid.quiz.detail', compact(
                'quiz',
                'lastAttempt',
                'attemptCount',
                'canRetake',
                'duration',
                'timePerQuestion',
                'showLeaderboard',
                'leaderboard',
                'quizFeatures'
            ));
        } catch (\Exception $e) {
            Log::error('Error in showQuizDetail: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Start quiz attempt
     */
    public function startQuiz(Request $request, Exam $quiz)
    {
        try {
            $user = Auth::user();

            // Validasi akses
            if (!$user->hasRole('Murid')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            // Cek apakah quiz tersedia
            if ($quiz->status !== 'active' || !$quiz->is_quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak tersedia'
                ], 422);
            }

            // Cek apakah room terbuka dan quiz dimulai
            if (!$quiz->is_room_open || !$quiz->is_quiz_started) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz belum dimulai oleh guru'
                ], 422);
            }

            // Cek participant
            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi tidak ditemukan'
                ], 422);
            }

            $participant = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('student_id', $user->id)
                ->first();

            if (!$participant || $participant->status !== 'started') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum diizinkan mengerjakan quiz'
                ], 422);
            }

            // Buat atau lanjutkan attempt
            $attempt = ExamAttempt::firstOrCreate(
                [
                    'exam_id' => $quiz->id,
                    'student_id' => $user->id,
                    'finished_at' => null,
                ],
                [
                    'started_at' => now(),
                    'status' => 'started',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Quiz siap dikerjakan',
                'redirect' => route('quiz.play', $quiz->id)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error starting quiz: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show quiz result
     */
    public function quizResult($quizId, $attemptId)
    {
        try {
            $user = Auth::user();

            $quiz = Exam::findOrFail($quizId);

            $attempt = ExamAttempt::with(['answers.question.choices'])
                ->where('exam_id', $quizId)
                ->where('student_id', $user->id)
                ->findOrFail($attemptId);

            // Hitung statistik
            $totalQuestions = $quiz->questions()->count();
            $answeredQuestions = $attempt->answers()->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $incorrectAnswers = $answeredQuestions - $correctAnswers;

            $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

            // Get quiz stats
            $quizStats = $attempt->exam_settings['quiz_stats'] ?? [];

            // Get leaderboard
            $leaderboard = $this->getQuizLeaderboard($quiz);

            // Check user position in leaderboard
            $userPosition = 0;
            foreach ($leaderboard as $index => $entry) {
                if ($entry['student_id'] == $user->id) {
                    $userPosition = $index + 1;
                    break;
                }
            }

            return view('murid.quiz.result', [
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
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing quiz result: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Hasil quiz tidak ditemukan.');
        }
    }

    /**
     * Get quiz leaderboard
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
     * Use powerup in quiz
     */
    public function usePowerup(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'powerup_type' => 'required|in:skip,hint,extra_time,double_points',
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

            $quiz = Exam::findOrFail($quizId);

            if (!$quiz->enable_powerups) {
                return response()->json([
                    'success' => false,
                    'message' => 'Powerups tidak diaktifkan untuk quiz ini.'
                ], 422);
            }

            // Simpan penggunaan powerup
            $powerups = $attempt->exam_settings['powerups_used'] ?? [];
            $powerups[] = [
                'type' => $request->powerup_type,
                'timestamp' => now()->toDateTimeString(),
                'question_id' => $request->question_id ?? null,
            ];

            $quizData = $attempt->exam_settings;
            $quizData['powerups_used'] = $powerups;
            $attempt->exam_settings = $quizData;
            $attempt->save();

            return response()->json([
                'success' => true,
                'message' => 'Powerup digunakan!',
                'powerup_type' => $request->powerup_type,
            ]);
        } catch (\Exception $e) {
            Log::error('Error using powerup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.'
            ], 500);
        }
    }

    /**
     * Claim bonus in quiz
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

            // Simpan bonus
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
     * Save quiz progress
     */
    public function saveQuizProgress(Request $request, $quizId)
    {
        try {
            $user = Auth::user();

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

            $request->validate([
                'current_question' => 'required|integer',
                'answers' => 'nullable|json',
                'time_remaining' => 'required|integer',
            ]);

            // Update remaining time
            $attempt->remaining_time = $request->time_remaining;

            // Save progress data
            $progressData = $attempt->exam_settings['progress'] ?? [];
            $progressData = array_merge($progressData, [
                'current_question' => $request->current_question,
                'last_saved' => now()->toDateTimeString(),
                'answers' => json_decode($request->answers, true),
            ]);

            $quizData = $attempt->exam_settings;
            $quizData['progress'] = $progressData;
            $attempt->exam_settings = $quizData;

            $attempt->save();

            return response()->json([
                'success' => true,
                'message' => 'Progress disimpan.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving quiz progress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.'
            ], 500);
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

        // Enrich quiz data
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

        // Enrich quiz data
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

        // Enrich quiz data
        foreach ($quizzes as $quiz) {
            $this->enrichQuizData($quiz, $user->id);
        }

        return view('murid.quiz.completed', compact('quizzes'));
    }

    // ==================== HELPER METHODS ====================

    /**
     * Enrich exam data with user-specific information
     */
    private function enrichExamData($exam, $userId)
    {
        // Hitung status waktu
        $now = now();
        $timeStatus = 'available';

        if ($exam->start_at && $now < $exam->start_at) {
            $timeStatus = 'upcoming';
        } elseif ($exam->end_at && $now > $exam->end_at) {
            $timeStatus = 'finished';
        }

        $exam->time_status = $timeStatus;

        // Cek attempt terakhir
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $userId)
            ->latest()
            ->first();

        // Hitung jumlah percobaan yang sudah submit
        $exam->attempt_count = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $userId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        // Tentukan status tampilan
        $displayStatus = 'available';

        if ($exam->time_status === 'finished') {
            $displayStatus = 'finished';
        } elseif ($exam->time_status === 'upcoming') {
            $displayStatus = 'upcoming';
        } elseif ($attempt) {
            if ($attempt->status === 'submitted' || $attempt->status === 'timeout') {
                $displayStatus = 'completed';
            } elseif ($attempt->status === 'in_progress') {
                $displayStatus = 'ongoing';
            }
        }

        $exam->display_status = $displayStatus;
        $exam->last_attempt = $attempt;
        $exam->questions_count = $exam->questions->count();

        // Cek apakah bisa retake
        $exam->can_retake = true;
        if ($exam->limit_attempts > 0 && $exam->attempt_count >= $exam->limit_attempts) {
            $exam->can_retake = false;
        }
    }

    /**
     * Enrich quiz data with user-specific information
     */
    private function enrichQuizData($quiz, $userId)
    {
        // Hitung status waktu
        $now = now();
        $timeStatus = 'available';

        if ($quiz->start_at && $now < $quiz->start_at) {
            $timeStatus = 'upcoming';
        } elseif ($quiz->end_at && $now > $quiz->end_at) {
            $timeStatus = 'finished';
        }

        $quiz->time_status = $timeStatus;

        // Cek attempt terakhir
        $attempt = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->latest()
            ->first();

        // Hitung jumlah percobaan yang sudah submit
        $quiz->attempt_count = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $userId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        // Tentukan status tampilan
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

        // Cek apakah bisa retake
        $quiz->can_retake = $quiz->enable_retake;
        if ($quiz->limit_attempts > 0 && $quiz->attempt_count >= $quiz->limit_attempts) {
            $quiz->can_retake = false;
        }

        // Tambahkan informasi room
        $quiz->room_status = $quiz->is_room_open ? ($quiz->is_quiz_started ? 'started' : 'open') : 'closed';
    }

    /**
     * Get quiz leaderboard
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
     * Force submit karena pelanggaran
     */
    private function forceSubmitDueToViolation($attempt)
    {
        try {
            DB::beginTransaction();

            $attempt->status = 'submitted';
            $attempt->is_cheating_detected = true;
            $attempt->ended_at = now();
            $attempt->save();

            // Bersihkan session
            $examId = $attempt->exam_id;
            session()->forget([
                'current_attempt_' . $examId,
                'fullscreen_required_' . $examId,
                'exam_started_' . $examId,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in forceSubmitDueToViolation: ' . $e->getMessage());
        }
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

            // Update participant jika ada
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

            // Bersihkan session
            $examId = $attempt->exam_id;
            session()->forget([
                'current_quiz_attempt_' . $examId,
                'quiz_started_' . $examId,
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
     * Clear exam session
     */
    private function clearExamSession($examId)
    {
        $keys = [
            'current_attempt_' . $examId,
            'fullscreen_required_' . $examId,
            'exam_started_' . $examId,
            'violation_count_' . $examId,
            'exam_answers_' . $examId,
            'exam_time_' . $examId,
        ];

        foreach ($keys as $key) {
            session()->forget($key);
        }

        session()->regenerate();
    }

    // ==================== COMPATIBILITY METHODS ====================

    /**
     * GET /exams
     * Compatibility method for existing routes
     */
    public function index()
    {
        return $this->indexSoal(request());
    }

    /**
     * GET /exams/active
     */
    public function active()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('Murid.Exam.active', ['exams' => collect()]);
        }

        $exams = Exam::where('class_id', $student->class_id)
            ->where('type', '!=', 'QUIZ')
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
            ->orderBy('end_at', 'asc')
            ->get();

        return view('Murid.Exam.active', compact('exams'));
    }

    /**
     * GET /exams/upcoming
     */
    public function upcoming()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('Murid.Exam.upcoming', ['exams' => collect()]);
        }

        $exams = Exam::where('class_id', $student->class_id)
            ->where('type', '!=', 'QUIZ')
            ->where('status', 'active')
            ->where('start_at', '>', now())
            ->with(['subject', 'class'])
            ->orderBy('start_at', 'asc')
            ->get();

        return view('Murid.Exam.upcoming', compact('exams'));
    }

    /**
     * GET /exams/completed
     */
    public function completed()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->class_id) {
            return view('Murid.Exam.completed', ['exams' => collect()]);
        }

        $exams = Exam::where('class_id', $student->class_id)
            ->where('type', '!=', 'QUIZ')
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
            ->get();

        return view('Murid.Exam.completed', compact('exams'));
    }

    /**
     * GET /exams/{exam}
     */
    public function show(Exam $exam)
    {
        $user = Auth::user();
        $student = $user->student;

        // Redirect to quiz if it's a quiz type
        if ($exam->type === 'QUIZ') {
            return redirect()->route('quiz.detail', $exam->id);
        }

        // Check access
        if (!$exam->canBeAccessedByStudent($user->id, $student->class_id)) {
            return back()->with('error', 'Anda tidak dapat mengakses ujian ini.');
        }

        $hasAttempted = $exam->hasAttempt($user->id);
        $latestAttempt = $exam->getLatestAttempt($user->id);
        $attemptCount = $exam->getAttemptCount($user->id);
        $canRetry = $exam->canRetry($user->id);

        return view('murid.exams.show', compact(
            'exam',
            'hasAttempted',
            'latestAttempt',
            'attemptCount',
            'canRetry'
        ));
    }

    // Tambahkan method untuk murid mengakses quiz

    /**
     * Join quiz room
     */
    /**
     * Get quiz room status for student (AJAX)
     */
    public function getQuizRoomStatus(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan'
                ], 422);
            }

            $quiz = Exam::with(['activeSession'])->findOrFail($quizId);

            // Validasi akses
            if ($quiz->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak tersedia untuk kelas Anda'
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
                    'participant' => null,
                    'time_remaining' => null,
                    'should_redirect' => false
                ]);
            }

            // Cari participant siswa ini (DENGAN relasi student)
            $currentParticipant = QuizParticipant::with(['student'])
                ->where([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id
                ])->first();

            // Ambil ALL participants dengan relasi student
            $participants = QuizParticipant::with(['student'])
                ->where('quiz_session_id', $session->id)
                ->where('student_id', '!=', $user->id) // Jangan tampilkan diri sendiri
                ->where('is_present', true)
                ->get();

            // Format data participants untuk dikirim ke frontend
            $participantsData = $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'name' => $participant->student->name ?? 'Unknown',
                    'email' => $participant->student->email ?? '',
                    'status' => $participant->status,
                    'joined_time' => $participant->joined_at ? $participant->joined_at->format('H:i') : '-',
                    'initial' => $participant->student->name ? strtoupper(substr($participant->student->name, 0, 1)) : '?',
                ];
            });

            // Hitung statistik (termasuk diri sendiri)
            $allParticipants = QuizParticipant::where('quiz_session_id', $session->id)
                ->where('is_present', true)
                ->get();

            $stats = [
                'total_students' => $allParticipants->count(),
                'joined' => $allParticipants->where('status', '!=', 'disconnected')->count(),
                'ready' => $allParticipants->where('status', 'ready')->count(),
                'started' => $allParticipants->where('status', 'started')->count(),
                'submitted' => $allParticipants->where('status', 'submitted')->count()
            ];

            // Data participant saat ini
            $participantData = null;
            if ($currentParticipant) {
                $participantData = [
                    'status' => $currentParticipant->status,
                    'joined_at' => $currentParticipant->joined_at ? $currentParticipant->joined_at->format('H:i') : null,
                    'ready_at' => $currentParticipant->ready_at ? $currentParticipant->ready_at->format('H:i') : null,
                    'name' => $currentParticipant->student->name ?? 'Unknown',
                ];
            }

            // Hitung sisa waktu
            $timeRemaining = null;
            if ($quiz->is_quiz_started && $session->session_started_at && $quiz->duration) {
                $elapsed = now()->diffInSeconds($session->session_started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            }

            // Tentukan apakah harus redirect
            $shouldRedirect = false;
            if ($quiz->is_quiz_started && $currentParticipant && $currentParticipant->status === 'started') {
                $shouldRedirect = true;
            }

            return response()->json([
                'success' => true,
                'is_room_open' => (bool) $quiz->is_room_open,
                'is_quiz_started' => (bool) $quiz->is_quiz_started,
                'stats' => $stats,
                'participants' => $participantsData,
                'participant' => $participantData,
                'time_remaining' => $timeRemaining,
                'should_redirect' => $shouldRedirect,
                'redirect_url' => $shouldRedirect ? route('quiz.play', $quiz->id) : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getQuizRoomStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start quiz attempt for student
     */
    public function startQuizAttempt(Request $request, Exam $quiz)
    {
        $student = Auth::user()->student;

        if (!$quiz->is_quiz || !$quiz->is_quiz_started) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz belum dimulai'
            ], 422);
        }

        $session = $quiz->activeSession;
        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan'
            ], 422);
        }

        $participant = QuizParticipant::where([
            'quiz_session_id' => $session->id,
            'student_id' => $student->user_id
        ])->first();

        if (!$participant || $participant->status === 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar di sesi ini'
            ], 422);
        }

        try {
            // Update participant status
            $participant->startQuiz();

            // Create exam attempt
            $attempt = ExamAttempt::create([
                'exam_id' => $quiz->id,
                'student_id' => $student->user_id,
                'started_at' => now(),
                'remaining_time' => $quiz->getTotalQuizTime() ?? 0,
                'status' => 'in_progress',
                'exam_settings' => $quiz->getAllSettings(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz dimulai',
                'attempt_id' => $attempt->id,
                'total_time' => $quiz->getTotalQuizTime(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit quiz attempt
     */
    public function submitQuizAttempt(Request $request, Exam $quiz, ExamAttempt $attempt)
    {
        $student = Auth::user()->student;

        if ($attempt->student_id !== $student->user_id || $attempt->exam_id !== $quiz->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Update attempt
            $attempt->update([
                'ended_at' => now(),
                'status' => 'submitted',
            ]);

            // Update participant
            $session = $quiz->activeSession;
            if ($session) {
                $participant = QuizParticipant::where([
                    'quiz_session_id' => $session->id,
                    'student_id' => $student->user_id
                ])->first();

                if ($participant) {
                    $participant->submitQuiz();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quiz berhasil disubmit',
                'redirect' => route('quiz.result', ['quiz' => $quiz->id, 'attempt' => $attempt->id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /quiz/{quiz}/room/student
     * Halaman untuk siswa masuk ke ruangan quiz
     */
    public function joinQuizRoomPage($quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            $quiz = Exam::with(['class', 'subject', 'activeSession'])
                ->where('type', 'QUIZ')
                ->findOrFail($quizId);

            // Validasi akses
            if ($quiz->class_id != $student->class_id) {
                return redirect()->route('quiz.index')
                    ->with('error', 'Quiz tidak tersedia untuk kelas Anda.');
            }

            $session = $quiz->activeSession;
            $participant = null;

            if ($session) {
                $participant = QuizParticipant::where([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id
                ])->first();
            }

            // ✅ PERBAIKAN: HAPUS REDIRECT LOOP KE PLAY
            // Biarkan siswa tetap di room sampai mereka klik tombol untuk mulai
            // atau sampai JavaScript mendeteksi quiz sudah dimulai

            // JavaScript di room.blade.php akan handle polling dan redirect otomatis

            return view('quiz.room', compact('quiz', 'session', 'participant'));
        } catch (\Exception $e) {
            Log::error('Error in joinQuizRoomPage: ' . $e->getMessage());
            return redirect()->route('quiz.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk siswa bergabung ke ruangan quiz
     */
    public function joinQuizRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 422);
            }

            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak tersedia untuk kelas Anda.'
                ], 403);
            }

            // Cek apakah quiz aktif
            if ($quiz->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak aktif.'
                ], 422);
            }

            // Cek apakah ruangan terbuka
            if (!$quiz->is_room_open) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan quiz belum dibuka oleh guru.'
                ], 422);
            }

            // Cek apakah quiz sudah dimulai
            if ($quiz->is_quiz_started) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz sudah dimulai. Tidak bisa bergabung sekarang.'
                ], 422);
            }

            DB::beginTransaction();

            // Get session
            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi quiz tidak ditemukan.'
                ], 422);
            }

            // Cek apakah sudah join
            $participant = QuizParticipant::where([
                'quiz_session_id' => $session->id,
                'student_id' => $user->id
            ])->first();

            if (!$participant) {
                // Buat peserta baru
                $participant = QuizParticipant::create([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id,
                    'exam_id' => $quiz->id,
                    'status' => 'waiting',
                    'joined_at' => now(),
                    'is_present' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ]);
            } else {
                // Update status jika sudah ada
                $participant->update([
                    'is_present' => true,
                    'joined_at' => now(),
                    'status' => 'waiting'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil bergabung ke ruangan quiz!',
                'participant_status' => 'waiting',
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in joinQuizRoom: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark student as ready in quiz room
     * POST /quiz/{quiz}/room/mark-ready
     */
    public function markAsReady(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 422);
            }

            $quiz = Exam::findOrFail($quizId);

            // Validasi akses
            if ($quiz->class_id != $student->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak tersedia untuk kelas Anda.'
                ], 403);
            }

            // Cek apakah quiz aktif
            if ($quiz->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz tidak aktif.'
                ], 422);
            }

            // Cek apakah ruangan terbuka
            if (!$quiz->is_room_open) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan quiz belum dibuka.'
                ], 422);
            }

            DB::beginTransaction();

            // Get session
            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi quiz tidak ditemukan.'
                ], 422);
            }

            // Cari participant
            $participant = QuizParticipant::where([
                'quiz_session_id' => $session->id,
                'student_id' => $user->id
            ])->first();

            if (!$participant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum bergabung ke ruangan quiz.'
                ], 422);
            }

            // Update status menjadi ready
            $participant->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status Anda telah diubah menjadi Siap!',
                'participant_status' => 'ready'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in markAsReady: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
