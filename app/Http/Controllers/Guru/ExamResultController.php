<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\Classes;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class ExamResultController extends Controller
{
    public function index($examId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Hitung statistik umum
        $totalStudents = $exam->class->students()->count();

        // Ambil attempts dengan data student yang lengkap
        $attempts = ExamAttempt::select(
            'exam_attempts.*',
            'students.nis',
            'users.name as student_name',
            'users.email as student_email',
            'users.profile_photo'
        )
            ->leftJoin('students', 'exam_attempts.student_id', '=', 'students.id')
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['submitted', 'timeout'])
            ->get()
            ->map(function ($attempt) {
                // Buat student data object
                $attempt->student_data = (object) [
                    'id' => $attempt->student_id,
                    'nis' => $attempt->nis,
                    'name' => $attempt->student_name,
                    'email' => $attempt->student_email,
                    'profile_photo' => $attempt->profile_photo,
                    'profile_photo_url' => $attempt->profile_photo
                        ? asset('storage/' . $attempt->profile_photo)
                        : asset('images/default-avatar.png')
                ];

                // Hapus kolom tambahan yang tidak perlu
                unset($attempt->nis, $attempt->student_name, $attempt->student_email, $attempt->profile_photo);

                return $attempt;
            });

        // Hitung total attempts
        $totalAttempts = $attempts->count();

        // Hitung statistik tambahan
        $completedAttempts = $totalAttempts;
        $averageScore = $attempts->avg('final_score') ?? 0;
        $maxScore = $attempts->max('final_score') ?? 0;
        $avgScore = $averageScore;

        // Distribusi nilai
        $scoreDistribution = [
            'A' => $attempts->where('final_score', '>=', 85)->count(),
            'B' => $attempts->whereBetween('final_score', [75, 84.9])->count(),
            'C' => $attempts->whereBetween('final_score', [65, 74.9])->count(),
            'D' => $attempts->whereBetween('final_score', [55, 64.9])->count(),
            'E' => $attempts->where('final_score', '<', 55)->count(),
        ];

        // Analisis per soal
        $questions = ExamQuestion::where('exam_id', $examId)
            ->withCount(['answers as correct_answers_count' => function ($query) {
                $query->where('is_correct', true);
            }])
            ->withCount('answers')
            ->get()
            ->map(function ($question) use ($totalAttempts) {
                $question->accuracy = $question->answers_count > 0
                    ? round(($question->correct_answers_count / $question->answers_count) * 100, 1)
                    : 0;
                return $question;
            });

        return view('Guru.Exam.results.index', compact(
            'exam',
            'totalStudents',
            'totalAttempts',
            'avgScore',
            'scoreDistribution',
            'questions',
            'attempts',
            'completedAttempts',
            'averageScore',
            'maxScore'
        ));
    }

    public function show($examId, $attemptId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attempt = ExamAttempt::with(['student', 'answers.question.choices'])
            ->where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        // Hitung statistik untuk attempt ini
        $totalQuestions = $exam->questions()->count();
        $answeredQuestions = $attempt->answers()->count();
        $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
        $incorrectAnswers = $answeredQuestions - $correctAnswers;

        // Waktu pengerjaan
        $timeElapsed = $attempt->getTimeElapsed();
        $minutes = floor($timeElapsed / 60);
        $seconds = $timeElapsed % 60;
        $timeFormatted = sprintf('%02d:%02d', $minutes, $seconds);

        return view('guru.exams.results.detail', compact(
            'exam',
            'attempt',
            'totalQuestions',
            'answeredQuestions',
            'correctAnswers',
            'incorrectAnswers',
            'timeFormatted'
        ));
    }

    public function byStudent($examId, $studentId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $student = Student::findOrFail($studentId);

        $attempts = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with('answers')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guru.exams.results.student', compact(
            'exam',
            'student',
            'attempts'
        ));
    }

    public function updateScore(Request $request, $examId, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'score' => 'required|numeric|min:0',
        ]);

        $teacher = Auth::user()->teacher;

        $exam = Exam::where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Update score per question
            $answer = ExamAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $request->question_id)
                ->firstOrFail();

            $answer->score = $request->score;

            // Jika ada penilaian manual untuk essay
            if ($request->has('is_correct')) {
                $answer->is_correct = $request->is_correct;
            }

            if ($request->has('feedback')) {
                $answer->feedback = $request->feedback;
            }

            $answer->save();

            // Hitung ulang total score
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions()->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui',
                'total_score' => $totalScore,
                'final_score' => round($finalScore, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function regrade($examId, $attemptId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $answers = ExamAnswer::where('attempt_id', $attempt->id)->get();

            foreach ($answers as $answer) {
                $question = ExamQuestion::with('choices')->find($answer->question_id);

                if ($question && $question->type === 'PG') {
                    // Hanya koreksi otomatis untuk PG
                    if ($answer->choice_id) {
                        $correctChoice = $question->choices->where('is_correct', true)->first();
                        if ($correctChoice && $correctChoice->id == $answer->choice_id) {
                            $answer->score = $question->score;
                            $answer->is_correct = true;
                        } else {
                            $answer->score = 0;
                            $answer->is_correct = false;
                        }
                        $answer->save();
                    }
                }
            }

            // Hitung ulang total score
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore = $exam->questions()->sum('score');
            $finalScore = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            $attempt->score = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Koreksi otomatis berhasil dilakukan',
                'total_score' => $totalScore,
                'final_score' => round($finalScore, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetAttempt($examId, $attemptId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Hapus semua jawaban
            ExamAnswer::where('attempt_id', $attempt->id)->delete();

            // Reset attempt
            $attempt->score = 0;
            $attempt->final_score = 0;
            $attempt->status = 'in_progress';
            $attempt->ended_at = null;
            $attempt->remaining_time = $exam->duration * 60;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attempt berhasil direset, siswa dapat mengulang ujian'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export($examId, $format = 'pdf')
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class', 'questions'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $attempts = ExamAttempt::where('exam_id', $examId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->with('student')
            ->orderBy('final_score', 'desc')
            ->get();

        $data = [
            'exam' => $exam,
            'attempts' => $attempts,
            'totalStudents' => $exam->class->students()->count(),
            'totalAttempts' => $attempts->count(),
            'avgScore' => $attempts->avg('final_score') ?? 0,
            'exportDate' => now()->format('d F Y H:i:s'),
        ];

        if ($format === 'excel') {
            return $this->exportExcel($data);
        }

        // Default PDF
        $pdf = PDF::loadView('guru.exams.results.export-pdf', $data);
        return $pdf->download('hasil-ujian-' . $exam->title . '-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportExcel($data)
    {
        // Implementasi export Excel menggunakan Laravel Excel
        // Untuk sementara kembalikan view biasa
        return view('guru.exams.results.export-excel', $data);
    }

    public function questionAnalysis($examId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $questions = ExamQuestion::where('exam_id', $examId)
            ->with(['choices' => function ($query) {
                $query->orderBy('order');
            }])
            ->withCount(['answers as correct_answers_count' => function ($query) {
                $query->where('is_correct', true);
            }])
            ->withCount('answers')
            ->withCount(['answers as choice_selections' => function ($query) use ($examId) {
                $query->select(DB::raw('choice_id, COUNT(*) as count'))
                    ->whereNotNull('choice_id')
                    ->groupBy('choice_id');
            }])
            ->get()
            ->map(function ($question) use ($examId) {
                // Hitung akurasi
                $question->accuracy = $question->answers_count > 0
                    ? round(($question->correct_answers_count / $question->answers_count) * 100, 1)
                    : 0;

                // Analisis pilihan jawaban
                if ($question->type === 'PG') {
                    $choices = $question->choices;
                    foreach ($choices as $choice) {
                        $choice->selection_count = ExamAnswer::where('exam_id', $examId)
                            ->where('question_id', $question->id)
                            ->where('choice_id', $choice->id)
                            ->count();
                    }
                    $question->choices = $choices;
                }

                return $question;
            });

        return view('guru.exams.results.question-analysis', compact('exam', 'questions'));
    }
}
