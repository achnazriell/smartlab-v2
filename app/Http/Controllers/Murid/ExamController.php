<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\Student;
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

        // Pisahkan berdasarkan status
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

    public function show($id)
    {
        $exam = Exam::with(['subject', 'class', 'questions'])
            ->findOrFail($id);

        $student = Auth::user()->student;

        // Cek apakah siswa berada di kelas yang benar
        if ($exam->class_id !== $student->class_id) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        // Cek apakah ujian aktif
        if (!$exam->isOngoing() && !$exam->is_upcoming && !$exam->isFinished()) {
            abort(404, 'Ujian tidak tersedia.');
        }

        // Cek attempt sebelumnya
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', Auth::id())
            ->latest()
            ->first();

        $canStart = true;
        $message = null;

        if ($attempt) {
            if ($attempt->isSubmitted() || $attempt->isTimeout()) {
                if ($exam->limit_attempts > 1) {
                    $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                        ->where('student_id', Auth::id())
                        ->whereIn('status', ['submitted', 'timeout'])
                        ->count();

                    if ($attemptCount >= $exam->limit_attempts) {
                        $canStart = false;
                        $message = 'Anda telah mencapai batas percobaan.';
                    }
                } else {
                    $canStart = false;
                    $message = 'Anda sudah menyelesaikan ujian ini.';
                }
            } elseif ($attempt->isInProgress()) {
                // Bisa melanjutkan
                $canStart = true;
            }
        }

        return view('Murid.Exam.show', compact('exam', 'attempt', 'canStart', 'message'));
    }

    public function start(Request $request, $examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student || !$student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // Cari exam
            $exam = Exam::findOrFail($examId);

            // Validasi akses
            if ($exam->class_id !== $student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }

            // Cek apakah sudah ada attempt yang sedang berjalan
            $existingAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($existingAttempt) {
                // Redirect ke halaman mengerjakan
                return redirect()->route('soal.kerjakan', $examId);
            }

            // Cek apakah sudah mencapai limit attempts
            $attemptCount = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            if ($exam->limit_attempts && $attemptCount >= $exam->limit_attempts) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Anda telah mencapai batas maksimal percobaan.');
            }

            // Cek waktu ujian
            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum dimulai. Mulai: ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($exam->end_at && $now > $exam->end_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian sudah berakhir. Berakhir: ' . $exam->end_at->format('d M Y H:i'));
            }

            // Buat attempt baru
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $user->id,
                'started_at' => now(),
                'remaining_time' => $exam->duration * 60,
                'status' => 'in_progress',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Simpan attempt_id di session
            session(['current_attempt_' . $examId => $attempt->id]);

            return redirect()->route('soal.kerjakan', $examId)
                ->with('success', 'Ujian dimulai! Anda memiliki ' . $exam->duration . ' menit.');
        } catch (\Exception $e) {
            \Log::error('Error starting exam: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

            // Cari exam
            $exam = Exam::with(['questions.choices'])
                ->where('id', $examId)
                ->where('class_id', $student->class_id)
                ->where('status', 'active')
                ->firstOrFail();

            // Cek waktu ujian
            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum dimulai. Mulai: ' . $exam->start_at->format('d M Y H:i'));
            }

            if ($exam->end_at && $now > $exam->end_at) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian sudah berakhir. Berakhir: ' . $exam->end_at->format('d M Y H:i'));
            }

            // Cari atau buat attempt
            $attempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            // Jika tidak ada attempt yang sedang berjalan, coba buat baru
            if (!$attempt) {
                // Cek apakah sudah mencapai batas attempt
                $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                if ($exam->limit_attempts && $attemptCount >= $exam->limit_attempts) {
                    return redirect()->route('soal.detail', $examId)
                        ->with('error', 'Anda telah mencapai batas maksimal percobaan.');
                }

                // Buat attempt baru
                $attempt = ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'student_id' => $user->id,
                    'started_at' => now(),
                    'remaining_time' => $exam->duration * 60, // dalam detik
                    'status' => 'in_progress',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            // Simpan attempt_id di session
            session(['current_attempt_' . $examId => $attempt->id]);

            // Ambil jawaban yang sudah disimpan
            $answers = \App\Models\ExamAnswer::where('attempt_id', $attempt->id)
                ->get()
                ->keyBy('question_id')
                ->map(function ($item) {
                    return $item->choice_id ?? $item->answer_text;
                })
                ->toArray();

            // Format questions untuk frontend
            $questions = $exam->questions->map(function ($question) use ($answers) {
                $options = [];

                // Pastikan untuk tipe 'PG' (multiple_choice)
                if ($question->type === 'PG' && $question->choices->isNotEmpty()) {
                    foreach ($question->choices as $choice) {
                        // Gunakan struktur yang sesuai dengan frontend
                        $options[$choice->id] = $choice->text; // Pastikan ini array asosiatif
                    }
                }

                // Tambahkan juga untuk tipe 'IS' (Isian Singkat)
                if ($question->type === 'IS') {
                    // Untuk IS, tetap beri options kosong
                    $options = [];
                }

                return [
                    'id' => $question->id,
                    'question_text' => $question->question, // Pastikan ini 'question', bukan 'question_text'
                    'type' => $question->type,
                    'score' => $question->score,
                    'options' => $options, // Array asosiatif dengan id choice sebagai key
                    'selected_answer' => $answers[$question->id] ?? null,
                ];
            });

            // Hitung waktu tersisa
            $timeElapsed = $attempt->started_at->diffInSeconds(now());
            $duration = $exam->duration * 60;
            $timeRemaining = max(0, $duration - $timeElapsed);

            // Update remaining_time
            $attempt->update(['remaining_time' => $timeRemaining]);

            return view('murid.exams.attempt', [
                'exam' => $exam,
                'questions' => $questions,
                'attempt' => $attempt,
                'timeRemaining' => $timeRemaining,
                'answeredCount' => count(array_filter($answers)),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Exam not found: ' . $e->getMessage());
            return redirect()->route('soal.index')
                ->with('error', 'Ujian tidak ditemukan atau Anda tidak memiliki akses.');
        } catch (\Exception $e) {
            \Log::error('Error in attemptFromSession: ' . $e->getMessage());
            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman mengerjakan soal (versi dengan attempt_id spesifik)
     * Method asli yang butuh 2 parameter
     */
    public function attempt($examId, $attemptId)
    {
        $user = Auth::user();

        // Cari exam
        $exam = Exam::with(['questions' => function ($query) {
            $query->with(['choices' => function ($q) {
                $q->orderBy('order');
            }]);
        }])->findOrFail($examId);

        // Cari attempt
        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->findOrFail($attemptId);

        // Validasi akses
        if ($attempt->student_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke attempt ini');
        }

        // Cek waktu
        $timeElapsed = $attempt->getTimeElapsed();
        $timeRemaining = $exam->duration * 60 - $timeElapsed;

        if ($timeRemaining <= 0) {
            $attempt->timeout();
            return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                ->with('error', 'Waktu ujian telah habis');
        }

        // Ambil jawaban yang sudah diisi
        $answers = ExamAnswer::where('attempt_id', $attempt->id)
            ->pluck('choice_id', 'question_id')
            ->toArray();

        // Hitung soal yang sudah dijawab
        $answeredCount = count($answers);

        // Jika exam memiliki shuffle_question, acak urutan soal
        $questions = $exam->questions;
        if ($exam->shuffle_question) {
            $questions = $questions->shuffle();
        }

        $formattedQuestions = $exam->questions->map(function ($question) use ($answers) {
            $options = [];

            if ($question->type === 'PG' && $question->choices->isNotEmpty()) {
                foreach ($question->choices as $choice) {
                    // Key harus berupa string atau ID choice
                    $options[$choice->id] = $choice->text;
                }
            }

            return [
                'id' => $question->id,
                'question_text' => $question->question, // Perhatikan nama field ini
                'type' => $question->type,
                'score' => $question->score,
                'options' => $options,
                'selected_answer' => $answers[$question->id] ?? null,
            ];
        });

        return view('murid.exams.attempt', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
            'formattedQuestions' => $formattedQuestions,
            'answers' => $answers,
            'answeredCount' => $answeredCount,
            'timeRemaining' => $timeRemaining,
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

            // Cek apakah sudah ada jawaban
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

            // Simpan jawaban
            if ($question->type === 'multiple_choice') {
                $answer->choice_id = $request->choice_id;
                $answer->answer_text = null;
            } else {
                $answer->choice_id = null;
                $answer->answer_text = $request->answer;
            }

            $answer->time_taken = $request->time_taken;
            $answer->answered_at = now();

            // Hitung skor untuk pilihan ganda
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
                // Untuk essay, default score 0 sampai diperiksa
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

        // Update remaining time
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

            // Validasi input
            $request->validate([
                'answers' => 'required|json',
            ]);

            // Decode answers
            $answers = json_decode($request->answers, true);

            // Cari attempt yang sedang berjalan
            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.index')
                    ->with('error', 'Tidak ada ujian yang sedang berjalan.');
            }

            // Cari exam untuk mendapatkan questions
            $exam = Exam::with('questions.choices')->findOrFail($examId);

            // Simpan setiap jawaban
            foreach ($answers as $questionId => $answerValue) {
                $question = $exam->questions->where('id', $questionId)->first();

                if (!$question) continue;

                // Cek apakah jawaban sudah ada
                $examAnswer = \App\Models\ExamAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $questionId)
                    ->first();

                if (!$examAnswer) {
                    $examAnswer = new \App\Models\ExamAnswer();
                    $examAnswer->exam_id = $examId;
                    $examAnswer->question_id = $questionId;
                    $examAnswer->student_id = $user->id;
                    $examAnswer->attempt_id = $attempt->id;
                }

                // Simpan berdasarkan tipe soal
                if ($question->type === 'multiple_choice') {
                    $examAnswer->choice_id = $answerValue;
                    $examAnswer->answer_text = null;

                    // Hitung skor jika pilihan ganda
                    $correctChoice = $question->choices->where('is_correct', true)->first();

                    if ($correctChoice && $correctChoice->id == $answerValue) {
                        $examAnswer->score = $question->score;
                        $examAnswer->is_correct = true;
                    } else {
                        $examAnswer->score = 0;
                        $examAnswer->is_correct = false;
                    }
                } else {
                    $examAnswer->choice_id = null;
                    $examAnswer->answer_text = $answerValue;
                    $examAnswer->score = 0; // Default untuk essay, perlu diperiksa manual
                    $examAnswer->is_correct = false;
                }

                $examAnswer->answered_at = now();
                $examAnswer->save();
            }

            // Hitung total skor
            $totalScore = \App\Models\ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Update attempt status
            $attempt->status = 'submitted';
            $attempt->ended_at = now();
            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            // Hapus session
            session()->forget('current_attempt_' . $examId);

            DB::commit();

            return redirect()->route('soal.hasil', ['exam' => $examId, 'attempt' => $attempt->id])
                ->with('success', 'Jawaban berhasil dikumpulkan!')
                ->with('clear_storage', true);
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

            return view('murid.exams.result', compact('exam', 'attempt'));
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

        // Hanya boleh review jika show_correct_answer = true
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

        // Update last activity
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
            'details' => 'nullable|string',
        ]);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        $attempt->logViolation($request->type, $request->details);

        return response()->json([
            'success' => true,
            'violation_count' => $attempt->violation_count,
            'is_cheating_detected' => $attempt->is_cheating_detected
        ]);
    }
}
