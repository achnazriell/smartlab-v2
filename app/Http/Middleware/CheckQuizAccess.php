<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;
use App\Models\QuizParticipant;

class CheckQuizAccess
{
    public function handle(Request $request, Closure $next)
    {
        $quizId = $request->route('quiz');
        $user = Auth::user();

        // Get quiz
        $quiz = Exam::find($quizId);

        if (!$quiz) {
            return redirect()->route('quiz.index')->with('error', 'Quiz tidak ditemukan.');
        }

        // Check if student
        if ($user->hasRole('Murid')) {
            $student = $user->student;

            if (!$student) {
                return redirect()->route('quiz.index')->with('error', 'Data siswa tidak ditemukan.');
            }

            // Check class access
            if ($quiz->class_id != $student->class_id) {
                return redirect()->route('quiz.index')->with('error', 'Quiz tidak tersedia untuk kelas Anda.');
            }

            // Check quiz status
            if ($quiz->status !== 'active') {
                return redirect()->route('quiz.index')->with('error', 'Quiz tidak aktif.');
            }

            // Check if quiz has started
            if (!$quiz->is_quiz_started) {
                return redirect()->route('quiz.room', $quiz->id)->with('error', 'Quiz belum dimulai.');
            }

            // Check participant
            $session = $quiz->activeSession;
            if ($session) {
                $participant = QuizParticipant::where([
                    'quiz_session_id' => $session->id,
                    'student_id' => $user->id
                ])->first();

                if (!$participant) {
                    return redirect()->route('quiz.room', $quiz->id)->with('error', 'Anda belum bergabung.');
                }

                // Allow access if status is started
                if ($participant->status === 'started') {
                    return $next($request);
                }

                // If not started, update status
                if ($participant->status !== 'submitted') {
                    $participant->update(['status' => 'started', 'started_at' => now()]);
                    return $next($request);
                }

                // If already submitted, redirect to results
                return redirect()->route('quiz.room', $quiz->id)->with('info', 'Anda sudah mengerjakan quiz ini.');
            }

            return redirect()->route('quiz.room', $quiz->id)->with('error', 'Sesi tidak ditemukan.');
        }

        return $next($request);
    }
}
