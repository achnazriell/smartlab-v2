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
        $user = Auth::user();

        // Cari exam
        $exam = Exam::findOrFail($examId);

        // Validasi akses
        if (!$user->student || $exam->class_id !== $user->student->class_id) {
            return redirect()->route('soal.index')
                ->with('error', 'Anda tidak memiliki akses ke ujian ini');
        }

        // Cek apakah request untuk reset
        $reset = $request->input('reset', false);

        if (!$reset) {
            // Cek apakah sudah ada attempt yang belum selesai
            $existingAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($existingAttempt) {
                // Simpan attempt_id ke session untuk redirect ke attemptFromSession
                session(['current_attempt_' . $examId => $existingAttempt->id]);

                return redirect()->route('soal.kerjakan', $examId);
            }
        } else {
            // Reset: tutup attempt yang sedang berjalan
            $existingAttempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($existingAttempt) {
                $existingAttempt->update(['status' => 'aborted']);
                ExamAnswer::where('attempt_id', $existingAttempt->id)->delete();
            }
        }

        // Cek apakah sudah mencapai limit attempts
        $attemptCount = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $user->id)
            ->count();

        if ($exam->limit_attempts && $attemptCount >= $exam->limit_attempts) {
            return redirect()->route('soal.detail', $examId)
                ->with('error', 'Anda telah mencapai batas maksimal percobaan');
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

        // Simpan attempt_id ke session
        session(['current_attempt_' . $examId => $attempt->id]);

        return redirect()->route('soal.kerjakan', $examId);
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

            // Cari exam
            $exam = Exam::with(['questions' => function ($query) {
                $query->with('choices');
            }])->findOrFail($examId);

            // Validasi akses
            if (!$user->student || $exam->class_id !== $user->student->class_id) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini');
            }

            // Cari attempt yang sedang berjalan
            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            // Jika tidak ada attempt yang sedang berjalan, redirect ke detail
            if (!$attempt) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Tidak ada ujian yang sedang berjalan. Silakan mulai dari halaman detail.');
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

            return view('murid.exams.attempt', [
                'exam' => $exam,
                'attempt' => $attempt,
                'questions' => $questions,
                'answers' => $answers,
                'answeredCount' => $answeredCount,
                'timeRemaining' => $timeRemaining,
            ]);
        } catch (\Exception $e) {
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
            $query->with('choices');
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

        return view('murid.exams.attempt', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
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
        $exam = Exam::findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Hitung skor akhir
            $attempt->calculateScore();

            // Tandai sebagai selesai
            $attempt->ended_at = now();
            $attempt->status = 'submitted';
            $attempt->save();

            DB::commit();

            return redirect()->route('murid.exams.result', ['exam' => $examId, 'attempt' => $attempt->id])
                ->with('success', 'Ujian berhasil dikumpulkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function result($examId, $attemptId)
    {
        $exam = Exam::findOrFail($examId);

        $attempt = ExamAttempt::with(['answers.question.choices'])
            ->where('exam_id', $examId)
            ->where('student_id', Auth::id())
            ->findOrFail($attemptId);

        // Cek apakah siswa berhak melihat hasil
        if ($exam->show_result_after === 'never') {
            abort(403, 'Hasil ujian tidak ditampilkan.');
        }

        if ($exam->show_result_after === 'after_exam' && !$exam->isFinished()) {
            abort(403, 'Hasil ujian akan ditampilkan setelah ujian berakhir.');
        }

        return view('Murid.Exam.result', compact('exam', 'attempt'));
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
