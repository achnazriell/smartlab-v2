<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamResultsExport;
use Barryvdh\DomPDF\PDF;

class ExamResultController extends Controller
{
    public function index($examId)
    {
        $exam = Exam::where('teacher_id', Auth::id())
            ->with(['attempts' => function ($query) {
                $query->with(['student', 'answers'])
                    ->where('status', 'submitted')
                    ->orderBy('final_score', 'desc');
            }])
            ->findOrFail($examId);

        // Hitung statistik
        $totalAttempts = $exam->attempts->count();
        $averageScore = $exam->attempts->avg('final_score');
        $highestScore = $exam->attempts->max('final_score');
        $lowestScore = $exam->attempts->min('final_score');
        $passCount = $exam->attempts->where('final_score', '>=', $exam->min_pass_grade)->count();

        return view('Guru.Exam.results.index', compact(
            'exam',
            'totalAttempts',
            'averageScore',
            'highestScore',
            'lowestScore',
            'passCount'
        ));
    }

    public function show($examId, $attemptId)
    {
        $exam = Exam::where('teacher_id', Auth::id())
            ->findOrFail($examId);

        $attempt = ExamAttempt::with(['student', 'answers.question.choices'])
            ->where('exam_id', $examId)
            ->findOrFail($attemptId);

        return view('Guru.Exam.results.show', compact('exam', 'attempt'));
    }

    public function updateScore(Request $request, $examId, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'score' => 'required|numeric|min:0',
        ]);

        $exam = Exam::where('teacher_id', Auth::id())
            ->findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->findOrFail($attemptId);

        try {
            DB::beginTransaction();

            // Update score untuk jawaban essay
            $answer = ExamAnswer::where('attempt_id', $attemptId)
                ->where('question_id', $request->question_id)
                ->firstOrFail();

            $answer->update([
                'score' => $request->score,
                'is_correct' => $request->score > 0,
            ]);

            // Recalculate total score
            $attempt->calculateScore();

            DB::commit();

            return response()->json([
                'success' => true,
                'new_score' => $answer->score,
                'total_score' => $attempt->final_score,
                'message' => 'Nilai berhasil diperbarui'
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
        $exam = Exam::where('teacher_id', Auth::id())
            ->findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->findOrFail($attemptId);

        try {
            DB::beginTransaction();

            // Recalculate all scores
            foreach ($attempt->answers as $answer) {
                $answer->calculateScore();
            }

            // Recalculate attempt score
            $attempt->calculateScore();

            DB::commit();

            return response()->json([
                'success' => true,
                'new_score' => $attempt->final_score,
                'message' => 'Penilaian ulang berhasil dilakukan'
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
        $exam = Exam::where('teacher_id', Auth::id())
            ->findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $examId)
            ->findOrFail($attemptId);

        try {
            DB::beginTransaction();

            // Delete all answers
            $attempt->answers()->delete();

            // Reset attempt
            $attempt->update([
                'started_at' => null,
                'ended_at' => null,
                'status' => 'in_progress',
                'remaining_time' => $exam->duration * 60,
                'score' => null,
                'final_score' => null,
                'violation_count' => 0,
                'violation_log' => null,
                'is_cheating_detected' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Percobaan ujian berhasil direset'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPDF($examId)
    {
        $exam = Exam::where('teacher_id', Auth::id())
            ->with(['attempts' => function ($query) {
                $query->with('student')
                    ->where('status', 'submitted')
                    ->orderBy('final_score', 'desc');
            }])
            ->findOrFail($examId);

        $pdf = PDF::loadView('Guru.Exam.results.export_pdf', compact('exam'));

        return $pdf->download('hasil-ujian-' . $exam->title . '.pdf');
    }

    public function exportExcel($examId)
    {
        $exam = Exam::where('teacher_id', Auth::id())
            ->findOrFail($examId);

        return Excel::download(new ExamResultsExport($exam), 'hasil-ujian-' . $exam->title . '.xlsx');
    }
}
