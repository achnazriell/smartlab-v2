<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\QuizParticipant;
use App\Models\QuizSession;
use App\Models\Student;
use App\Models\StudentClassAssignment;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    // ===========================================================================
    // QUIZ MODES:
    //   homework  = Quiz Mandiri  : siswa kerjakan sendiri dalam rentang waktu,
    //                               pelanggaran melebihi batas → auto-submit
    //   live      = Live Quiz     : guru buka ruangan, pantau real-time,
    //                               pelanggaran TIDAK auto-submit tapi kartu merah
    //                               guru bisa kirim peringatan & dering
    //   guided    = Quiz Terpandu : soal tampil di layar guru/proyektor,
    //                               siswa hanya jawab di perangkat masing-masing,
    //                               guru kontrol perpindahan soal
    // ===========================================================================

    // ==========================================
    // HELPER
    // ==========================================

    /**
     * Base query untuk quiz milik guru yang sedang login.
     * Menggunakan withoutGlobalScopes agar quiz draft/inactive tetap muncul.
     */
    private function quizQuery()
    {
        return Exam::withoutGlobalScopes()
            ->where('type', 'QUIZ')
            ->where('teacher_id', Auth::user()->teacher->id);
    }

    /**
     * Bangun violations array dari violation_log participant.
     * Mendukung kolom bertipe JSON (cast array) atau string JSON mentah.
     * Mengabaikan entri teacher_warning agar tidak tercampur dengan pelanggaran siswa.
     */
    private function buildViolations($participant): array
    {
        $raw = $participant->violation_log;
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        $raw = is_array($raw) ? $raw : [];

        return array_values(array_filter(array_map(function ($item) {
            // Abaikan entri teacher_warning dari daftar pelanggaran siswa
            if (($item['type'] ?? '') === 'teacher_warning') return null;
            return [
                'type'      => $item['type']      ?? 'unknown',
                'details'   => $item['details']   ?? $item['detail'] ?? '',
                'timestamp' => $item['timestamp'] ?? '',
            ];
        }, $raw)));
    }

    // ==========================================
    // INDEX
    // ==========================================

    /**
     * Display a listing of quizzes
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $quizzes = $this->quizQuery()
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $quizzes->where('status', $request->status);
        }
        if ($request->filled('class_id')) {
            $quizzes->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $quizzes->where('subject_id', $request->subject_id);
        }
        if ($request->filled('quiz_mode')) {
            $quizzes->where('quiz_mode', $request->quiz_mode);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $quizzes->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('subject', fn($q2) => $q2->where('name_subject', 'like', "%{$search}%"));
            });
        }

        $quizzes = $quizzes->paginate(10);

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;
        $classes    = $teacher->classesTaughtInAcademicYear($yearId)->get();
        $subjects   = $teacher->subjectsTaughtInAcademicYear($yearId)->get();

        return view('Guru.Quiz.index', compact('quizzes', 'classes', 'subjects'));
    }

    // ==========================================
    // CREATE & STORE
    // ==========================================

    /**
     * Show the form for creating a new quiz
     */
    public function create()
    {
        $user    = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Anda harus login sebagai guru');
        }

        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $mapels  = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();

        return view('guru.quiz.create', compact('mapels', 'classes'));
    }

    /**
     * Store a newly created quiz.
     * Mendukung multi-kelas (class_ids[]) dan mode quiz: homework / live / guided.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'              => 'required|string|max:255',
            'type'               => 'required|in:QUIZ',
            'subject_id'         => 'required|exists:subjects,id',
            'class_ids'          => 'required|array|min:1',
            'class_ids.*'        => 'exists:classes,id',
            'quiz_mode'          => 'required|in:live,homework,guided',
            'time_per_question'  => 'nullable|integer|min:0|max:600',
            'duration'           => 'required|integer|min:1|max:480',

            // Hanya wajib untuk homework
            'start_at'           => 'required_if:quiz_mode,homework|nullable|date',
            'end_at'             => 'required_if:quiz_mode,homework|nullable|date|after:start_at',

            'violation_limit'    => 'nullable|integer|min:1|max:50',
            'limit_attempts'     => 'nullable|integer|min:1|max:10',
            'min_pass_grade'     => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $teacher = Auth::user()->teacher;

        try {
            DB::beginTransaction();

            $timePerQuestion = (int) ($request->time_per_question ?? 0);

            // Kelas pertama sebagai primary (kompatibilitas kolom class_id lama)
            $primaryClassId = $request->class_ids[0];

            $quiz = Exam::create([
                'teacher_id'           => $teacher->id,
                'class_id'             => $primaryClassId,
                'subject_id'           => $request->subject_id,
                'title'                => $request->title,
                'type'                 => 'QUIZ',
                'duration'             => $request->duration,
                'start_at'             => $request->quiz_mode === 'homework' ? $request->start_at : null,
                'end_at'               => $request->quiz_mode === 'homework' ? $request->end_at : null,
                'time_per_question'    => $timePerQuestion,
                'quiz_mode'            => $request->quiz_mode,
                'difficulty_level'     => 'medium',
                'status'               => 'draft',

                // Pengaturan soal
                'shuffle_question'     => $request->boolean('shuffle_question'),
                'shuffle_answer'       => $request->boolean('shuffle_answer'),
                'show_score'           => $request->boolean('show_score', true),
                'show_correct_answer'  => $request->boolean('show_correct_answer'),
                'show_result_after'    => $request->show_result_after ?? 'immediately',
                'limit_attempts'       => $request->limit_attempts ?? 1,
                'min_pass_grade'       => $request->min_pass_grade ?? 0,
                'enable_retake'        => $request->boolean('enable_retake'),

                // Fitur interaktif
                'show_leaderboard'     => $request->boolean('show_leaderboard'),
                'enable_music'         => $request->boolean('enable_music'),
                'enable_memes'         => $request->boolean('enable_memes'),
                'enable_powerups'      => $request->boolean('enable_powerups'),
                'instant_feedback'     => $request->boolean('instant_feedback'),
                'streak_bonus'         => $request->boolean('streak_bonus'),
                'time_bonus'           => $request->boolean('time_bonus'),

                // Keamanan
                'fullscreen_mode'      => $request->boolean('fullscreen_mode'),
                'block_new_tab'        => $request->boolean('block_new_tab'),
                'prevent_copy_paste'   => $request->boolean('prevent_copy_paste'),
                // violation_limit hanya untuk homework; live/guided pakai monitoring
                'violation_limit'      => $request->quiz_mode === 'homework'
                    ? ($request->violation_limit ?? 3)
                    : 99, // effectively unlimited untuk live/guided
                'disable_violations'   => false,

                // Room
                'is_room_open'         => false,
                'is_quiz_started'      => false,
                'quiz_started_at'      => null,
                'quiz_remaining_time'  => null,
            ]);

            // Assign ke semua kelas yang dipilih
            foreach ($request->class_ids as $classId) {
                $this->assignQuizToStudentsByClass($quiz, (int) $classId);
            }

            DB::commit();

            Log::info('Quiz created', ['quiz_id' => $quiz->id, 'class_ids' => $request->class_ids, 'mode' => $quiz->quiz_mode]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Quiz berhasil dibuat!',
                    'exam_id'  => $quiz->id,
                    'redirect' => route('guru.quiz.questions', $quiz->id)
                ]);
            }

            return redirect()->route('guru.quiz.questions', $quiz->id)
                ->with('success', 'Quiz berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quiz: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat quiz: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal membuat quiz: ' . $e->getMessage())->withInput();
        }
    }

    // ==========================================
    // SHOW QUESTION CREATOR
    // ==========================================

    /**
     * Show quiz question creator page
     */
    public function showQuestionCreator(Exam $quiz)
    {
        $teacher = Auth::user()->teacher;

        if ($quiz->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        if ($quiz->type !== 'QUIZ') {
            return redirect()->route('guru.exams.soal', $quiz->id);
        }

        // Quiz yang sudah selesai tidak dapat diedit soalnya
        if ($quiz->status === 'finished') {
            return redirect()->route('guru.quiz.preview', $quiz->id)
                ->with('error', 'Soal quiz yang sudah selesai tidak dapat diedit.');
        }

        $questions    = $quiz->questions()->with('choices')->orderBy('order')->get();
        $questionCount = $questions->count();
        $totalScore   = $questions->sum('score');

        $questionsData = $questions->map(function ($q) {
            return [
                'id'           => $q->id,
                'question'     => $q->question,
                'type'         => $q->type,
                'score'        => $q->score,
                'explanation'  => $q->explanation ?? '',
                'choices'      => $q->choices->map(fn($c) => [
                    'id'         => $c->id,
                    'text'       => $c->text,
                    'is_correct' => (bool) $c->is_correct,
                ])->values(),
                'short_answers' => $q->type === 'IS' ? (json_decode($q->short_answers ?? '[]') ?? []) : [],
            ];
        })->values();

        $otherQuizzes = Exam::where('teacher_id', $teacher->id)
            ->where('type', 'QUIZ')
            ->where('id', '!=', $quiz->id)
            ->whereHas('questions')
            ->withCount('questions')
            ->get();

        return view('guru.quiz.questions', compact('quiz', 'questions', 'questionCount', 'totalScore', 'questionsData', 'otherQuizzes'));
    }

    // ==========================================
    // EDIT & UPDATE
    // ==========================================

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        if ($quiz->type !== 'QUIZ') {
            return redirect()->route('guru.exams.edit', ['exam' => $quiz->id]);
        }

        // Quiz yang sudah selesai tidak dapat diedit
        if ($quiz->status === 'finished') {
            return redirect()->route('guru.quiz.preview', $quiz->id)
                ->with('error', 'Quiz yang sudah selesai tidak dapat diedit.');
        }

        $teacher    = auth()->user()->teacher;
        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();

        // Ambil semua kelas untuk mapel quiz ini agar multi-select bisa tampil
        $classes = $teacher->classesTaughtInAcademicYear($yearId)
            ->wherePivot('subject_id', $quiz->subject_id)
            ->get();

        // Kelas yang sudah ter-assign ke quiz ini
        $assignedClassIds = DB::table('exam_student')
            ->join('students', 'exam_student.student_id', '=', 'students.user_id')
            ->join('student_class_assignments', 'students.id', '=', 'student_class_assignments.student_id')
            ->where('exam_student.exam_id', $quiz->id)
            ->pluck('student_class_assignments.class_id')
            ->unique()
            ->values()
            ->toArray();

        // Jika belum ada data pivot (quiz lama), fallback ke class_id
        if (empty($assignedClassIds) && $quiz->class_id) {
            $assignedClassIds = [$quiz->class_id];
        }

        return view('guru.quiz.edit', compact('quiz', 'mapels', 'classes', 'assignedClassIds'));
    }

    /**
     * Update the specified quiz
     */
    public function update(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Quiz yang sudah selesai tidak dapat diedit sama sekali
        if ($quiz->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Quiz yang sudah selesai tidak dapat diedit. Status "Selesai" bersifat permanen.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title'              => 'required|string|max:255',
            'subject_id'         => 'required|exists:subjects,id',
            'class_ids'          => 'required|array|min:1',
            'class_ids.*'        => 'exists:classes,id',
            'quiz_mode'          => 'required|in:live,homework,guided',
            'time_per_question'  => 'nullable|integer|min:0|max:600',
            'duration'           => 'required|integer|min:1|max:480',

            'start_at'           => 'required_if:quiz_mode,homework|nullable|date',
            'end_at'             => 'required_if:quiz_mode,homework|nullable|date|after:start_at',

            'violation_limit'    => 'nullable|integer|min:1|max:50',
            'limit_attempts'     => 'nullable|integer|min:1|max:10',
            'min_pass_grade'     => 'nullable|numeric|min:0|max:100',
            // Status 'finished' tidak boleh diset manual via update
            'status'             => 'nullable|in:draft,active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $primaryClassId  = $request->class_ids[0];
            $timePerQuestion = (int) ($request->time_per_question ?? 0);

            $updateData = [
                'title'               => $request->title,
                'subject_id'          => $request->subject_id,
                'class_id'            => $primaryClassId,
                'quiz_mode'           => $request->quiz_mode,
                'time_per_question'   => $timePerQuestion,
                'duration'            => $request->duration,
                'start_at'            => $request->quiz_mode === 'homework' ? $request->start_at : null,
                'end_at'              => $request->quiz_mode === 'homework' ? $request->end_at : null,

                'shuffle_question'    => $request->boolean('shuffle_question'),
                'shuffle_answer'      => $request->boolean('shuffle_answer'),
                'show_score'          => $request->boolean('show_score'),
                'show_correct_answer' => $request->boolean('show_correct_answer'),
                'show_result_after'   => $request->show_result_after ?? 'immediately',
                'limit_attempts'      => $request->limit_attempts ?? 1,
                'min_pass_grade'      => $request->min_pass_grade ?? 0,
                'enable_retake'       => $request->boolean('enable_retake'),

                'show_leaderboard'    => $request->boolean('show_leaderboard'),
                'enable_music'        => $request->boolean('enable_music'),
                'enable_memes'        => $request->boolean('enable_memes'),
                'enable_powerups'     => $request->boolean('enable_powerups'),
                'instant_feedback'    => $request->boolean('instant_feedback'),
                'streak_bonus'        => $request->boolean('streak_bonus'),
                'time_bonus'          => $request->boolean('time_bonus'),

                'fullscreen_mode'     => $request->boolean('fullscreen_mode'),
                'block_new_tab'       => $request->boolean('block_new_tab'),
                'prevent_copy_paste'  => $request->boolean('prevent_copy_paste'),
                'violation_limit'     => $request->quiz_mode === 'homework'
                    ? ($request->violation_limit ?? 3)
                    : 99,
            ];

            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
            }

            $quiz->update($updateData);

            // Sinkronisasi exam_student untuk kelas baru
            // Hapus assignment lama, lalu buat ulang untuk kelas yang dipilih
            DB::table('exam_student')->where('exam_id', $quiz->id)->delete();
            foreach ($request->class_ids as $classId) {
                $this->assignQuizToStudentsByClass($quiz, (int) $classId);
            }

            DB::commit();

            Log::info('Quiz updated', ['quiz_id' => $quiz->id, 'mode' => $quiz->quiz_mode]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Quiz berhasil diperbarui!',
                    'redirect' => route('guru.quiz.index')
                ]);
            }

            return redirect()->route('guru.quiz.index')->with('success', 'Quiz berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating quiz: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal memperbarui quiz: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal memperbarui quiz: ' . $e->getMessage());
        }
    }

    // ==========================================
    // DESTROY
    // ==========================================

    /**
     * Remove the specified quiz
     */
    public function destroy(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $quiz->delete();

            return redirect()->route('guru.quiz.index')->with('success', 'Quiz berhasil dihapus!');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus quiz: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // ROOM — TAMPILAN
    // ==========================================

    /**
     * Show quiz room (halaman guru).
     * Digunakan untuk mode live dan guided.
     */
    public function showRoom(Exam $quiz)
    {
        $teacher = Auth::user()->teacher;

        if ($quiz->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke quiz ini.');
        }

        // Quiz Mandiri tidak punya room
        if ($quiz->quiz_mode === 'homework') {
            return redirect()->route('guru.quiz.preview', $quiz->id)
                ->with('info', 'Quiz Mandiri tidak memiliki ruangan. Lihat hasil di halaman preview.');
        }

        $quiz->load(['class', 'subject', 'questions', 'activeSession.participants.student']);

        return view('quiz.room', compact('quiz'));
    }

    /**
     * Halaman kontrol soal terpadu (guru).
     * GET /guru/quiz/{quiz}/guided
     * Route name: guru.quiz.guided
     *
     * Alias sederhana: redirect ke guidedControl jika quiz_mode === guided,
     * atau fallback ke preview. Ini mempertahankan kompatibilitas route lama
     * dari file perbaikan.
     */
    public function guided(Exam $quiz)
    {
        if ($quiz->quiz_mode !== 'guided') {
            return redirect()->route('guru.quiz.index')
                ->with('error', 'Quiz ini bukan mode Terpadu.');
        }

        return $this->guidedControl($quiz);
    }

    /**
     * Halaman kontrol soal terpadu (guru) — implementasi penuh.
     */
    public function guidedControl(Exam $quiz)
    {
        $teacher = Auth::user()->teacher;

        if ($quiz->teacher_id !== $teacher->id) {
            abort(403);
        }

        if ($quiz->quiz_mode !== 'guided') {
            return redirect()->route('guru.quiz.preview', $quiz->id)
                ->with('error', 'Halaman kontrol soal hanya tersedia untuk mode Terpadu.');
        }

        if (!$quiz->is_quiz_started) {
            return redirect()->route('guru.quiz.preview', $quiz->id)
                ->with('error', 'Quiz belum dimulai.');
        }

        $quiz->load(['class', 'subject', 'activeSession']);
        $questions      = $quiz->questions()->with('choices')->orderBy('order')->get();
        $totalQuestions = $questions->count();
        $currentIndex   = (int) ($quiz->guided_current_index ?? 0);
        $currentQuestion = $questions->get($currentIndex);
        $session        = $quiz->activeSession;
        $sessionCode    = $session?->session_code;

        $initialQuestion = $currentQuestion ? [
            'id'       => $currentQuestion->id,
            'question' => $currentQuestion->question,
            'type'     => $currentQuestion->type,
            'score'    => $currentQuestion->score,
            'choices'  => $currentQuestion->choices->map(fn($c) => [
                'id'         => $c->id,
                'label'      => $c->label,
                'text'       => $c->text,
                'is_correct' => (bool) $c->is_correct,
            ])->values()->toArray(),
        ] : null;

        $stats = ['joined' => 0, 'submitted' => 0];
        if ($session) {
            $stats['joined']    = $session->participants()->count();
            $stats['submitted'] = $session->participants()->where('status', 'submitted')->count();
        }

        return view('guru.quiz.petunjuk-soal', compact(
            'quiz',
            'questions',
            'totalQuestions',
            'currentIndex',
            'initialQuestion',
            'sessionCode',
            'stats'
        ));
    }

    // ==========================================
    // ROOM — BUKA / TUTUP / MULAI / HENTIKAN
    // ==========================================

    /**
     * Open quiz room — buat QuizSession baru.
     * Hanya untuk live dan guided.
     * Route: POST /guru/quiz/{quiz}/room/open
     * Name : guru.quiz.room.open
     */
    public function openRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::with(['class', 'subject'])->findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($quiz->quiz_mode === 'homework') {
                return response()->json(['success' => false, 'message' => 'Quiz Mandiri tidak menggunakan ruangan.'], 422);
            }

            if ($quiz->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Quiz sudah selesai dan tidak dapat dibuka kembali.'], 422);
            }

            if ($quiz->questions()->count() === 0) {
                return response()->json(['success' => false, 'message' => 'Quiz harus memiliki minimal 1 soal sebelum dibuka.'], 422);
            }

            if ($quiz->is_room_open) {
                return response()->json(['success' => false, 'message' => 'Ruangan sudah terbuka.'], 422);
            }

            DB::beginTransaction();

            $sessionCode = $this->generateUniqueSessionCode();

            $session = QuizSession::create([
                'exam_id'            => $quiz->id,
                'teacher_id'         => $user->teacher->id,
                'session_code'       => $sessionCode,
                'session_status'     => 'waiting',
                'session_started_at' => null,
                'session_ended_at'   => null,
                'total_duration'     => $quiz->duration,
                'total_students'     => 0,
                'students_joined'    => 0,
                'students_ready'     => 0,
                'students_started'   => 0,
                'students_submitted' => 0,
            ]);

            $quiz->update([
                'is_room_open'        => true,
                'is_quiz_started'     => false,
                'quiz_started_at'     => null,
                'quiz_remaining_time' => $quiz->duration * 60,
                'status'              => 'active',
            ]);

            DB::commit();

            Log::info('Quiz room opened', ['quiz_id' => $quiz->id, 'session_code' => $sessionCode, 'mode' => $quiz->quiz_mode]);

            return response()->json([
                'success'      => true,
                'message'      => 'Ruangan berhasil dibuka! Siswa dapat bergabung.',
                'session_code' => $sessionCode,
                'session_id'   => $session->id,
                'quiz_mode'    => $quiz->quiz_mode,
                'redirect'     => route('guru.quiz.room', $quiz->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error opening quiz room: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Close quiz room.
     * Tidak menghapus participants agar history kehadiran tetap terjaga.
     * Route: POST /guru/quiz/{quiz}/room/close
     * Name : guru.quiz.room.close
     */
    public function closeRoom(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();

            $quiz->update([
                'is_room_open'        => false,
                'is_quiz_started'     => false,
                'quiz_started_at'     => null,
                'quiz_remaining_time' => null,
            ]);

            if ($session = $quiz->activeSession) {
                $session->update([
                    'session_status'   => 'finished',
                    'session_ended_at' => now(),
                ]);
                // CATATAN: participants TIDAK dihapus agar history kehadiran terjaga
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Ruangan berhasil ditutup.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('closeRoom error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Start quiz — mulai sesi pengerjaan.
     * Live  : semua yang ready mulai sekaligus.
     * Guided: guru kontrol, semua siap dulu.
     * Route: POST /guru/quiz/{quiz}/room/start
     * Name : guru.quiz.room.start
     */
    public function startQuiz(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if (!$quiz->is_room_open) {
                return response()->json(['success' => false, 'message' => 'Buka ruangan terlebih dahulu'], 422);
            }

            if ($quiz->is_quiz_started) {
                return response()->json(['success' => false, 'message' => 'Quiz sudah dimulai'], 422);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session tidak ditemukan'], 422);
            }

            DB::beginTransaction();

            $updateData = [
                'is_quiz_started'     => true,
                'quiz_started_at'     => now(),
                'quiz_remaining_time' => $quiz->duration * 60,
            ];

            // Guided mode: set index soal pertama dan deadline timer
            if ($quiz->quiz_mode === 'guided') {
                $timePerQuestion = (int) ($quiz->time_per_question ?? 0);
                $deadline = $timePerQuestion > 0 ? now()->addSeconds($timePerQuestion)->timestamp : null;

                $updateData['guided_current_index']     = 0;
                $updateData['guided_show_answer']       = false;
                $updateData['guided_question_deadline'] = $deadline;
            }

            $quiz->update($updateData);

            $session->update([
                'session_status'     => 'active',
                'session_started_at' => now(),
            ]);

            // Buat ExamAttempt untuk semua peserta yang ready
            $readyParticipants = $session->participants()->where('status', 'ready')->get();

            foreach ($readyParticipants as $participant) {
                ExamAttempt::create([
                    'exam_id'          => $quiz->id,
                    'student_id'       => $participant->student_id,
                    'quiz_session_id'  => $session->id,
                    'started_at'       => now(),
                    'status'           => 'in_progress',
                    'remaining_time'   => $quiz->duration * 60,
                    'ip_address'       => $participant->ip_address,
                    'user_agent'       => $participant->user_agent,
                    'exam_settings'    => json_encode([
                        'quiz_mode'      => $quiz->quiz_mode,
                        'guided_current' => $quiz->quiz_mode === 'guided' ? 0 : null,
                    ]),
                ]);

                $participant->update(['status' => 'started', 'started_at' => now()]);
            }

            // Juga ubah peserta waiting → started agar tidak tertinggal
            $session->participants()
                ->where('status', 'waiting')
                ->update(['status' => 'started', 'started_at' => now()]);

            $session->updateStats();

            DB::commit();

            Log::info('Quiz started', [
                'quiz_id' => $quiz->id,
                'mode'    => $quiz->quiz_mode,
                'started' => $readyParticipants->count(),
            ]);

            return response()->json([
                'success'       => true,
                'message'       => 'Quiz dimulai! ' . $readyParticipants->count() . ' peserta mulai mengerjakan.',
                'started_count' => $readyParticipants->count(),
                'quiz_mode'     => $quiz->quiz_mode,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('startQuiz error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Stop quiz — hentikan paksa, submit semua attempt yang masih in_progress.
     * Route: POST /guru/quiz/{quiz}/room/stop
     * Name : guru.quiz.room.stop
     */
    public function stopQuiz(Request $request, $quizId)
    {
        try {
            $user = Auth::user();
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != $user->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session tidak ditemukan'], 422);
            }

            DB::beginTransaction();

            $quiz->update([
                'is_room_open'        => false,
                'is_quiz_started'     => false,
                'quiz_started_at'     => null,
                'quiz_remaining_time' => null,
                'status'              => 'finished',
            ]);

            $session->update([
                'session_status'   => 'finished',
                'session_ended_at' => now(),
            ]);

            // Force-submit semua ExamAttempt yang masih in_progress
            $activeAttempts = ExamAttempt::where('exam_id', $quiz->id)
                ->where('quiz_session_id', $session->id)
                ->where('status', 'in_progress')
                ->get();

            foreach ($activeAttempts as $attempt) {
                // Gunakan method submit() di model jika ada, fallback ke update manual
                if (method_exists($attempt, 'submit')) {
                    $attempt->submit();
                } else {
                    $attempt->update([
                        'status'       => 'timeout',
                        'submitted_at' => now(),
                        'ended_at'     => now(),
                    ]);
                }
            }

            // Update status semua peserta yang belum submit
            $session->participants()
                ->whereIn('status', ['started', 'waiting', 'ready'])
                ->update(['status' => 'submitted', 'submitted_at' => now()]);

            $session->updateStats();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Quiz dihentikan! Semua peserta telah di-submit otomatis.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('stopQuiz error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // ROOM — STATUS (POLLING ENDPOINT)
    // ==========================================

    /**
     * Get room status untuk guru (polling setiap ~3 detik).
     *
     * Menghitung sisa waktu secara real-time dari quiz_started_at + duration,
     * memicu auto-close jika waktu habis, serta menyertakan detail violations
     * dan has_violation per participant agar kartu merah & modal pelanggaran
     * berfungsi di tampilan guru.
     *
     * Route: GET /guru/quiz/{quiz}/room/status
     * Name : guru.quiz.room.status
     */
    public function getRoomStatus(Exam $quiz)
    {
        try {
            $teacher = Auth::user()->teacher;

            if ($quiz->teacher_id !== $teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // ── Auto-close jika waktu sudah habis ────────────────────────────
            $quiz->refresh();

            $quizExpired = false;
            if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
                $elapsedSeconds = now()->diffInSeconds($quiz->quiz_started_at);
                $totalSeconds   = $quiz->duration * 60;
                $timeRemaining  = max(0, $totalSeconds - $elapsedSeconds);

                if ($timeRemaining <= 0) {
                    $wasJustClosed = $quiz->autoCloseIfExpired();
                    $quiz->refresh();

                    if ($wasJustClosed || !$quiz->is_quiz_started) {
                        $quizExpired = true;
                    }
                }
            }
            // ─────────────────────────────────────────────────────────────────

            $session = $quiz->activeSession;

            // Jika quiz expired, cari session yang baru saja ditutup
            if (!$session && $quizExpired) {
                $session = QuizSession::where('exam_id', $quiz->id)
                    ->orderByDesc('created_at')
                    ->first();
            }

            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Session tidak aktif'], 422);
            }

            // ── Hitung time_remaining real-time ───────────────────────────────
            $timeRemaining = null;
            if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
                $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
            } elseif ($quiz->duration > 0 && !$quiz->is_quiz_started) {
                $timeRemaining = $quiz->duration * 60;
            }
            // ─────────────────────────────────────────────────────────────────

            $participants = $session->participants()
                ->with(['student:id,name,email'])
                ->get()
                ->map(function ($participant) use ($quiz) {
                    $attempt = ExamAttempt::where('student_id', $participant->student_id)
                        ->where('exam_id', $quiz->id)
                        ->where('quiz_session_id', $participant->quiz_session_id)
                        ->first();

                    // ── Detail violations untuk modal guru ────────────────────
                    $violations = $this->buildViolations($participant);

                    // PENTING: violation_count SELALU diambil dari participant,
                    // bukan dari attempt. Pelanggaran dicatat oleh logViolation()
                    // ke tabel quiz_participants, bukan exam_attempts.
                    $violationCount = (int) ($participant->violation_count ?? 0);

                    $isCheating = $attempt ? $attempt->is_cheating_detected : false;

                    // Unread warnings (teacher_warning yang belum dibaca siswa)
                    $log            = $participant->violation_log ?? [];
                    if (is_string($log)) $log = json_decode($log, true) ?: [];
                    $unreadWarnings = count(array_filter(
                        $log,
                        fn($l) => ($l['type'] ?? '') === 'teacher_warning' && !($l['read'] ?? false)
                    ));
                    // ──────────────────────────────────────────────────────────

                    return [
                        'id'                   => $participant->id,
                        'student_id'           => $participant->student_id,
                        'student_name'         => $participant->student->name ?? 'Unknown',
                        'student_email'        => $participant->student->email ?? '',
                        'status'               => $participant->status,
                        'joined_at'            => optional($participant->joined_at)->format('H:i:s'),
                        'ready_at'             => optional($participant->ready_at)->format('H:i:s'),
                        'started_at'           => optional($participant->started_at)->format('H:i:s'),
                        'submitted_at'         => optional($participant->submitted_at)->format('H:i:s'),
                        'is_present'           => (bool) $participant->is_present,
                        'violation_count'      => (int) $violationCount,
                        'violations'           => $violations,      // ← detail untuk modal guru
                        'has_violation'        => $violationCount > 0, // ← true = card merah
                        'is_cheating_detected' => $isCheating,
                        'unread_warnings'      => $unreadWarnings,
                        'score'                => $attempt ? round($attempt->final_score ?? 0, 2) : null,
                    ];
                });

            $stats = $session->updateStats();

            return response()->json([
                'success'          => true,
                'quiz_expired'     => $quizExpired,
                'time_remaining'   => $timeRemaining,
                'is_room_open'     => (bool) $quiz->is_room_open,
                'is_quiz_started'  => (bool) $quiz->is_quiz_started,
                'room_status'      => $quiz->is_room_open
                    ? ($quiz->is_quiz_started ? 'started' : 'open')
                    : 'closed',
                'quiz'             => [
                    'id'                   => $quiz->id,
                    'title'                => $quiz->title,
                    'quiz_mode'            => $quiz->quiz_mode,
                    'is_room_open'         => (bool) $quiz->is_room_open,
                    'is_quiz_started'      => (bool) $quiz->is_quiz_started,
                    'quiz_started_at'      => $quiz->quiz_started_at?->format('Y-m-d H:i:s'),
                    'quiz_remaining_time'  => $timeRemaining,
                    'duration'             => $quiz->duration,
                    'guided_current_index' => $quiz->guided_current_index ?? 0,
                    'total_questions'      => $quiz->questions()->count(),
                ],
                'session'          => [
                    'id'             => $session->id,
                    'session_code'   => $session->session_code,
                    'session_status' => $session->session_status,
                ],
                'stats'            => $stats,
                'participants'     => $participants,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting room status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get room status untuk SISWA.
     * Mengirim time_remaining real-time agar countdown siswa sinkron dengan server.
     * Juga memicu autoCloseIfExpired jika waktu habis.
     *
     * Route: GET /quiz/{quiz}/room/status
     * Name : quiz.room.status
     *
     * ╔══════════════════════════════════════════════════════════════════╗
     * ║  TAMBAHKAN ROUTE INI di routes/web.php (grup siswa/quiz):       ║
     * ║                                                                  ║
     * ║  Route::get('/quiz/{quiz}/room/status',                          ║
     * ║      [QuizController::class, 'getStudentRoomStatus'])            ║
     * ║      ->name('quiz.room.status');                                 ║
     * ╚══════════════════════════════════════════════════════════════════╝
     */
    public function getStudentRoomStatus(Exam $quiz)
    {
        try {
            $quiz->refresh();

            $quizExpired   = false;
            $timeRemaining = null;

            if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
                $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
                $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);

                if ($timeRemaining <= 0) {
                    $wasJustClosed = $quiz->autoCloseIfExpired();
                    $quiz->refresh();
                    if ($wasJustClosed || !$quiz->is_quiz_started) {
                        $quizExpired   = true;
                        $timeRemaining = 0;
                    }
                }
            } elseif ($quiz->duration > 0) {
                $timeRemaining = $quiz->duration * 60;
            }

            $session     = $quiz->activeSession;
            $user        = Auth::user();
            $participant = null;
            $stats       = ['joined' => 0, 'ready' => 0, 'started' => 0, 'submitted' => 0];

            if ($session) {
                $participant = QuizParticipant::where('quiz_session_id', $session->id)
                    ->where('student_id', $user->id)
                    ->first();

                $stats = [
                    'joined'    => $session->participants()->count(),
                    'ready'     => $session->participants()->where('status', 'ready')->count(),
                    'started'   => $session->participants()->where('status', 'started')->count(),
                    'submitted' => $session->participants()->where('status', 'submitted')->count(),
                ];
            }

            return response()->json([
                'success'         => true,
                'quiz_expired'    => $quizExpired,
                'time_remaining'  => $timeRemaining,
                'is_room_open'    => (bool) $quiz->is_room_open,
                'is_quiz_started' => (bool) $quiz->is_quiz_started,
                'quiz'            => [
                    'is_room_open'        => (bool) $quiz->is_room_open,
                    'is_quiz_started'     => (bool) $quiz->is_quiz_started,
                    'quiz_remaining_time' => $timeRemaining,
                    'duration'            => $quiz->duration,
                ],
                'stats'       => $stats,
                'participant' => $participant ? [
                    'id'     => $participant->id,
                    'status' => $participant->status,
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStudentRoomStatus: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get room participants list (AJAX)
     */
    public function getRoomParticipants(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session      = $quiz->activeSession;
        $participants = $session ? $session->participants()->with('student')->get() : collect();
        $stats        = $quiz->getParticipantStats();

        $formatted = $participants->map(fn($p) => [
            'id'           => $p->id,
            'name'         => $p->student->name ?? 'Unknown',
            'email'        => $p->student->email ?? '',
            'status'       => $p->status,
            'joined_at'    => $p->joined_at?->format('H:i'),
            'ready_at'     => $p->ready_at?->format('H:i'),
            'started_at'   => $p->started_at?->format('H:i'),
            'submitted_at' => $p->submitted_at?->format('H:i'),
            'is_present'   => (bool) $p->is_present,
        ]);

        // Hitung time_remaining real-time
        $timeRemaining = null;
        if ($quiz->is_quiz_started && $quiz->quiz_started_at && $quiz->duration > 0) {
            $elapsed       = now()->diffInSeconds($quiz->quiz_started_at);
            $timeRemaining = max(0, ($quiz->duration * 60) - $elapsed);
        } elseif ($quiz->duration > 0) {
            $timeRemaining = $quiz->duration * 60;
        }

        return response()->json([
            'success'         => true,
            'participants'    => $formatted,
            'stats'           => $stats,
            'time_remaining'  => $timeRemaining,
            'is_quiz_started' => (bool) $quiz->is_quiz_started,
            'is_room_open'    => (bool) $quiz->is_room_open,
            'quiz_started'    => (bool) $quiz->is_quiz_started,
            'room_open'       => (bool) $quiz->is_room_open,
        ]);
    }

    // ==========================================
    // ROOM — KELOLA PESERTA
    // ==========================================

    /**
     * Kick participant — ubah status ke 'kicked', record tetap ada untuk history.
     * Route: POST /guru/quiz/{quiz}/room/kick/{participant}
     * Name : guru.quiz.room.kick
     */
    public function kickParticipant(Request $request, $quizId, $participantId)
    {
        try {
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != Auth::user()->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Sesi tidak aktif'], 422);
            }

            $participant = QuizParticipant::where('id', $participantId)
                ->where('quiz_session_id', $session->id)
                ->first();

            if (!$participant) {
                return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            }

            $name = $participant->student->name ?? 'Peserta';

            // Update status bukan delete agar history kehadiran terjaga
            $participant->update(['status' => 'kicked', 'is_present' => false]);
            $session->updateStats();

            return response()->json(['success' => true, 'message' => "{$name} berhasil dikeluarkan."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark participant as ready.
     * Route: POST /guru/quiz/{quiz}/room/mark-ready/{participant}
     * Name : guru.quiz.room.mark-ready
     */
    public function markParticipantReady(Request $request, $quizId, $participantId)
    {
        try {
            $quiz = Exam::findOrFail($quizId);

            if ($quiz->teacher_id != Auth::user()->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $session = $quiz->activeSession;
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'Sesi tidak aktif'], 422);
            }

            $participant = QuizParticipant::where('id', $participantId)
                ->where('quiz_session_id', $session->id)
                ->first();

            if (!$participant) {
                return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            }

            $participant->update(['status' => 'ready', 'ready_at' => now()]);
            $session->updateStats();

            return response()->json(['success' => true, 'message' => 'Status peserta berhasil diubah menjadi siap!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // VIOLATION & WARNING MANAGEMENT
    // ==========================================

    /**
     * Kirim peringatan ke peserta tertentu (live & guided).
     * Peringatan disimpan ke violation_log dengan type='teacher_warning'
     * agar siswa bisa polling via getWarnings().
     *
     * Route: POST /guru/quiz/{quiz}/room/participant/{participant}/warn
     * Route: POST /guru/quiz/{quiz}/room/warn/{participant}  (alias lama)
     */
    public function warnParticipant(Request $request, Exam $quiz, $participantId)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session = $quiz->activeSession;
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak aktif'], 422);
        }

        $participant = QuizParticipant::where('id', $participantId)
            ->where('quiz_session_id', $session->id)
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
        }

        $request->validate([
            'message'    => 'nullable|string|max:500',
            'alert_type' => 'nullable|in:message,sound,vibrate',
        ]);

        $name    = $participant->student->name ?? 'siswa';
        $message = $request->input('message')
            ?: "{$name}, guru mengingatkanmu untuk tidak melakukan kecurangan selama quiz berlangsung!";

        // Simpan peringatan ke violation_log agar getWarnings() bisa membacanya
        $log   = $participant->violation_log ?? [];
        if (is_string($log)) $log = json_decode($log, true) ?: [];

        $log[] = [
            'type'      => 'teacher_warning',
            'message'   => $message,
            'alert'     => $request->alert_type ?? 'message',
            'timestamp' => now()->toDateTimeString(),
            'read'      => false,
        ];

        $participant->violation_log = $log;
        $participant->save();

        Log::info('Teacher warning sent', [
            'quiz_id'        => $quiz->id,
            'participant_id' => $participant->id,
            'alert_type'     => $request->alert_type ?? 'message',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Peringatan berhasil dikirim ke {$name}.",
        ]);
    }

    /**
     * Siswa polling peringatan dari guru.
     * Route: GET /quiz/{quiz}/room/warnings
     */
    public function getWarnings(Request $request, Exam $quiz)
    {
        $user    = Auth::user();
        $session = $quiz->activeSession;

        if (!$session) {
            return response()->json(['warnings' => []]);
        }

        $participant = QuizParticipant::where('quiz_session_id', $session->id)
            ->where('student_id', $user->id)
            ->first();

        if (!$participant) {
            return response()->json(['warnings' => []]);
        }

        $log = $participant->violation_log ?? [];
        if (is_string($log)) $log = json_decode($log, true) ?: [];

        $warnings = array_filter($log, fn($l) => ($l['type'] ?? '') === 'teacher_warning' && !($l['read'] ?? false));

        // Tandai sudah dibaca
        $updatedLog = array_map(function ($item) {
            if (($item['type'] ?? '') === 'teacher_warning') {
                $item['read'] = true;
            }
            return $item;
        }, $log);

        $participant->violation_log = $updatedLog;
        $participant->save();

        return response()->json([
            'warnings' => array_values($warnings),
        ]);
    }

    // ==========================================
    // GUIDED MODE CONTROLS
    // ==========================================

    /**
     * [GUIDED] Set timer per soal.
     * Route: POST /guru/quiz/{quiz}/room/guided/set-time
     */
    public function guidedSetTime(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['time_per_question' => 'required|integer|min:0|max:300']);

        $seconds  = (int) $request->input('time_per_question');
        $deadline = $seconds > 0 ? now()->addSeconds($seconds)->timestamp : null;

        $quiz->update([
            'time_per_question'        => $seconds,
            'guided_question_deadline' => $deadline,
            'guided_show_answer'       => false,
        ]);

        return response()->json([
            'success'           => true,
            'time_per_question' => $seconds,
            'question_deadline' => $deadline,
            'message'           => $seconds > 0
                ? "Timer {$seconds} detik/soal aktif!"
                : 'Mode manual aktif (tanpa timer)',
        ]);
    }

    /**
     * [GUIDED] Navigasi ke soal berikutnya.
     * Route: POST /guru/quiz/{quiz}/room/guided/next
     */
    public function guidedNext(Request $request, Exam $quiz)
    {
        return $this->guidedNavigate($quiz, 'next');
    }

    /**
     * [GUIDED] Kembali ke soal sebelumnya.
     * Route: POST /guru/quiz/{quiz}/room/guided/prev
     */
    public function guidedPrev(Request $request, Exam $quiz)
    {
        return $this->guidedNavigate($quiz, 'prev');
    }

    /**
     * [GUIDED] Pindah ke soal tertentu.
     * Route: POST /guru/quiz/{quiz}/room/guided/goto
     */
    public function guidedGoto(Request $request, Exam $quiz)
    {
        $request->validate(['index' => 'required|integer|min:0']);
        return $this->guidedNavigate($quiz, 'goto', $request->index);
    }

    /**
     * Implementasi navigasi soal guided — dipakai oleh next/prev/goto.
     */
    private function guidedNavigate(Exam $quiz, string $direction, int $targetIndex = 0)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->quiz_mode !== 'guided') {
            return response()->json(['error' => 'Bukan mode Quiz Terpandu'], 422);
        }

        $session = $quiz->activeSession;
        if (!$session || !$quiz->is_quiz_started) {
            return response()->json(['error' => 'Quiz belum dimulai'], 422);
        }

        $totalQuestions = $quiz->questions()->count();
        $current        = (int) ($quiz->guided_current_index ?? 0);

        $newIndex = match ($direction) {
            'next'  => min($current + 1, $totalQuestions - 1),
            'prev'  => max($current - 1, 0),
            'goto'  => max(0, min($targetIndex, $totalQuestions - 1)),
            default => $current,
        };

        // Hitung deadline baru berdasarkan time_per_question
        $timePerQuestion = (int) ($quiz->time_per_question ?? 0);
        $deadline        = $timePerQuestion > 0 ? now()->addSeconds($timePerQuestion)->timestamp : null;

        $quiz->update([
            'guided_current_index'     => $newIndex,
            'guided_question_deadline' => $deadline,
            'guided_show_answer'       => false, // Reset untuk soal baru
        ]);

        $currentQuestion = $quiz->questions()->with('choices')->orderBy('order')->skip($newIndex)->first();

        return response()->json([
            'success'           => true,
            'current_index'     => $newIndex,
            'total_questions'   => $totalQuestions,
            'is_first'          => $newIndex === 0,
            'is_last'           => $newIndex === $totalQuestions - 1,
            'question_deadline' => $deadline,
            'time_per_question' => $timePerQuestion,
            'show_answer'       => false,
            'question'          => $currentQuestion ? [
                'id'       => $currentQuestion->id,
                'question' => $currentQuestion->question,
                'type'     => $currentQuestion->type,
                'score'    => $currentQuestion->score,
                'choices'  => $currentQuestion->choices->map(fn($c) => [
                    'id'         => $c->id,
                    'label'      => $c->label,
                    'text'       => $c->text,
                    'is_correct' => (bool) $c->is_correct, // Guru boleh lihat
                ])->values(),
            ] : null,
        ]);
    }

    /**
     * [GUIDED] Ambil soal aktif saat ini — untuk polling siswa.
     * Siswa TIDAK mendapat is_correct.
     * Route: GET /quiz/{quiz}/room/guided/current
     */
    public function guidedCurrentQuestion(Exam $quiz)
    {
        if ($quiz->quiz_mode !== 'guided') {
            return response()->json(['error' => 'Bukan mode Quiz Terpandu'], 422);
        }

        if (!$quiz->is_quiz_started) {
            return response()->json(['waiting' => true, 'message' => 'Menunggu guru memulai quiz...']);
        }

        $currentIndex    = (int) ($quiz->guided_current_index ?? 0);
        $totalQuestions  = $quiz->questions()->count();
        $currentQuestion = $quiz->questions()->with('choices')->orderBy('order')->skip($currentIndex)->first();
        $now             = now()->timestamp;
        $deadline        = $quiz->guided_question_deadline;

        $timeLeft = null;
        if ($deadline) {
            $timeLeft = max(0, $deadline - $now);
        }

        return response()->json([
            'success'           => true,
            'current_index'     => $currentIndex,
            'total'             => $totalQuestions,
            'question_deadline' => $deadline,
            'time_left'         => $timeLeft,
            'time_per_question' => (int) ($quiz->time_per_question ?? 0),
            'question'          => $currentQuestion ? [
                'id'       => $currentQuestion->id,
                'question' => $currentQuestion->question,
                'type'     => $currentQuestion->type,
                'score'    => $currentQuestion->score,
                'choices'  => $currentQuestion->choices->map(fn($c) => [
                    'id'    => $c->id,
                    'label' => $c->label,
                    'text'  => $c->text,
                    // is_correct TIDAK dikirim ke siswa
                ])->values(),
            ] : null,
        ]);
    }

    /**
     * [GUIDED] Guru tampilkan jawaban benar.
     * Route: POST /guru/quiz/{quiz}/room/guided/show-answer  (alias lama)
     * Route: POST /guru/quiz/{quiz}/room/guided/reveal       (nama baru)
     */
    public function guidedShowAnswer(Request $request, Exam $quiz)
    {
        return $this->guidedRevealAnswer($quiz);
    }

    /**
     * [GUIDED] Guru: set show_answer = true — implementasi utama.
     */
    public function guidedRevealAnswer(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $quiz->update(['guided_show_answer' => true]);

        return response()->json(['success' => true, 'show_answer' => true]);
    }

    /**
     * [GUIDED] Guru polling state soal aktif (timer, show_answer).
     * Route: GET /guru/quiz/{quiz}/room/guided/state
     */
    public function guidedState(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $now      = now()->timestamp;
        $deadline = $quiz->guided_question_deadline;
        $timeLeft = $deadline ? max(0, $deadline - $now) : null;

        $currentIndex    = (int) ($quiz->guided_current_index ?? 0);
        $totalQuestions  = $quiz->questions()->count();
        $currentQuestion = $quiz->questions()->with('choices')->orderBy('order')->skip($currentIndex)->first();

        $session = $quiz->activeSession;
        $stats   = ['joined' => 0, 'submitted' => 0];
        if ($session) {
            $stats['joined']    = $session->participants()->count();
            $stats['submitted'] = $session->participants()->where('status', 'submitted')->count();
        }

        return response()->json([
            'success'           => true,
            'current_index'     => $currentIndex,
            'total_questions'   => $totalQuestions,
            'question_deadline' => $deadline,
            'time_left'         => $timeLeft,
            'show_answer'       => (bool) $quiz->guided_show_answer,
            'time_per_question' => (int) ($quiz->time_per_question ?? 0),
            'is_first'          => $currentIndex === 0,
            'is_last'           => $currentIndex === $totalQuestions - 1,
            'stats'             => $stats,
            'question'          => $currentQuestion ? [
                'id'       => $currentQuestion->id,
                'question' => $currentQuestion->question,
                'type'     => $currentQuestion->type,
                'score'    => $currentQuestion->score,
                'choices'  => $currentQuestion->choices->map(fn($c) => [
                    'id'         => $c->id,
                    'label'      => $c->label,
                    'text'       => $c->text,
                    'is_correct' => (bool) $c->is_correct, // Guru boleh lihat
                ])->values(),
            ] : null,
        ]);
    }

    // ==========================================
    // LEADERBOARD
    // ==========================================

    /**
     * Leaderboard ringkas (JSON) — dipanggil dari room blade via AJAX.
     * Route: GET /guru/quiz/{quiz}/leaderboard  (JSON)
     */
    public function leaderboard(Exam $quiz)
    {
        $leaderboard = ExamAttempt::where('exam_id', $quiz->id)
            ->whereIn('status', ['submitted', 'timeout'])
            ->orderByDesc('final_score')
            ->orderBy('submitted_at')
            ->take(50)
            ->get()
            ->map(function ($a, $i) {
                $user = \App\Models\User::find($a->student_id);
                return [
                    'rank'         => $i + 1,
                    'student_name' => $user?->name ?? 'Peserta',
                    'score'        => round($a->final_score ?? 0, 1),
                    'time_taken'   => $a->started_at && $a->ended_at
                        ? $a->started_at->diffInSeconds($a->ended_at)
                        : 0,
                ];
            })->values();

        return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
    }

    /**
     * Leaderboard lengkap (view + JSON) — halaman dedicated.
     * Route: GET /guru/quiz/{quiz}/leaderboard/full
     */
    public function quizLeaderboard($quizId)
    {
        try {
            $quiz = Exam::findOrFail($quizId);

            $attempts = ExamAttempt::where('exam_id', $quiz->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->with('student:id,name,email')
                ->orderByDesc('final_score')
                ->orderBy('ended_at', 'asc')
                ->limit(10)
                ->get();

            $leaderboard = $attempts->map(function ($attempt, $index) {
                $studentName = $attempt->student?->name ?? \App\Models\User::find($attempt->student_id)?->name ?? 'Peserta ' . $attempt->student_id;
                $timeTaken   = $attempt->ended_at && $attempt->started_at
                    ? $attempt->started_at->diffInSeconds($attempt->ended_at)
                    : 0;

                return [
                    'rank'         => $index + 1,
                    'student_id'   => $attempt->student_id,
                    'student_name' => $studentName,
                    'name'         => $studentName,
                    'score'        => round($attempt->final_score ?? 0, 2),
                    'final_score'  => round($attempt->final_score ?? 0, 2),
                    'time_taken'   => $timeTaken,
                    'submitted_at' => $attempt->ended_at?->format('Y-m-d H:i:s'),
                    'status'       => $attempt->status,
                ];
            });

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'leaderboard' => $leaderboard, 'count' => $leaderboard->count()]);
            }

            return view('guru.quiz.leaderboard', compact('quiz', 'leaderboard'));
        } catch (\Exception $e) {
            Log::error('Error in quizLeaderboard: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('guru.quiz.index');
        }
    }

    // ==========================================
    // RESULTS
    // ==========================================

    /**
     * Results ringkas (view) — dipanggil langsung setelah quiz selesai.
     * Route: GET /guru/quiz/{quiz}/results
     * Name : guru.quiz.results
     */
    public function results(Exam $quiz)
    {
        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->whereIn('status', ['submitted', 'timeout'])
            ->with('student:id,name,email')
            ->orderByDesc('final_score')
            ->get();

        return view('guru.quiz.results', compact('quiz', 'attempts'));
    }

    /**
     * Results lengkap dengan statistik dan pagination.
     * Route: GET /guru/quiz/{quiz}/results/full
     */
    public function quizResults(Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) abort(403);

        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_attempts' => ExamAttempt::where('exam_id', $quiz->id)->count(),
            'average_score'  => round(ExamAttempt::where('exam_id', $quiz->id)->avg('final_score') ?? 0, 2),
            'highest_score'  => ExamAttempt::where('exam_id', $quiz->id)->max('final_score') ?? 0,
            'lowest_score'   => ExamAttempt::where('exam_id', $quiz->id)->min('final_score') ?? 0,
        ];

        return view('guru.quiz.results', compact('quiz', 'attempts', 'stats'));
    }

    public function attemptDetail(Exam $quiz, ExamAttempt $attempt)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) abort(403);

        $quiz = $attempt->exam;
        if ($quiz->teacher_id !== Auth::user()->teacher->id) abort(403);

        $answers        = ExamAnswer::where('attempt_id', $attempt->id)
            ->with(['question', 'choice', 'question.choices' => fn($q) => $q->where('is_correct', true)])
            ->get();
        $totalQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $score          = $attempt->final_score;

        return view('guru.quiz.attempt-detail', compact('attempt', 'quiz', 'answers', 'totalQuestions', 'correctAnswers', 'score'));
    }

    public function exportResults(Exam $quiz, $format = 'excel')
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) abort(403);

        // TODO: implementasi export Excel/PDF
        return response()->json(['success' => true, 'message' => 'Export feature coming soon']);
    }

    public function studentResults(Exam $quiz, $studentId)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) abort(403);

        $attempts = ExamAttempt::where('exam_id', $quiz->id)
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
        $student  = \App\Models\User::findOrFail($studentId);

        return view('guru.quiz.student-results', compact('quiz', 'attempts', 'student'));
    }

    // ==========================================
    // QUESTION MANAGEMENT
    // ==========================================

    public function importFile(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();

            $rows = $ext === 'csv'
                ? $this->parseImportCsv($path)
                : $this->parseImportExcel($path);

            if (empty($rows)) {
                return response()->json(['success' => false, 'message' => 'File kosong atau tidak dapat dibaca'], 422);
            }

            $questions = [];
            foreach ($rows as $i => $row) {
                $r = [];
                foreach ($row as $k => $v) {
                    $key    = strtolower(trim(preg_replace('/\s+/', '_', (string) $k)));
                    $r[$key] = is_string($v) ? trim($v) : $v;
                }

                $question = trim($r['pertanyaan'] ?? $r['question'] ?? $r['soal'] ?? '');
                if (empty($question)) continue;

                $optA = trim($r['opsi_a'] ?? $r['option_a'] ?? $r['a'] ?? '');
                $optB = trim($r['opsi_b'] ?? $r['option_b'] ?? $r['b'] ?? '');
                $optC = trim($r['opsi_c'] ?? $r['option_c'] ?? $r['c'] ?? '');
                $optD = trim($r['opsi_d'] ?? $r['option_d'] ?? $r['d'] ?? '');
                $optE = trim($r['opsi_e'] ?? $r['option_e'] ?? $r['e'] ?? '');

                if (empty($optA) || empty($optB)) continue;

                $correct = strtoupper(trim(
                    $r['jawaban'] ?? $r['correct_answer'] ?? $r['answer'] ?? $r['kunci'] ?? ''
                ));
                if (!in_array($correct, ['A', 'B', 'C', 'D', 'E'])) continue;

                $correctIndex = ord($correct) - ord('A');
                $score        = (int) ($r['skor'] ?? $r['score'] ?? $r['nilai'] ?? 10);
                $score        = max(1, min(100, $score));
                $explanation  = trim($r['pembahasan'] ?? $r['explanation'] ?? '');

                $optList = array_filter([$optA, $optB, $optC, $optD, $optE]);
                $choices = [];
                foreach (array_values($optList) as $idx => $text) {
                    $choices[] = [
                        'text'       => $text,
                        'is_correct' => $idx === $correctIndex,
                    ];
                }

                $questions[] = [
                    'type'        => 'PG',
                    'question'    => $question,
                    'score'       => $score,
                    'explanation' => $explanation,
                    'choices'     => $choices,
                ];
            }

            if (empty($questions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada soal valid. Pastikan format kolom benar dan kolom jawaban berisi A/B/C/D.',
                ], 422);
            }

            return response()->json([
                'success'   => true,
                'questions' => $questions,
                'message'   => count($questions) . ' soal berhasil dibaca dari file',
            ]);
        } catch (\Exception $e) {
            Log::error('importFile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function parseImportCsv(string $path): array
    {
        $rows    = [];
        $headers = null;

        if (($handle = fopen($path, 'r')) === false) return [];

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom !== "\xEF\xBB\xBF") {
            // skip, handle sudah di-rewind
        } else {
            fread($handle, 3); // skip BOM again
        }
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if ($headers === null) {
                $headers = $data;
                continue;
            }
            if (empty(array_filter($data))) continue;
            $padded = array_pad($data, count($headers), '');
            $rows[] = array_combine($headers, array_slice($padded, 0, count($headers)));
        }
        fclose($handle);
        return $rows;
    }

    private function parseImportExcel(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \Exception('PhpSpreadsheet tidak terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        if (empty($rows)) return [];

        $headers = array_shift($rows);
        $headers = array_map(fn($h) => is_null($h) ? '' : (string) $h, $headers);

        $result = [];
        foreach ($rows as $row) {
            if (empty(array_filter(array_map('strval', $row)))) continue;
            $padded   = array_pad($row, count($headers), null);
            $result[] = array_combine($headers, array_slice($padded, 0, count($headers)));
        }
        return $result;
    }

    public function importPreview($quizId)
    {
        $sourceExam = Exam::findOrFail($quizId);

        if ($sourceExam->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $questions = $sourceExam->questions()->with('choices')->get();

        $formatted = $questions->map(function ($q) {
            $data = [
                'id'          => $q->id,
                'question'    => $q->question,
                'type'        => $q->type,
                'score'       => $q->score,
                'explanation' => $q->explanation,
            ];
            if ($q->type === 'PG') {
                $data['choices'] = $q->choices->map(fn($c) => ['text' => $c->text, 'is_correct' => $c->is_correct])->toArray();
            } elseif ($q->type === 'IS') {
                $data['short_answers'] = json_decode($q->short_answers ?? '[]');
            }
            return $data;
        });

        return response()->json(['success' => true, 'questions' => $formatted]);
    }

    public function getQuestions(Exam $quiz)
    {
        try {
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $questions = $quiz->questions()->with('choices')->orderBy('order')->get();

            return response()->json([
                'success'         => true,
                'questions'       => $questions,
                'total_questions' => $questions->count(),
                'total_score'     => $questions->sum('score'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting questions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil soal: ' . $e->getMessage()], 500);
        }
    }

    public function storeQuestions(Request $request, Exam $quiz)
    {
        try {
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($quiz->status === 'finished') {
                return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat diubah soalnya.'], 422);
            }

            $validator = Validator::make($request->all(), [
                'questions'                        => 'required|array|min:1',
                'questions.*.question'             => 'required|string|max:5000',
                'questions.*.type'                 => 'required|in:PG,IS,ES',
                'questions.*.score'                => 'required|integer|min:1|max:100',
                'questions.*.explanation'          => 'nullable|string|max:1000',
                'questions.*.choices'              => 'required_if:questions.*.type,PG|array|min:2|max:6',
                'questions.*.choices.*.text'       => 'required_with:questions.*.choices|string|max:1000',
                'questions.*.choices.*.is_correct' => 'required_if:questions.*.type,PG|boolean',
                'questions.*.short_answers'        => 'required_if:questions.*.type,IS|array|min:1',
                'questions.*.short_answers.*'      => 'required_with:questions.*.short_answers|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();
            $quiz->questions()->delete();

            $createdQuestions = [];
            $order = 0;

            foreach ($request->questions as $qData) {
                $question = ExamQuestion::create([
                    'exam_id'     => $quiz->id,
                    'question'    => $qData['question'],
                    'type'        => $qData['type'],
                    'score'       => $qData['score'],
                    'explanation' => $qData['explanation'] ?? null,
                    'order'       => $order++,
                ]);

                if ($qData['type'] === 'PG' && !empty($qData['choices'])) {
                    $ci = 0;
                    foreach ($qData['choices'] as $c) {
                        if (!empty(trim($c['text']))) {
                            ExamChoice::create([
                                'question_id' => $question->id,
                                'label'       => chr(65 + $ci),
                                'text'        => $c['text'],
                                'is_correct'  => $c['is_correct'] ?? false,
                                'order'       => $ci++,
                            ]);
                        }
                    }
                } elseif ($qData['type'] === 'IS' && !empty($qData['short_answers'])) {
                    $question->update(['short_answers' => json_encode($qData['short_answers'])]);
                }

                $question->load('choices');
                $createdQuestions[] = $question;
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Soal berhasil disimpan!',
                'questions'       => $createdQuestions,
                'total_questions' => count($createdQuestions),
                'total_score'     => array_sum(array_column($request->questions, 'score')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing questions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan soal: ' . $e->getMessage()], 500);
        }
    }

    public function storeSingleQuestion(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat diubah soalnya.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'question'             => 'required|string|max:5000',
            'type'                 => 'required|in:PG',
            'score'                => 'required|integer|min:1|max:100',
            'explanation'          => 'nullable|string|max:1000',
            'choices'              => 'required|array|min:2|max:6',
            'choices.*.text'       => 'required|string|max:1000',
            'choices.*.is_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $order    = ($quiz->questions()->max('order') ?? -1) + 1;
            $question = ExamQuestion::create([
                'exam_id'     => $quiz->id,
                'question'    => $request->question,
                'type'        => 'PG',
                'score'       => $request->score,
                'explanation' => $request->explanation ?? null,
                'order'       => $order,
            ]);

            $ci = 0;
            foreach ($request->choices as $c) {
                if (!empty(trim($c['text']))) {
                    ExamChoice::create([
                        'question_id' => $question->id,
                        'label'       => chr(65 + $ci),
                        'text'        => $c['text'],
                        'is_correct'  => $c['is_correct'],
                        'order'       => $ci++,
                    ]);
                }
            }

            DB::commit();
            $question->load('choices');

            return response()->json(['success' => true, 'message' => 'Soal berhasil disimpan', 'question' => $question]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving single question: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan soal: ' . $e->getMessage()], 500);
        }
    }

    public function updateQuestion(Request $request, Exam $quiz, ExamQuestion $question)
    {
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat diubah soalnya.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'type'                 => 'required|in:PG,IS',
            'question'             => 'required|string|max:1000',
            'score'                => 'required|integer|min:1|max:100',
            'explanation'          => 'nullable|string|max:500',
            'choices'              => 'required_if:type,PG|array|min:2',
            'choices.*.text'       => 'required|string|max:500',
            'choices.*.is_correct' => 'required_if:type,PG|boolean',
            'short_answers'        => 'required_if:type,IS|array|min:1',
            'short_answers.*'      => 'string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $question->update([
            'type'        => $request->type,
            'question'    => $request->question,
            'score'       => $request->score,
            'explanation' => $request->explanation,
        ]);

        if ($request->type === 'PG') {
            $question->update(['short_answers' => null]);
            $question->choices()->delete();
            foreach ($request->choices as $i => $c) {
                ExamChoice::create([
                    'question_id' => $question->id,
                    'label'       => chr(65 + $i),
                    'text'        => $c['text'],
                    'is_correct'  => $c['is_correct'] ?? false,
                    'order'       => $i,
                ]);
            }
        } else {
            $question->choices()->delete();
            $question->update(['short_answers' => json_encode($request->short_answers)]);
        }

        DB::commit();

        return response()->json([
            'success'  => true,
            'message'  => 'Soal berhasil diperbarui',
            'question' => $question->load('choices'),
        ]);
    }

    public function deleteQuestion(Exam $quiz, ExamQuestion $question)
    {
        if ($question->exam_id !== $quiz->id || $quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat dihapus soalnya.'], 422);
        }

        try {
            $question->delete();
            foreach ($quiz->questions()->orderBy('order')->get() as $i => $q) {
                $q->update(['order' => $i]);
            }

            return response()->json([
                'success'         => true,
                'message'         => 'Soal berhasil dihapus',
                'total_questions' => $quiz->questions()->count(),
                'total_score'     => $quiz->questions()->sum('score'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus soal: ' . $e->getMessage()], 500);
        }
    }

    public function reorderQuestions(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat diubah urutan soalnya.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'order'   => 'required|array',
            'order.*' => 'exists:exam_questions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            foreach ($request->order as $i => $qId) {
                ExamQuestion::where('id', $qId)->where('exam_id', $quiz->id)->update(['order' => $i]);
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Urutan soal berhasil diubah']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengubah urutan soal: ' . $e->getMessage()], 500);
        }
    }

    public function importQuestions(Request $request, Exam $quiz)
    {
        $validator = Validator::make($request->all(), ['exam_id' => 'required|exists:exams,id']);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $sourceExam = Exam::findOrFail($request->exam_id);

        if ($sourceExam->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke soal ini'], 403);
        }

        try {
            DB::beginTransaction();

            $questions     = $sourceExam->questions()->with('choices')->get();
            $importedCount = 0;
            $currentOrder  = ($quiz->questions()->max('order') ?? -1);

            foreach ($questions as $q) {
                $currentOrder++;
                $newQ = ExamQuestion::create([
                    'exam_id'     => $quiz->id,
                    'question'    => $q->question,
                    'type'        => $q->type,
                    'score'       => $q->score,
                    'explanation' => $q->explanation,
                    'order'       => $currentOrder,
                ]);

                foreach ($q->choices as $c) {
                    ExamChoice::create([
                        'question_id' => $newQ->id,
                        'label'       => $c->label,
                        'text'        => $c->text,
                        'is_correct'  => $c->is_correct,
                        'order'       => $c->order,
                    ]);
                }

                if ($q->type === 'IS' && $q->short_answers) {
                    $newQ->update(['short_answers' => $q->short_answers]);
                }

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} soal",
                'count'   => $importedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengimpor soal: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // QUIZ LIFECYCLE
    // ==========================================

    public function previewQuiz(Exam $quiz)
    {
        try {
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                abort(403, 'Anda tidak memiliki akses ke quiz ini.');
            }

            $quiz->load(['class', 'subject', 'questions.choices', 'activeSession']);

            $questions      = $quiz->questions()->with('choices')->orderBy('order')->get();
            $totalQuestions = $questions->count();

            $modeLabel = match ($quiz->quiz_mode) {
                'homework' => 'Quiz Mandiri',
                'live'     => 'Live Quiz',
                'guided'   => 'Quiz Terpandu',
                default    => ucfirst($quiz->quiz_mode ?? 'Quiz'),
            };

            $stats       = ['joined' => 0, 'submitted' => 0];
            $sessionCode = null;
            if ($quiz->activeSession) {
                $stats['joined']    = $quiz->activeSession->participants()->count();
                $stats['submitted'] = $quiz->activeSession->participants()->where('status', 'submitted')->count();
                $sessionCode        = $quiz->activeSession->session_code;
            }

            $assignedClassIds = DB::table('exam_student')
                ->join('student_class_assignments', 'exam_student.student_id', '=', 'student_class_assignments.student_id')
                ->where('exam_student.exam_id', $quiz->id)
                ->pluck('student_class_assignments.class_id')
                ->unique()
                ->values()
                ->toArray();

            if (empty($assignedClassIds) && $quiz->class_id) {
                $assignedClassIds = [$quiz->class_id];
            }

            return view('guru.quiz.preview', compact(
                'quiz',
                'questions',
                'totalQuestions',
                'modeLabel',
                'stats',
                'sessionCode',
                'assignedClassIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in previewQuiz: ' . $e->getMessage());
            return redirect()->route('guru.quiz.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function finalizeQuiz(Request $request, Exam $quiz)
    {
        try {
            if ($quiz->teacher_id !== Auth::user()->teacher->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), ['action' => 'required|in:publish,save_draft']);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            if ($request->action === 'publish') {
                if ($quiz->questions()->count() < 1) {
                    return response()->json(['success' => false, 'message' => 'Minimal harus ada 1 soal sebelum mempublish'], 422);
                }
                $quiz->update(['status' => 'active']);
                $message = 'Quiz berhasil dipublikasikan!';
            } else {
                $quiz->update(['status' => 'draft']);
                $message = 'Quiz disimpan sebagai draft.';
            }

            return response()->json([
                'success'     => true,
                'message'     => $message,
                'exam_status' => $quiz->status,
                'redirect'    => route('guru.quiz.preview', ['quiz' => $quiz->id])
            ]);
        } catch (\Exception $e) {
            Log::error('Error finalizing quiz: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function publishQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat dipublish ulang.'], 422);
        }

        try {
            $quiz->update(['status' => 'active']);
            return response()->json(['success' => true, 'message' => 'Quiz berhasil dipublish!', 'exam_status' => $quiz->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mempublish quiz: ' . $e->getMessage()], 500);
        }
    }

    public function unpublishQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($quiz->status === 'finished') {
            return response()->json(['success' => false, 'message' => 'Quiz yang sudah selesai tidak dapat diubah statusnya.'], 422);
        }

        try {
            $quiz->update(['status' => 'draft']);
            return response()->json(['success' => true, 'message' => 'Quiz berhasil diunpublish!', 'exam_status' => $quiz->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengunpublish quiz: ' . $e->getMessage()], 500);
        }
    }

    public function duplicateQuiz(Request $request, Exam $quiz)
    {
        if ($quiz->teacher_id !== Auth::user()->teacher->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $newExam         = $quiz->replicate();
            $newExam->title  = $quiz->title . ' (Salinan)';
            $newExam->status = 'draft';
            $newExam->save();

            foreach ($quiz->questions()->with('choices')->get() as $q) {
                $newQ          = $q->replicate();
                $newQ->exam_id = $newExam->id;
                $newQ->save();

                foreach ($q->choices as $c) {
                    $newC              = $c->replicate();
                    $newC->question_id = $newQ->id;
                    $newC->save();
                }
            }

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Quiz berhasil diduplikasi!',
                'new_exam_id' => $newExam->id,
                'redirect'    => route('guru.quiz.questions', $newExam->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menduplikasi quiz: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // FILTER VIEWS
    // ==========================================

    public function draftQuizzes()
    {
        $teacher = Auth::user()->teacher;
        $quizzes = $this->quizQuery()->where('status', 'draft')->with(['subject', 'class'])->orderBy('created_at', 'desc')->paginate(10);
        return view('guru.quiz.draft', compact('quizzes'));
    }

    public function activeQuizzes()
    {
        $teacher = Auth::user()->teacher;
        $quizzes = $this->quizQuery()->where('status', 'active')->with(['subject', 'class'])->orderBy('created_at', 'desc')->paginate(10);
        return view('guru.quiz.active', compact('quizzes'));
    }

    public function completedQuizzes()
    {
        $teacher = Auth::user()->teacher;
        $quizzes = $this->quizQuery()->where('status', 'finished')->with(['subject', 'class'])->orderBy('created_at', 'desc')->paginate(10);
        return view('guru.quiz.completed', compact('quizzes'));
    }

    // ==========================================
    // BULK OPERATIONS
    // ==========================================

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:exams,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $deleted = $this->quizQuery()->whereIn('id', $request->ids)->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => "{$deleted} quiz berhasil dihapus", 'deleted_count' => $deleted]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus quiz: ' . $e->getMessage()], 500);
        }
    }

    public function bulkPublish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:exams,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $updated = $this->quizQuery()->where('status', 'draft')->whereIn('id', $request->ids)->update(['status' => 'active']);
            DB::commit();

            return response()->json(['success' => true, 'message' => "{$updated} quiz berhasil dipublish", 'updated_count' => $updated]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mempublish quiz: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // PRIVATE HELPERS
    // ==========================================

    /**
     * Assign quiz ke semua siswa di satu kelas berdasarkan tahun ajaran aktif.
     */
    private function assignQuizToStudentsByClass(Exam $quiz, int $classId): void
    {
        try {
            $activeYear = AcademicYear::active()->first();

            if (!$activeYear) {
                Log::warning('No active academic year when assigning quiz', ['quiz_id' => $quiz->id]);
                return;
            }

            $studentIds = StudentClassAssignment::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->pluck('student_id')
                ->toArray();

            if (empty($studentIds)) {
                Log::warning('No students in class', ['class_id' => $classId, 'quiz_id' => $quiz->id]);
                return;
            }

            $userIds = Student::whereIn('id', $studentIds)->pluck('user_id')->toArray();

            if (empty($userIds)) {
                Log::warning('No user IDs found for students', ['class_id' => $classId]);
                return;
            }

            $assignments = [];
            foreach ($userIds as $userId) {
                $exists = DB::table('exam_student')
                    ->where('exam_id', $quiz->id)
                    ->where('student_id', $userId)
                    ->exists();
                if (!$exists) {
                    $assignments[] = [
                        'exam_id'    => $quiz->id,
                        'student_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($assignments)) {
                DB::table('exam_student')->insert($assignments);
                Log::info('Quiz assigned to students', [
                    'quiz_id'  => $quiz->id,
                    'class_id' => $classId,
                    'count'    => count($assignments),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error assigning quiz to students: ' . $e->getMessage(), [
                'quiz_id'  => $quiz->id,
                'class_id' => $classId,
            ]);
        }
    }

    /**
     * Generate kode session unik 6 karakter (huruf kapital).
     */
    private function generateUniqueSessionCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (QuizSession::where('session_code', $code)->exists());

        return $code;
    }

    /**
     * Get classes by subject untuk AJAX (digunakan di create/edit quiz).
     */
    public function getClassesBySubject($subjectId)
    {
        try {
            $teacher    = Auth::user()->teacher;
            $activeYear = AcademicYear::active()->first();
            $yearId     = $activeYear?->id;

            if (!$yearId) {
                return response()->json(['success' => false, 'classes' => []]);
            }

            $classes = $teacher->classesTaughtInAcademicYear($yearId)
                ->wherePivot('subject_id', $subjectId)
                ->get(['classes.id', 'classes.name_class as name']);

            return response()->json(['success' => true, 'classes' => $classes]);
        } catch (\Exception $e) {
            Log::error('Error getClassesBySubject: ' . $e->getMessage());
            return response()->json(['success' => false, 'classes' => []]);
        }
    }
}
