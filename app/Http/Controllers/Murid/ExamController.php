<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    /**
     * GET /soal
     * Tampilkan daftar soal untuk siswa
     */
    /**
     * GET /soal
     * Tampilkan daftar soal untuk siswa
     */
    public function indexSoal(Request $request)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            // Validasi siswa
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

            // Query dasar - hanya exam aktif untuk kelas siswa
            $query = Exam::with([
                'subject',
                'teacher.user',
                'questions'
            ])
                ->where('class_id', $classId)
                ->where('status', 'active');

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
                    ->where('student_id', $user->id)
                    ->latest()
                    ->first();

                // Hitung jumlah percobaan yang sudah submit
                $exam->attempt_count = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
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
            \Log::error('Error in indexSoal: ' . $e->getMessage(), [
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
            \Log::info('Student data:', [
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
                \Log::error('Exam not found', ['exam_id' => $examId]);
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }

            // Debug: Log exam data
            \Log::info('Exam data:', [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'class_id' => $exam->class_id,
                'student_class_id' => $student->class_id,
                'status' => $exam->status
            ]);

            // Cek apakah exam untuk kelas siswa
            if ($exam->class_id != $student->class_id) {
                \Log::warning('Class mismatch', [
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

            // Cek waktu akses dengan metode yang lebih sederhana
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
            \Log::error('Error in showDetail: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * POST /soal/{exam}/start
     * Mulai attempt baru
     */
    /**
     * POST /soal/{exam}/start
     * Mulai attempt baru
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
            \Log::info('Start exam attempt', [
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

            \Log::info('Exam attempt created', [
                'attempt_id' => $attempt->id,
                'exam_id' => $examId,
                'student_id' => $user->id
            ]);

            return redirect()->route('soal.kerjakan', $examId)
                ->with('success', 'Ujian dimulai! Anda memiliki ' . $exam->duration . ' menit.')
                ->with('require_fullscreen', $exam->fullscreen_mode);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error starting exam: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('soal.detail', $examId)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /soal/{exam}/kerjakan
     * Halaman mengerjakan soal
     */
    /**
     * GET /soal/{exam}/kerjakan
     * Halaman mengerjakan soal
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

            // Format Pertanyaan (PENTING: Gunakan values() agar jadi Array di JSON, bukan Object)
            $questions = $exam->questions->map(function ($question) {
                $options = [];
                if (($question->type === 'PG' || $question->type === 'multiple_choice') && $question->choices->isNotEmpty()) {
                    foreach ($question->choices->sortBy('order') as $choice) {
                        $options[$choice->label] = $choice->text; // Label A, B, C sebagai Key
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
                    // Kita perlu label (A,B,C) untuk frontend, cari label dari choice_id
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

            // Marked for review (default kosong, bisa disimpan di DB nanti)
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
            \Log::error('Error in attemptFromSession: ' . $e->getMessage());
            return redirect()->route('soal.index')->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    /**
     * POST /soal/{exam}/submit
     * Submit jawaban
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
            $showResult = $request->input('show_result', 0); // Tambahkan ini

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
     * Tampilkan hasil ujian
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

    /**
     * POST /soal/{exam}/violation
     * Log pelanggaran
     */
    public function logViolation(Request $request, $examId)
    {
        $request->validate([
            'type' => 'required|string',
            'count' => 'required|integer|min:1',
        ]);

        try {
            $user = Auth::user();

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada attempt yang sedang berjalan'
                ], 404);
            }

            // Update violation count
            $attempt->violation_count = $request->count;

            // Save violation log
            $log = $attempt->violation_log ?? [];
            $log[] = [
                'type' => $request->type,
                'count' => $request->count,
                'timestamp' => now()->toDateTimeString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];
            $attempt->violation_log = $log;

            // Jika mencapai batas, force submit
            $exam = Exam::find($examId);
            $violationLimit = $exam->violation_limit ?? 3;

            if ($request->count >= $violationLimit) {
                $attempt->is_cheating_detected = true;
                $attempt->save();

                $this->forceSubmitDueToViolation($attempt);

                return response()->json([
                    'success' => true,
                    'message' => 'Attempt disubmit karena pelanggaran',
                    'force_submit' => true,
                    'redirect_url' => route('soal.hasil', [
                        'exam' => $examId,
                        'attempt' => $attempt->id
                    ])
                ]);
            }

            $attempt->save();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran dicatat',
                'violation_count' => $attempt->violation_count
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * Helper: Get exam status
     */
    private function getExamStatus($exam, $attempt)
    {
        if (!$exam) return 'not_available';

        // Cek jika sudah melewati deadline
        if ($exam->end_at && now() > $exam->end_at) {
            return 'expired';
        }

        // Cek jika belum dimulai
        if ($exam->start_at && now() < $exam->start_at) {
            return 'upcoming';
        }

        // Cek berdasarkan attempt
        if ($attempt) {
            if ($attempt->status === 'submitted' || $attempt->status === 'timeout') {
                return 'completed';
            } elseif ($attempt->status === 'in_progress') {
                return 'ongoing';
            }
        }

        // Default: tersedia untuk dikerjakan
        return 'available';
    }

    /**
     * Helper: Force submit karena timeout
     */
    private function forceSubmitDueToTimeout($attempt)
    {
        try {
            DB::beginTransaction();

            $attempt->status = 'timeout';
            $attempt->ended_at = now();
            $attempt->save();

            // Bersihkan session
            session()->forget([
                'current_attempt_' . $attempt->exam_id,
                'fullscreen_required_' . $attempt->exam_id,
                'exam_started_' . $attempt->exam_id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in forceSubmitDueToTimeout: ' . $e->getMessage());
        }
    }

    public function forceSubmitViolation(Request $request, $examId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Validasi input
            $validated = $request->validate([
                'answers' => 'required|json',
                'violation_count' => 'required|integer|min:1',
                'attempt_id' => 'required|exists:exam_attempts,id',
            ]);

            $answers = json_decode($validated['answers'], true);

            // Ambil attempt
            $attempt = ExamAttempt::where('id', $validated['attempt_id'])
                ->where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->firstOrFail();

            // Pastikan attempt masih in_progress
            if ($attempt->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt sudah disubmit sebelumnya'
                ], 400);
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

            // Hitung skor
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Update attempt
            $attempt->status = 'submitted';
            $attempt->ended_at = now();
            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->is_cheating_detected = true;
            $attempt->violation_count = $validated['violation_count'];
            $attempt->save();

            // Bersihkan session
            $this->clearExamSession($examId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil disubmit karena pelanggaran',
                'redirect_url' => route('soal.hasil', [
                    'exam' => $examId,
                    'attempt' => $attempt->id
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in forceSubmitViolation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Helper: Force submit karena pelanggaran
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
            session()->forget([
                'current_attempt_' . $attempt->exam_id,
                'fullscreen_required_' . $attempt->exam_id,
                'exam_started_' . $attempt->exam_id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in forceSubmitDueToViolation: ' . $e->getMessage());
        }
    }

    public function active()
    {
        $student = Auth::user()->student;

        $exams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->where('start_at', '<=', now()) // ← ERROR DI SINI
            ->where('end_at', '>=', now())
            ->with(['subject', 'class'])
            ->orderBy('end_at', 'asc')
            ->get();

        return view('Murid.Exam.active', compact('exams'));
    }

    public function upcoming()
    {
        $student = Auth::user()->student;

        $exams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->where('start_at', '>', now()) // ← Pastikan tidak ada `column:`
            ->with(['subject', 'class'])
            ->orderBy('start_at', 'asc')
            ->get();

        return view('Murid.Exam.upcoming', compact('exams'));
    }

    public function completed()
    {
        $student = Auth::user()->student;

        $exams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->where('end_at', '<', now()) // ← Pastikan tidak ada `column:`
            ->with(['subject', 'class'])
            ->orderBy('end_at', 'desc')
            ->get();

        return view('Murid.Exam.completed', compact('exams'));
    }

    // Di controller
    public function show($id)
    {
        try {
            $exam = Exam::with(['questions' => function ($query) {
                $query->orderBy('order');
            }, 'subject'])->findOrFail($id);

            $questions = $exam->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_image' => $question->question_image,
                    'type' => $question->type,
                    'options' => $question->type === 'PG' ? json_decode($question->options, true) : null,
                    'order' => $question->order
                ];
            });

            // Hitung waktu tersisa
            $timeRemaining = $exam->duration * 60; // konversi menit ke detik

            return view('siswa.exam.show', [
                'exam' => $exam,
                'questions' => $questions,
                'timeRemaining' => $timeRemaining,
                'violationCount' => 0, // atau ambil dari session/DB
                'answers' => [],
                'essayAnswers' => [],
                'markedForReview' => []
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat ujian: ' . $e->getMessage());
        }
    }

    public function directAttempt($examId)
    {
        $user = Auth::user();

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($attempt) {
            return redirect()->route('soal.kerjakan', $examId);
        }

        return redirect()->route('soal.detail', $examId)
            ->with('error', 'Tidak ada ujian yang sedang berjalan.');
    }

    public function continueAttempt($id)
    {
        $exam = Exam::findOrFail($id);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        return redirect()->route('murid.exams.attempt', ['exam' => $exam->id, 'attempt' => $attempt->id]);
    }

    public function attempt($examId, $attemptId)
    {
        $user = Auth::user();

        $exam = Exam::with(['questions' => function ($query) {
            $query->with(['choices' => function ($q) {
                $q->orderBy('order');
            }]);
        }])->findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->findOrFail($attemptId);

        if ($attempt->student_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke attempt ini');
        }

        $timeElapsed = $attempt->getTimeElapsed();
        $timeRemaining = $exam->duration * 60 - $timeElapsed;

        if ($timeRemaining <= 0) {
            $attempt->timeout();
            return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                ->with('error', 'Waktu ujian telah habis');
        }

        $answers = ExamAnswer::where('attempt_id', $attempt->id)
            ->pluck('choice_id', 'question_id')
            ->toArray();

        $answeredCount = count($answers);

        $settings = $attempt->exam_settings;

        $questions = $exam->questions;

        if (!empty($settings['shuffle_question'])) {
            $questions = $questions->shuffle();
        }

        $questions = $exam->questions->map(function ($question) use ($answers) {
            $options = [];

            if ($question->type === 'PG' && $question->choices->isNotEmpty()) {
                $choices = $question->choices;

                if ($question->randomize_choices) {
                    $choices = $choices->shuffle();
                }

                foreach ($choices as $choice) {
                    $options[$choice->id] = $choice->text;
                }
            }

            return [
                'id' => $question->id,
                'question_text' => nl2br(e($question->question)),
                'type' => $question->type,
                'score' => $question->score,
                'options' => $options,
                'selected_answer' => $answers[$question->id] ?? null,
                'settings' => [
                    'enable_timer' => $question->enable_timer ?? false,
                    'time_limit' => $question->time_limit ?? null,
                    'enable_skip' => $question->enable_skip ?? true,
                    'show_explanation' => $question->show_explanation ?? false,
                    'enable_mark_review' => $question->enable_mark_review ?? true,
                    'randomize_choices' => $question->randomize_choices ?? false,
                    'require_all_options' => $question->require_all_options ?? false,
                ]
            ];
        });

        $securitySettings = [
            'fullscreen_mode' => (bool) $exam->fullscreen_mode,
            'block_new_tab' => (bool) $exam->block_new_tab,
            'prevent_copy_paste' => (bool) $exam->prevent_copy_paste,
            'allow_copy' => (bool) $exam->allow_copy,
            'allow_screenshot' => (bool) $exam->allow_screenshot,
            'shuffle_question' => (bool) $exam->shuffle_question,
            'shuffle_answer' => (bool) $exam->shuffle_answer,
            'auto_submit' => (bool) $exam->auto_submit,
            'enable_proctoring' => (bool) $exam->enable_proctoring,
            'require_camera' => (bool) $exam->require_camera,
            'require_mic' => (bool) $exam->require_mic,
            'violation_limit' => $exam->violation_limit ?? 3,
        ];

        if (empty($attempt->exam_settings)) {
            $attempt->update(['exam_settings' => $securitySettings]);
        }

        return view('murid.exams.attempt', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
            'answers' => $answers,
            'answeredCount' => $answeredCount,
            'timeRemaining' => $timeRemaining,
            'securitySettings' => $securitySettings,
            'requireFullscreen' => (bool) $exam->fullscreen_mode,
        ]);
    }

    public function saveAnswer(Request $request, $examId)
    {
        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'answer' => 'nullable',
            'choice_id' => 'nullable|exists:exam_choices,id',
            'time_taken' => 'nullable|integer',
        ]);

        $exam = Exam::findOrFail($examId);
        $question = ExamQuestion::findOrFail($request->question_id);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $answer = ExamAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $question->id)
                ->first();

            if (!$answer) {
                $answer = new ExamAnswer();
                $answer->exam_id = $examId;
                $answer->question_id = $question->id;
                $answer->student_id = Auth::id();
                $answer->attempt_id = $attempt->id;
            }

            if ($question->type === 'multiple_choice') {
                $answer->choice_id = $request->choice_id;
                $answer->answer_text = null;
            } else {
                $answer->choice_id = null;
                $answer->answer_text = $request->answer;
            }

            $answer->time_taken = $request->time_taken;
            $answer->answered_at = now();

            if ($question->type === 'multiple_choice' && $request->choice_id) {
                $correctChoice = $question->choices()->where('is_correct', true)->first();
                if ($correctChoice && $correctChoice->id == $request->choice_id) {
                    $answer->score = $question->score;
                    $answer->is_correct = true;
                } else {
                    $answer->score = 0;
                    $answer->is_correct = false;
                }
            } else {
                $answer->score = 0;
                $answer->is_correct = false;
            }

            $answer->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function autoSave(Request $request, $examId)
    {
        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        if ($request->has('remaining_time')) {
            $attempt->remaining_time = $request->remaining_time;
            $attempt->save();
        }

        return response()->json(['success' => true]);
    }

    public function review($examId, $attemptId)
    {
        $exam = Exam::findOrFail($examId);

        $attempt = ExamAttempt::with(['answers.question.choices'])
            ->where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->findOrFail($attemptId);

        if (!$exam->show_correct_answer) {
            abort(403, 'Review jawaban tidak diizinkan.');
        }

        return view('Murid.Exam.review', compact('exam', 'attempt'));
    }

    public function heartbeat(Request $request, $examId)
    {
        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        $attempt->touch();

        return response()->json([
            'success' => true,
            'remaining_time' => $attempt->getTimeRemaining(),
            'status' => $attempt->status
        ]);
    }


    // Tambahkan method ini di controller
    public function handleViolationSubmit(Request $request, $examId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.index')
                    ->with('error', 'Tidak ada ujian yang sedang berjalan.');
            }

            // Simpan semua jawaban yang belum disimpan
            if ($request->has('answers')) {
                $answers = json_decode($request->answers, true);

                $exam = Exam::with(['questions.choices'])->findOrFail($examId);

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

                    if ($question->type === 'PG') {
                        $choice = $question->choices->firstWhere('label', $answerValue);
                        if ($choice) {
                            $examAnswer->choice_id = $choice->id;
                            $examAnswer->answer_text = $choice->text;
                            $examAnswer->is_correct = $choice->is_correct;
                            $examAnswer->score = $choice->is_correct ? $question->score : 0;
                        }
                    } else {
                        $examAnswer->answer_text = $answerValue;
                        $examAnswer->score = 0;
                    }

                    $examAnswer->answered_at = now();
                    $examAnswer->save();
                }
            }

            // Update attempt status
            $attempt->status = 'submitted';
            $attempt->is_cheating_detected = true;
            $attempt->ended_at = now();
            $attempt->violation_count = 3;

            // Hitung skor akhir
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            // Clear session dengan benar
            $this->clearExamSession($examId);

            DB::commit();

            // Redirect tanpa mengandalkan session flash
            return redirect()->route('soal.hasil', [
                'exam' => $examId,
                'attempt' => $attempt->id,
                'auto_submitted' => 1
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in handleViolationSubmit: ' . $e->getMessage());

            // Fallback redirect
            return redirect()->route('soal.index')
                ->with('error', 'Ujian telah disubmit karena pelanggaran.');
        }
    }

    // Helper method untuk membersihkan session
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

        // Regenerate session untuk mencegah expired
        session()->regenerate();
    }

    private function forceSubmitAllAttempts($examId, $userId)
    {
        try {
            $attempts = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $userId)
                ->where('status', 'in_progress')
                ->get();

            foreach ($attempts as $attempt) {
                $this->forceSubmitDueToTimeout($attempt);
            }
        } catch (\Exception $e) {
            \Log::error('Error in forceSubmitAllAttempts: ' . $e->getMessage());
        }
    }
}
