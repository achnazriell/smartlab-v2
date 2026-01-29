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

class ExamController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        if (!$student || !$student->class_id) {
            return view('Murid.Exam.no-class');
        }

        $exams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->with(['subject', 'class'])
            ->where(function ($query) {
                $query->where('end_at', '>', now())
                    ->orWhereNull('end_at');
            })
            ->orderBy('start_at', 'asc')
            ->get();

        $activeExams = $exams->filter(function ($exam) {
            return $exam->isOngoing();
        });

        $upcomingExams = $exams->filter(function ($exam) {
            return $exam->is_upcoming;
        });

        $completedExams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->where('end_at', '<', now())
            ->with(['subject', 'class'])
            ->get();

        return view('Murid.Exam.index', compact(
            'activeExams',
            'upcomingExams',
            'completedExams'
        ));
    }

    public function indexSoal(Request $request)
    {
        $student = Auth::user()->student;

        if (!$student || !$student->class_id) {
            return view('Murid.Exam.no-class');
        }

        $query = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->with(['subject', 'class', 'teacher.user'])
            ->withCount('questions');

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhereHas('subject', function ($q) use ($request) {
                    $q->where('name_subject', 'like', '%' . $request->search . '%');
                });
        }

        if ($request->has('status')) {
            $now = now();
            switch ($request->status) {
                case 'belum_dikerjakan':
                    $query->whereDoesntHave('attempts', function ($q) {
                        $q->where('student_id', Auth::id())
                            ->whereIn('status', ['submitted', 'timeout']);
                    });
                    break;
                case 'sudah_dikerjakan':
                    $query->whereHas('attempts', function ($q) {
                        $q->where('student_id', Auth::id())
                            ->whereIn('status', ['submitted', 'timeout']);
                    });
                    break;
                case 'kadaluarsa':
                    $query->where('end_at', '<', $now);
                    break;
            }
        }

        $exams = $query->orderBy('start_at', 'asc')->paginate(12);

        $exams->getCollection()->transform(function ($exam) {
            $lastAttempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', Auth::id())
                ->latest()
                ->first();

            $exam->attempt = $lastAttempt;

            if ($lastAttempt) {
                if ($lastAttempt->status == 'in_progress') {
                    $exam->status = 'ongoing';
                } elseif (in_array($lastAttempt->status, ['submitted', 'timeout'])) {
                    $exam->status = 'completed';
                }
            } elseif ($exam->end_at && now()->gt($exam->end_at)) {
                $exam->status = 'expired';
            } elseif ($exam->start_at && now()->lt($exam->start_at)) {
                $exam->status = 'upcoming';
            } else {
                $exam->status = 'available';
            }

            return $exam;
        });

        return view('Murid.Exam.soal', compact('exams'));
    }

    public function active()
    {
        $student = Auth::user()->student;

        $exams = Exam::where('class_id', $student->class_id)
            ->where('status', 'active')
            ->where('start_at', '<=', now())
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
            ->where('start_at', '>', now())
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
            ->where('end_at', '<', now())
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

    public function start(Request $request, $examId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $student = $user->student;

            if (!$student || !$student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            $exam = Exam::findOrFail($examId);

            if ($exam->class_id !== $student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }

            $existingAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($existingAttempt) {
                DB::commit();
                return redirect()->route('soal.kerjakan', $examId)
                    ->with('info', 'Mengalihkan ke ujian yang sedang berjalan.');
            }

            $attemptCount = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            if ($exam->limit_attempts && $attemptCount >= $exam->limit_attempts) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Anda telah mencapai batas maksimal percobaan.');
            }

            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum dimulai. Mulai: ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($exam->end_at && $now > $exam->end_at) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian sudah berakhir. Berakhir: ' . $exam->end_at->format('d M Y H:i'));
            }

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $user->id,
                'started_at' => now(),
                'remaining_time' => $exam->duration * 60,
                'status' => 'in_progress',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'exam_settings' => $exam->getAllSettings(),
            ]);

            session([
                'current_attempt_' . $examId => $attempt->id,
                'fullscreen_required_' . $examId => $exam->fullscreen_mode,
                'exam_started_' . $examId => true,
            ]);

            DB::commit();

            return redirect()->route('soal.kerjakan', $examId)
                ->with('success', 'Ujian dimulai! Anda memiliki ' . $exam->duration . ' menit.')
                ->with('require_fullscreen', $exam->fullscreen_mode);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error starting exam: ' . $e->getMessage());

            return redirect()->route('soal.detail', $examId)
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
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

    public function attemptFromSession($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student || !$student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan atau belum memiliki kelas.');
            }

            $exam = Exam::with(['questions' => function ($query) {
                $query->with(['choices' => function ($q) {
                    $q->orderBy('order');
                }]);
            }])
                ->where('id', $examId)
                ->where('class_id', $student->class_id)
                ->where('status', 'active')
                ->firstOrFail();

            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum dimulai. Mulai: ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($exam->end_at && $now > $exam->end_at) {
                $this->forceSubmitAllAttempts($examId, $user->id);

                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian sudah berakhir. Berakhir: ' . $exam->end_at->format('d M Y H:i'));
            }

            // CEK APAKAH SUDAH MELANGGAR 3x DAN DITUTUP
            $attempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                // Cek apakah sudah pernah melanggar 3x
                $previousViolation = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->where('is_cheating_detected', true)
                    ->where('violation_count', '>=', 3)
                    ->latest()
                    ->first();

                if ($previousViolation) {
                    // Jika sudah 3x pelanggaran, redirect ke hasil
                    return redirect()->route('soal.hasil', [
                        'exam' => $examId,
                        'attempt' => $previousViolation->id,
                        'violation' => 'max'
                    ])->with('error', 'Anda telah mencapai batas maksimal pelanggaran.');
                }

                $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                if ($exam->limit_attempts && $attemptCount >= $exam->limit_attempts) {
                    return redirect()->route('soal.detail', $examId)
                        ->with('error', 'Anda telah mencapai batas maksimal percobaan.');
                }

                $lastAttempt = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->latest()
                    ->first();

                if ($lastAttempt && ($lastAttempt->isSubmitted() || $lastAttempt->isTimeout())) {
                    if ($exam->limit_attempts > 1 && $attemptCount < $exam->limit_attempts) {
                        $attempt = ExamAttempt::create([
                            'exam_id' => $exam->id,
                            'student_id' => $user->id,
                            'started_at' => now(),
                            'remaining_time' => $exam->duration * 60,
                            'status' => 'in_progress',
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'exam_settings' => $exam->getAllSettings(),
                        ]);
                    } else {
                        return redirect()->route('soal.detail', $examId)
                            ->with('error', 'Anda sudah menyelesaikan ujian ini.');
                    }
                } else {
                    $attempt = ExamAttempt::create([
                        'exam_id' => $exam->id,
                        'student_id' => $user->id,
                        'started_at' => now(),
                        'remaining_time' => $exam->duration * 60,
                        'status' => 'in_progress',
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'exam_settings' => $exam->getAllSettings(),
                    ]);
                }
            }

            session([
                'current_attempt_' . $examId => $attempt->id,
                'fullscreen_required_' . $examId => $exam->fullscreen_mode,
                'exam_started_' . $examId => true,
            ]);

            $timeElapsed = $attempt->getTimeElapsed();
            $timeRemaining = $exam->duration * 60 - $timeElapsed;

            if ($timeRemaining <= 0) {
                $this->forceSubmitDueToTimeout($attempt);
                return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                    ->with('error', 'Waktu ujian telah habis');
            }

            $attempt->update(['remaining_time' => $timeRemaining]);

            // PERBAIKAN: Ambil jawaban dengan benar
            $answers = ExamAnswer::where('attempt_id', $attempt->id)
                ->get()
                ->keyBy('question_id')
                ->map(function ($item) {
                    // Untuk PG, simpan choice_id atau label
                    if ($item->choice_id) {
                        return $item->choice_id;
                    }
                    // Untuk essay, simpan text
                    return $item->answer_text;
                })
                ->toArray();

            // PERBAIKAN: Format questions dengan benar
            $questions = $exam->questions->map(function ($question) use ($answers) {
                $options = [];

                if (($question->type === 'PG' || $question->type === 'multiple_choice') && $question->choices->isNotEmpty()) {
                    $choices = $question->choices->sortBy('order');

                    foreach ($choices as $choice) {
                        $options[$choice->label] = $choice->text; // Gunakan label (A, B, C) sebagai key
                    }
                }

                $selectedAnswer = $answers[$question->id] ?? null;

                return [
                    'id' => $question->id,
                    'question_text' => $question->question,
                    'question_image' => $question->question_image,
                    'type' => $question->type,
                    'score' => $question->score,
                    'options' => $options,
                    'selected_answer' => $selectedAnswer,
                    'order' => $question->order
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

            return view('murid.exams.attempt', [
                'exam' => $exam,
                'questions' => $questions,
                'attempt' => $attempt,
                'timeRemaining' => $timeRemaining,
                'answeredCount' => count(array_filter($answers)),
                'securitySettings' => $securitySettings,
                'requireFullscreen' => (bool) $exam->fullscreen_mode,
                'attemptId' => $attempt->id,
                'answers' => $answers,
                'essayAnswers' => ExamAnswer::where('attempt_id', $attempt->id)
                    ->whereNotNull('answer_text')
                    ->pluck('answer_text', 'question_id')
                    ->toArray(),
                'markedForReview' => [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in attemptFromSession: ' . $e->getMessage());
            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function submit(Request $request, $examId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $request->validate([
                'answers' => 'required|json',
            ]);

            $answers = json_decode($request->answers, true);

            // --- TAMBAHKAN KODE INI ---
            // Cek apakah ini submit paksa karena pelanggaran
            $isForcedViolation = $request->input('force_submit_violation') == 'true';
            $violationCount = $request->input('violation_count', 0);
            // --------------------------

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.index')
                    ->with('error', 'Tidak ada ujian yang sedang berjalan.');
            }

            // ... (Kode loop penyimpanan jawaban biarkan sama seperti sebelumnya) ...
            $exam = Exam::with(['questions.choices' => function ($query) {
                $query->orderBy('order');
            }])->findOrFail($examId);

            foreach ($answers as $questionId => $answerValue) {
                // ... (Logika simpan jawaban tetap sama) ...
                $question = $exam->questions->where('id', $questionId)->first();
                if (!$question) continue;

                // Copy paste logika penyimpanan jawaban Anda di sini
                // (Kode Anda sudah benar untuk bagian ini, tidak perlu diubah)

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

                // ... Logic PG/Essay ...
                if ($question->type === 'PG' || $question->type === 'multiple_choice') {
                    $choice = $question->choices->firstWhere('label', $answerValue);

                    if ($choice) {
                        $examAnswer->choice_id = $choice->id;
                        $examAnswer->answer_text = $choice->text;
                        $examAnswer->is_correct = $choice->is_correct; // Pastikan ini diset
                        $examAnswer->score = $choice->is_correct ? $question->score : 0;
                    } else {
                        $examAnswer->choice_id = null;
                        $examAnswer->answer_text = $answerValue;
                        $examAnswer->score = 0;
                        $examAnswer->is_correct = false;
                    }
                } else {
                    $examAnswer->choice_id = null;
                    $examAnswer->answer_text = $answerValue;
                    $examAnswer->score = 0;
                    $examAnswer->is_correct = false;
                }
                $examAnswer->answered_at = now();
                $examAnswer->save();
            }

            // Hitung Score Akhir
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Update Attempt Status
            $attempt->status = 'submitted';
            $attempt->ended_at = now();
            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;

            // --- TAMBAHKAN KODE INI ---
            // Tandai kecurangan jika submit dipaksa
            if ($isForcedViolation) {
                $attempt->is_cheating_detected = true;
                $attempt->violation_count = $violationCount;
            }
            // --------------------------

            $attempt->save();

            // Bersihkan Session
            session()->forget([
                'current_attempt_' . $examId,
                'fullscreen_required_' . $examId,
                'exam_started_' . $examId,
            ]);

            // Regenerate session di sini (aman karena proses submit sudah selesai)
            session()->regenerate();

            DB::commit();

            return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                ->with('success', $isForcedViolation ? 'Ujian dihentikan karena pelanggaran.' : 'Jawaban berhasil dikumpulkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error submitting exam: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function result($examId, $attemptId)
    {
        try {
            $user = Auth::user();

            $exam = Exam::findOrFail($examId);

            $attempt = ExamAttempt::with(['answers.question.choices'])
                ->where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->findOrFail($attemptId);

            $allow_retake = false;

            if ($exam->limit_attempts > 1) {
                $attemptCount = ExamAttempt::where('exam_id', $examId)
                    ->where('student_id', $user->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                $allow_retake = $attemptCount < $exam->limit_attempts;
            }

            $totalQuestions = $exam->questions()->count();
            $answeredQuestions = $attempt->answers()->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $incorrectAnswers = $answeredQuestions - $correctAnswers;

            $percentage = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

            $isPassed = false;
            if ($exam->min_pass_grade) {
                $isPassed = $attempt->final_score >= $exam->min_pass_grade;
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('soal.index')
                ->with('error', 'Hasil ujian tidak ditemukan.');
        } catch (\Exception $e) {
            \Log::error('Error viewing result: ' . $e->getMessage());
            return redirect()->route('soal.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];
            $attempt->violation_log = $log;

            // SIMPAN DULU sebelum cek batas pelanggaran
            $attempt->save();

            // Jika pelanggaran mencapai batas
            if ($request->count >= 10) {
                // Lakukan submit di background
                $this->forceSubmitDueToViolation($attempt);

                // Kembalikan attempt yang sudah diupdate
                $attempt->refresh();

                // Generate URL yang aman
                $redirectUrl = route('soal.hasil', [
                    'exam' => $examId,
                    'attempt' => $attempt->id,
                    'violation' => 'max',
                    't' => time() // Anti cache
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Attempt disubmit karena pelanggaran',
                    'violation_count' => $attempt->violation_count,
                    'force_submit' => true,
                    'redirect_url' => $redirectUrl
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Violation logged',
                'violation_count' => $attempt->violation_count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error logging violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    private function forceSubmitDueToViolation($attempt)
    {
        try {
            DB::beginTransaction();

            $attempt->status = 'submitted';
            $attempt->is_cheating_detected = true;
            $attempt->ended_at = now();

            // Hitung score
            $exam = Exam::with('questions')->find($attempt->exam_id);
            $answers = ExamAnswer::where('attempt_id', $attempt->id)->get();

            $totalScore = 0;
            foreach ($answers as $answer) {
                $question = $exam->questions->firstWhere('id', $answer->question_id);
                if ($question && ($question->type === 'PG' || $question->type === 'multiple_choice') && $answer->choice_id) {
                    $correctChoice = $question->choices->firstWhere('is_correct', true);
                    if ($correctChoice && $correctChoice->id == $answer->choice_id) {
                        $answer->score = $question->score;
                        $answer->is_correct = true;
                        $totalScore += $question->score;
                    } else {
                        $answer->score = 0;
                        $answer->is_correct = false;
                    }
                    $answer->save();
                }
            }

            $maxScore = $exam ? $exam->questions->sum('score') : 0;
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            // Hapus session exam saja
            session()->forget([
                'current_attempt_' . $attempt->exam_id,
                'fullscreen_required_' . $attempt->exam_id,
                'exam_started_' . $attempt->exam_id,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in forceSubmitDueToViolation: ' . $e->getMessage());
        }
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
