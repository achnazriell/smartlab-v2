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

        // Total siswa terdaftar di kelas ujian
        // Coba beberapa kemungkinan nama kolom FK
        // Total siswa terdaftar di kelas ujian — pakai currentStudents() sesuai pola ClassController
        $totalStudents = $exam->class->currentStudents()->count();

        // Ambil semua attempts (submitted/timeout) dengan data student
        $attempts = ExamAttempt::select(
            'exam_attempts.*',
            'students.nis',
            'users.name as student_name',
            'users.email as student_email'
        )
            ->leftJoin('students', 'exam_attempts.student_id', '=', 'students.id')
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['submitted', 'timeout'])
            ->orderByDesc('exam_attempts.final_score')
            ->get()
            ->map(function ($attempt) {
                $attempt->student_data = (object) [
                    'id'    => $attempt->student_id,
                    'nis'   => $attempt->nis,
                    'name'  => $attempt->student_name,
                    'email' => $attempt->student_email,
                ];
                unset($attempt->nis, $attempt->student_name, $attempt->student_email);
                return $attempt;
            });

        // Jumlah attempt (bisa > totalStudents jika ada yang reset & ulang)
        $totalAttempts = $attempts->count();

        // Siswa unik yang sudah mengerjakan
        $uniqueParticipants = $attempts->unique('student_id')->count();

        // Siswa yang belum ikut sama sekali
        $belumIkut = max(0, $totalStudents - $uniqueParticipants);

        // Statistik nilai
        $averageScore = $attempts->avg('final_score') ?? 0;
        $avgScore     = $averageScore;
        $maxScore     = $attempts->max('final_score') ?? 0;
        $completedAttempts = $totalAttempts;

        // Distribusi nilai (berdasarkan attempt terbaik per siswa jika ada lebih dari 1)
        $bestAttempts = $attempts->groupBy('student_id')->map(fn($g) => $g->sortByDesc('final_score')->first());

        $scoreDistribution = [
            'A' => $bestAttempts->where('final_score', '>=', 85)->count(),
            'B' => $bestAttempts->filter(fn($a) => $a->final_score >= 75 && $a->final_score < 85)->count(),
            'C' => $bestAttempts->filter(fn($a) => $a->final_score >= 65 && $a->final_score < 75)->count(),
            'D' => $bestAttempts->filter(fn($a) => $a->final_score >= 55 && $a->final_score < 65)->count(),
            'E' => $bestAttempts->where('final_score', '<', 55)->count(),
        ];

        // Analisis per soal
        $questions = ExamQuestion::where('exam_id', $examId)
            ->withCount(['answers as correct_answers_count' => function ($query) {
                $query->where('is_correct', true);
            }])
            ->withCount('answers')
            ->orderBy('order')
            ->get()
            ->map(function ($question) {
                $question->accuracy = $question->answers_count > 0
                    ? round(($question->correct_answers_count / $question->answers_count) * 100, 1)
                    : 0;
                return $question;
            });

        return view('Guru.Exam.results.index', compact(
            'exam',
            'totalStudents',
            'totalAttempts',
            'uniqueParticipants',
            'belumIkut',
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

        $attempt = ExamAttempt::with(['student.user', 'answers.question.choices'])
            ->where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        $totalQuestions   = $exam->questions()->count();
        $answeredQuestions = $attempt->answers()->count();
        $correctAnswers   = $attempt->answers()->where('is_correct', true)->count();
        $incorrectAnswers = $answeredQuestions - $correctAnswers;

        $timeElapsed  = $attempt->getTimeElapsed();
        $minutes      = floor($timeElapsed / 60);
        $seconds      = $timeElapsed % 60;
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

        return view('guru.exams.results.student', compact('exam', 'student', 'attempts'));
    }

    public function updateScore(Request $request, $examId, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'score'       => 'required|numeric|min:0',
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
            $answer = ExamAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $request->question_id)
                ->firstOrFail();

            $answer->score = $request->score;

            if ($request->has('is_correct')) {
                $answer->is_correct = (bool) $request->is_correct;
            }
            if ($request->has('feedback')) {
                $answer->feedback = $request->feedback;
            }

            $answer->save();

            // Hitung ulang total score
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxPossible = $exam->questions()->sum('score');
            $finalScore  = $maxPossible > 0 ? ($totalScore / $maxPossible) * 100 : 0;

            $attempt->score       = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Nilai berhasil diperbarui',
                'total_score' => $totalScore,
                'final_score' => round($finalScore, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
                    if ($answer->choice_id) {
                        $correctChoice = $question->choices->where('is_correct', true)->first();
                        if ($correctChoice && $correctChoice->id == $answer->choice_id) {
                            $answer->score      = $question->score;
                            $answer->is_correct = true;
                        } else {
                            $answer->score      = 0;
                            $answer->is_correct = false;
                        }
                        $answer->save();
                    }
                }
            }

            $totalScore  = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxPossible = $exam->questions()->sum('score');
            $finalScore  = $maxPossible > 0 ? ($totalScore / $maxPossible) * 100 : 0;

            $attempt->score       = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Koreksi otomatis berhasil dilakukan',
                'total_score' => $totalScore,
                'final_score' => round($finalScore, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
            ExamAnswer::where('attempt_id', $attempt->id)->delete();

            $attempt->score          = 0;
            $attempt->final_score    = 0;
            $attempt->status         = 'in_progress';
            $attempt->ended_at       = null;
            $attempt->remaining_time = $exam->duration * 60;
            $attempt->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attempt berhasil direset, siswa dapat mengulang ujian',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
            ->with('student.user')
            ->orderBy('final_score', 'desc')
            ->get();

        $data = [
            'exam'          => $exam,
            'attempts'      => $attempts,
            'totalStudents' => $exam->class->currentStudents()->count(),
            'totalAttempts' => $attempts->count(),
            'avgScore'      => $attempts->avg('final_score') ?? 0,
            'exportDate'    => now()->format('d F Y H:i:s'),
        ];

        if ($format === 'excel') {
            return $this->exportExcel($data);
        }

        $pdf = PDF::loadView('guru.exams.results.export-pdf', $data);
        return $pdf->download('hasil-ujian-' . $exam->title . '-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportExcel($data)
    {
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
            ->orderBy('order')
            ->get()
            ->map(function ($question) use ($examId) {
                $question->accuracy = $question->answers_count > 0
                    ? round(($question->correct_answers_count / $question->answers_count) * 100, 1)
                    : 0;

                if ($question->type === 'PG') {
                    foreach ($question->choices as $choice) {
                        $choice->selection_count = ExamAnswer::where('exam_id', $examId)
                            ->where('question_id', $question->id)
                            ->where('choice_id', $choice->id)
                            ->count();
                    }
                }

                return $question;
            });

        return view('guru.exams.results.question-analysis', compact('exam', 'questions'));
    }
}
