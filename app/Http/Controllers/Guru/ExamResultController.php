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

        $totalStudents = $exam->class->currentStudents()->count();

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

        $totalAttempts      = $attempts->count();
        $uniqueParticipants = $attempts->unique('student_id')->count();
        $belumIkut          = max(0, $totalStudents - $uniqueParticipants);

        $averageScore      = $attempts->avg('final_score') ?? 0;
        $avgScore          = $averageScore;
        $maxScore          = $attempts->max('final_score') ?? 0;
        $completedAttempts = $totalAttempts;

        $bestAttempts = $attempts->groupBy('student_id')
            ->map(fn($g) => $g->sortByDesc('final_score')->first());

        $scoreDistribution = [
            'A' => $bestAttempts->where('final_score', '>=', 85)->count(),
            'B' => $bestAttempts->filter(fn($a) => $a->final_score >= 75 && $a->final_score < 85)->count(),
            'C' => $bestAttempts->filter(fn($a) => $a->final_score >= 65 && $a->final_score < 75)->count(),
            'D' => $bestAttempts->filter(fn($a) => $a->final_score >= 55 && $a->final_score < 65)->count(),
            'E' => $bestAttempts->where('final_score', '<', 55)->count(),
        ];

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

    /**
     * FIX: Tidak eager-load 'student.user' karena menyebabkan RelationNotFoundException
     * (User model tidak punya relasi bernama 'user').
     * Gunakan JOIN manual untuk ambil nama & NIS siswa.
     */
    public function show($examId, $attemptId)
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Ambil attempt + jawaban – TANPA 'student.user' (penyebab error)
        $attempt = ExamAttempt::with(['answers.question.choices'])
            ->where('exam_id', $examId)
            ->where('id', $attemptId)
            ->firstOrFail();

        // Ambil data student + nama user via JOIN manual
        $studentRow = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.id', $attempt->student_id)
            ->select('students.*', 'users.name as user_name', 'users.email as user_email')
            ->first();

        // Pasang ke attempt agar blade bisa akses $attempt->student_data->name, dst.
        $attempt->student_data = (object) [
            'id'    => $attempt->student_id,
            'nis'   => $studentRow->nis        ?? '-',
            'name'  => $studentRow->user_name  ?? 'Siswa',
            'email' => $studentRow->user_email ?? '-',
        ];

        $totalQuestions    = $exam->questions()->count();
        $answeredQuestions = $attempt->answers()->count();
        $correctAnswers    = $attempt->answers()->where('is_correct', true)->count();
        $incorrectAnswers  = $answeredQuestions - $correctAnswers;

        $timeElapsed   = $attempt->getTimeElapsed();
        $minutes       = floor($timeElapsed / 60);
        $seconds       = $timeElapsed % 60;
        $timeFormatted = sprintf('%02d:%02d', $minutes, $seconds);

        return view('guru.exam.results.detail', compact(
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

            $totalScore  = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
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

    /**
     * Export hasil ujian.
     * URL contoh: GET /guru/exams/{exam}/results/export/excel
     *             GET /guru/exams/{exam}/results/export/pdf
     */
    public function export($examId, $format = 'pdf')
    {
        $teacher = Auth::user()->teacher;

        $exam = Exam::with(['subject', 'class', 'questions'])
            ->where('id', $examId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Ambil attempts via JOIN – TIDAK pakai 'student.user' agar tidak error
        $attempts = ExamAttempt::select(
            'exam_attempts.*',
            'students.nis',
            'users.name as student_name'
        )
            ->leftJoin('students', 'exam_attempts.student_id', '=', 'students.id')
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->where('exam_attempts.exam_id', $examId)
            ->whereIn('exam_attempts.status', ['submitted', 'timeout'])
            ->orderByDesc('exam_attempts.final_score')
            ->get();

        if ($format === 'excel') {
            return $this->exportExcel($exam, $attempts);
        }

        // PDF
        $data = [
            'exam'          => $exam,
            'attempts'      => $attempts,
            'totalStudents' => $exam->class->currentStudents()->count(),
            'totalAttempts' => $attempts->count(),
            'avgScore'      => $attempts->avg('final_score') ?? 0,
            'exportDate'    => now()->format('d F Y H:i:s'),
        ];

        $pdf = PDF::loadView('guru.exams.results.export-pdf', $data);
        return $pdf->download('hasil-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Buat file Excel (.xlsx) menggunakan PhpSpreadsheet.
     * Jika tidak tersedia, otomatis fallback ke CSV (tetap bisa dibuka di Excel).
     *
     * Install PhpSpreadsheet:
     *   composer require phpoffice/phpspreadsheet
     */
    private function exportExcel($exam, $attempts)
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->exportXlsx($exam, $attempts);
        }

        // Fallback CSV
        return $this->exportCsv($exam, $attempts);
    }

    private function exportXlsx($exam, $attempts)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Ujian');

        // ── Judul ──
        $sheet->setCellValue('A1', 'HASIL UJIAN — ' . strtoupper($exam->title));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // ── Info ujian ──
        $sheet->setCellValue('A2', 'Mata Pelajaran');
        $sheet->setCellValue('B2', ': ' . ($exam->subject->name ?? '-'));
        $sheet->setCellValue('D2', 'Kelas');
        $sheet->setCellValue('E2', ': ' . ($exam->class->name ?? '-'));

        $sheet->setCellValue('A3', 'Jenis Ujian');
        $sheet->setCellValue('B3', ': ' . ($exam->getDisplayType()));
        $sheet->setCellValue('D3', 'Tanggal Export');
        $sheet->setCellValue('E3', ': ' . now()->format('d/m/Y H:i'));

        $sheet->setCellValue('A4', 'Total Peserta');
        $sheet->setCellValue('B4', ': ' . $attempts->count());
        $sheet->setCellValue('D4', 'Rata-rata Nilai');
        $sheet->setCellValue('E4', ': ' . number_format($attempts->avg('final_score') ?? 0, 2));

        // ── Header kolom (baris 6) ──
        $headers = ['No', 'NIS', 'Nama Siswa', 'Nilai Akhir', 'Grade', 'Status', 'Waktu Submit'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $i => $header) {
            $cell = $cols[$i] . '6';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4F46E5'); // indigo-600
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // ── Data siswa ──
        $no  = 1;
        $row = 7;
        foreach ($attempts as $attempt) {
            $fs    = $attempt->final_score ?? 0;
            $grade = $fs >= 85 ? 'A' : ($fs >= 75 ? 'B' : ($fs >= 65 ? 'C' : ($fs >= 55 ? 'D' : 'E')));

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $attempt->nis ?? '-');
            $sheet->setCellValue('C' . $row, $attempt->student_name ?? 'Siswa');
            $sheet->setCellValue('D' . $row, round($fs, 2));
            $sheet->setCellValue('E' . $row, $grade);
            $sheet->setCellValue('F' . $row, ucfirst($attempt->status ?? '-'));
            $sheet->setCellValue('G' . $row, $attempt->ended_at
                ? Carbon::parse($attempt->ended_at)->format('d/m/Y H:i')
                : '-');

            // Warna grade
            $gradeColor = match ($grade) {
                'A'     => 'FFD1FAE5', // emerald-100
                'B'     => 'FFDBEAFE', // blue-100
                'C'     => 'FFFEF9C3', // yellow-100
                'D'     => 'FFFFEDD5', // orange-100
                default => 'FFFEE2E2', // red-100
            };
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($gradeColor);

            // Zebra stripe
            if ($no % 2 === 0) {
                foreach (['A', 'B', 'C', 'D', 'F', 'G'] as $c) {
                    $sheet->getStyle($c . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8FAFC');
                }
            }

            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $no++;
            $row++;
        }

        // ── Border ──
        $tableRange = 'A6:G' . ($row - 1);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // ── Auto-width ──
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Download ──
        $filename = 'hasil-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '-' . now()->format('Y-m-d') . '.xlsx';
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Fallback CSV – dapat dibuka langsung di Microsoft Excel.
     */
    private function exportCsv($exam, $attempts)
    {
        $filename = 'hasil-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($exam, $attempts) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($f, ['Judul Ujian', $exam->title]);
            fputcsv($f, ['Mata Pelajaran', $exam->subject->name ?? '-']);
            fputcsv($f, ['Kelas', $exam->class->name ?? '-']);
            fputcsv($f, ['Tanggal Export', now()->format('d/m/Y H:i')]);
            fputcsv($f, ['Rata-rata Nilai', number_format($attempts->avg('final_score') ?? 0, 2)]);
            fputcsv($f, []);

            fputcsv($f, ['No', 'NIS', 'Nama Siswa', 'Nilai Akhir', 'Grade', 'Status', 'Waktu Submit']);

            $no = 1;
            foreach ($attempts as $attempt) {
                $fs    = $attempt->final_score ?? 0;
                $grade = $fs >= 85 ? 'A' : ($fs >= 75 ? 'B' : ($fs >= 65 ? 'C' : ($fs >= 55 ? 'D' : 'E')));

                fputcsv($f, [
                    $no++,
                    $attempt->nis ?? '-',
                    $attempt->student_name ?? 'Siswa',
                    round($fs, 2),
                    $grade,
                    ucfirst($attempt->status ?? '-'),
                    $attempt->ended_at
                        ? Carbon::parse($attempt->ended_at)->format('d/m/Y H:i')
                        : '-',
                ]);
            }

            fclose($f);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
