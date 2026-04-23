<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ExamChoice;
use App\Models\ExamQuestion;
use App\Models\TeacherClass;
use App\Models\TeacherClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    /**
     * Dapatkan ID teacher dari user yang login
     */
    private function teacherId()
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru');
        return $teacher->id;
    }

    /**
     * GET /guru/exams
     * Tampilkan daftar semua ujian yang dibuat oleh guru
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->back()
                ->with('error', 'Anda harus login sebagai guru untuk mengakses halaman ini.');
        }

        // Query exams berdasarkan teacher - HANYA EXAM (tidak termasuk QUIZ)
        $query = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Exclude quiz
            ->with(['subject', 'class'])
            ->withCount('questions')
            ->latest();

        // Filter pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($q) use ($search) {
                        $q->where('name_subject', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($q) use ($search) {
                        $q->where('name_class', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter jenis (hanya jenis exam, bukan quiz)
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $exams = $query->paginate(10);

        // Statistik (hitung semua kecuali QUIZ)
        $total = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->count();
        $active = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->where('status', 'active')
            ->count();
        $draft = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->where('status', 'draft')
            ->count();

        // Get mapels & classes untuk filter dropdown — sama persis dengan QuizController
        $activeYear = AcademicYear::active()->first();
        $yearId     = $activeYear?->id;
        $mapels     = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes    = $teacher->classesTaughtInAcademicYear($yearId)->get();

        return view('Guru.Exam.index', compact(
            'exams',
            'total',
            'active',
            'draft',
            'mapels',
            'classes'
        ));
    }

    /**
     * GET /guru/exams/create
     * Tampilkan form untuk membuat ujian baru
     */
    public function create()
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) abort(403);

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)->get();

        return view('Guru.Exam.create', compact('mapels', 'classes'));
    }

    /**
     * POST /guru/exams
     * Simpan ujian baru ke database
     */
    public function store(Request $request)
    {
        // VALIDASI INPUT - TOLAK JIKA TYPE QUIZ
        if ($request->type === 'QUIZ') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tipe QUIZ tidak diperbolehkan. Untuk membuat quiz, gunakan halaman Buat Quiz.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,LAINNYA',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'duration' => 'required|integer|min:1|max:300',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_date',
            'limit_attempts' => 'nullable|integer|min:1|max:10',
            'violation_limit' => 'nullable|integer|min:1|max:50',
            'min_pass_grade' => 'nullable|numeric|min:0|max:100',
            'custom_type' => 'nullable|string|max:100',
        ]);

        if ($request->type === 'LAINNYA') {
            $request->validate([
                'custom_type' => 'required|string|max:100',
            ]);
        }

        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru');

        DB::beginTransaction();
        try {
            // LOG DATA YANG DITERIMA
            \Log::info('Exam Store Request Data:', $request->all());

            // Tentukan tipe ujian
            $type = $request->type;
            $customType = null;

            if ($type === 'LAINNYA' && $request->has('custom_type')) {
                $customType = $request->custom_type;
            }

            // Tentukan show_result_after
            $showResultAfter = $request->input('show_result_after', 'never');

            // Tentukan nilai security berdasarkan dropdown
            $securityLevel = $request->input('security_level', 'none');

            // Setel nilai checkbox berdasarkan security level
            $fullscreenMode = false;
            $blockNewTab = false;
            $preventCopyPaste = false;

            // PERBAIKAN: Jika disable_violations dicentang, matikan semua pengaturan keamanan
            $disableViolations = $request->has('disable_violations');

            if ($disableViolations) {
                // Jika disable_violations dicentang, matikan semua
                $fullscreenMode = false;
                $blockNewTab = false;
                $preventCopyPaste = false;
            } else {
                // Jika tidak, gunakan logic security level biasa
                if ($securityLevel === 'basic') {
                    $fullscreenMode = true;
                    $blockNewTab = true;
                    $preventCopyPaste = false;
                } elseif ($securityLevel === 'strict') {
                    $fullscreenMode = true;
                    $blockNewTab = true;
                    $preventCopyPaste = true;
                } elseif ($securityLevel === 'custom') {
                    // Gunakan nilai dari checkbox jika custom
                    $fullscreenMode = $request->has('fullscreen_mode');
                    $blockNewTab = $request->has('block_new_tab');
                    $preventCopyPaste = $request->has('prevent_copy_paste');
                }
            }

            // Konversi nilai boolean ke integer untuk database
            $fullscreenMode = $fullscreenMode ? 1 : 0;
            $blockNewTab = $blockNewTab ? 1 : 0;
            $preventCopyPaste = $preventCopyPaste ? 1 : 0;
            $disableViolations = $disableViolations ? 1 : 0;

            // BUAT EXAM
            $examData = [
                'teacher_id' => $teacher->id,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'title' => $request->title,
                'type' => $type,
                'custom_type' => $customType,
                'duration' => $request->duration,
                'start_at' => $request->start_at,
                'end_at'   => $request->end_at,

                // PENGATURAN DASAR
                'shuffle_question' => $request->has('shuffle_question') ? 1 : 0,
                'shuffle_answer' => $request->has('shuffle_answer') ? 1 : 0,
                'show_score' => $request->has('show_score') ? 1 : 0,

                // PENGATURAN KEAMANAN
                'fullscreen_mode' => $fullscreenMode,
                'block_new_tab' => $blockNewTab,
                'prevent_copy_paste' => $preventCopyPaste,
                'disable_violations' => $disableViolations,
                'violation_limit' => $request->input('violation_limit', 3),

                // PROCTORING
                'enable_proctoring' => $request->has('enable_proctoring') ? 1 : 0,
                'require_camera' => $request->has('require_camera') ? 1 : 0,
                'require_mic' => $request->has('require_mic') ? 1 : 0,

                // HASIL
                'show_correct_answer' => $request->has('show_correct_answer') ? 1 : 0,
                'show_result_after' => $showResultAfter,
                'limit_attempts' => $request->input('limit_attempts', 1),
                'min_pass_grade' => $request->input('min_pass_grade', 0),

                // STATUS AWAL
                'status' => 'draft',
            ];

            // Simpan security_level ke database jika ada di migration
            if (Schema::hasColumn('exams', 'security_level')) {
                $examData['security_level'] = $securityLevel;
            }

            \Log::info('Exam Data to Save:', $examData);

            $exam = Exam::create($examData);

            DB::commit();

            \Log::info('Exam created successfully with ID: ' . $exam->id);

            // Redirect ke halaman soal untuk exam
            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dibuat!',
                'exam_id' => $exam->id,
                'redirect' => route('guru.exams.soal', $exam->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating exam: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /guru/exams/{id}/soal
     * Tampilkan halaman untuk mengelola soal ujian
     */
    public function soal($id)
    {
        $teacher = auth()->user()->teacher;
        abort_if(!$teacher, 403, 'Anda harus login sebagai guru.');

        $exam = Exam::where('teacher_id', $teacher->id)
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['subject', 'class', 'questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->findOrFail($id);

        $isFinished = $exam->status === 'finished';

        return view('Guru.Exam.soal', compact('exam', 'isFinished'));
    }

    /**
     * GET /guru/exams/{id}
     * Tampilkan detail ujian
     */
    public function show($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['subject', 'class', 'questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->findOrFail($id);

        $totalQuestions = $exam->questions->count();
        $totalScore = $exam->questions->sum('score');

        return view('Guru.Exam.show', compact('exam', 'totalQuestions', 'totalScore'));
    }

    /**
     * GET /guru/exams/{id}/edit
     * Tampilkan form edit ujian
     */
    public function edit($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())->findOrFail($id);

        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return redirect()->route('guru.exams.show', $id)
                ->with('error', 'Ujian yang sudah selesai tidak dapat diedit.');
        }

        $teacher = auth()->user()->teacher;

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $mapels = $teacher->subjectsTaughtInAcademicYear($yearId)->get();
        $classes = $teacher->classesTaughtInAcademicYear($yearId)
            ->wherePivot('subject_id', $exam->subject_id)
            ->get();

        return view('Guru.Exam.edit', compact('exam', 'mapels', 'classes'));
    }

    /**
     * PUT /guru/exams/{id}
     * Update ujian
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        // Hanya blokir jika sudah finished atau waktu ujian sudah lewat
        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return back()->with('error', 'Ujian yang sudah selesai tidak dapat diedit.');
        }

        // TOLAK JIKA TYPE QUIZ
        if ($request->type === 'QUIZ') {
            return back()->with('error', 'Tipe QUIZ tidak diperbolehkan. Buat quiz baru.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:UH,UTS,UAS,LAINNYA',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'duration' => 'required|integer|min:1|max:300',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'violation_limit' => 'nullable|integer|min:0|max:50',
            'limit_attempts' => 'nullable|integer|min:1|max:10',
            'min_pass_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->type === 'LAINNYA') {
            $request->validate([
                'custom_type' => 'required|string|max:100',
            ]);
        }

        try {
            DB::beginTransaction();

            $type = $request->type;
            $customType = null;

            if ($type === 'LAINNYA' && $request->has('custom_type')) {
                $customType = $request->custom_type;
            }

            // PERBAIKAN: Atur keamanan jika disable_violations dicentang
            $disableViolations = $request->has('disable_violations');
            $fullscreenMode = false;
            $blockNewTab = false;
            $preventCopyPaste = false;

            if ($disableViolations) {
                // Matikan semua jika disable_violations
                $fullscreenMode = false;
                $blockNewTab = false;
                $preventCopyPaste = false;
            } else {
                // Gunakan nilai dari form
                $fullscreenMode = $request->has('fullscreen_mode');
                $blockNewTab = $request->has('block_new_tab');
                $preventCopyPaste = $request->has('prevent_copy_paste');
            }

            // Update basic exam info
            $updateData = [
                'title' => $request->title,
                'type' => $type,
                'custom_type' => $customType,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'duration' => $request->duration,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,

                // PENGATURAN DASAR
                'shuffle_question' => $request->boolean('shuffle_question'),
                'shuffle_answer' => $request->boolean('shuffle_answer'),
                'show_score' => $request->boolean('show_score'),
                'show_correct_answer' => $request->boolean('show_correct_answer'),

                // KEAMANAN
                'fullscreen_mode' => $fullscreenMode,
                'block_new_tab' => $blockNewTab,
                'prevent_copy_paste' => $preventCopyPaste,
                'disable_violations' => $disableViolations,
                'violation_limit' => $request->input('violation_limit', 3),

                // PROCTORING
                'enable_proctoring' => $request->boolean('enable_proctoring'),
                'require_camera' => $request->boolean('require_camera'),
                'require_mic' => $request->boolean('require_mic'),

                // HASIL
                'show_result_after' => $request->input('show_result_after', 'never'),
                'limit_attempts' => $request->input('limit_attempts', 1),
                'min_pass_grade' => $request->input('min_pass_grade', 0),
            ];

            $exam->update($updateData);

            DB::commit();

            return redirect()
                ->route('guru.exams.show', $exam->id)
                ->with('success', 'Ujian berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * DELETE /guru/exams/{id}
     * Hapus ujian
     */
    public function destroy($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        try {
            // Hanya bisa menghapus jika masih draft
            if ($exam->status !== 'draft') {
                return redirect()->back()
                    ->with('error', 'Hanya ujian dalam status draft yang bisa dihapus');
            }

            $exam->delete();

            return redirect()->route('guru.exams.index')
                ->with('success', 'Ujian berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /guru/exams/{exam}/results
     * Tampilkan hasil ujian
     */
    public function results($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['attempts' => function ($query) {
                $query->with('student')->orderBy('created_at', 'desc');
            }])
            ->findOrFail($id);

        $stats = [
            'total_attempts' => $exam->attempts->count(),
            'average_score' => $exam->attempts->avg('final_score') ?? 0,
            'highest_score' => $exam->attempts->max('final_score') ?? 0,
            'lowest_score' => $exam->attempts->min('final_score') ?? 0,
            'pass_count' => $exam->attempts->where('final_score', '>=', $exam->min_pass_grade)->count(),
            'fail_count' => $exam->attempts->where('final_score', '<', $exam->min_pass_grade)->count(),
        ];

        return view('Guru.Exam.results', compact('exam', 'stats'));
    }

    /**
     * GET /guru/exams/get-classes-by-subject/{subjectId}
     * AJAX endpoint untuk mendapatkan kelas berdasarkan subject
     */
    public function getClassesBySubject($subjectId)
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return response()->json(['classes' => []]);
        }

        $activeYear = AcademicYear::active()->first();
        $yearId = $activeYear?->id;

        $classes = $teacher->classesTaughtInAcademicYear($yearId)
            ->wherePivot('subject_id', $subjectId)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name_class]);

        return response()->json(['success' => true, 'classes' => $classes]);
    }

    /**
     * POST /guru/exams/{id}/finalize
     * Finalize exam (ubah dari draft ke active)
     */
    public function finalize($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with('questions')
            ->findOrFail($id);

        // Validasi minimal 1 soal
        if ($exam->questions->count() == 0) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian harus memiliki minimal 1 soal'
                ], 422);
            }
            return redirect()->route('guru.exams.show', $exam->id)
                ->with('error', 'Ujian harus memiliki minimal 1 soal sebelum dipublikasikan.');
        }

        try {
            DB::beginTransaction();

            // Update status menjadi active
            $exam->update([
                'status' => 'active',
                'updated_at' => now()
            ]);

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ujian berhasil dipublikasikan!',
                    'redirect' => route('guru.exams.show', $exam->id)
                ]);
            }

            return redirect()->route('guru.exams.show', $exam->id)
                ->with('success', 'Ujian berhasil dipublikasikan! Ujian sekarang aktif dan tersedia untuk siswa.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('guru.exams.show', $exam->id)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    /**
     * POST /guru/exams/{id}/toggle-status
     * Toggle status exam
     */
    public function toggleStatus(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        $validStatuses = ['draft', 'active', 'inactive'];
        $newStatus = $request->input('status');

        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return response()->json([
                'success' => false,
                'message' => 'Status ujian yang sudah selesai tidak dapat diubah.'
            ], 422);
        }

        if (!in_array($newStatus, $validStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid'
            ], 422);
        }

        try {
            $exam->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /guru/exams/{id}/update-status
     * Update status via form
     */
    public function updateStatus(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->findOrFail($id);

        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return redirect()->back()
                ->with('error', 'Status ujian yang sudah selesai tidak dapat diubah.');
        }

        if ($request->status === 'finished') {
            return redirect()->back()
                ->with('error', 'Tidak dapat mengubah status ujian menjadi selesai secara manual.');
        }

        try {
            $exam->update(['status' => $request->status]);

            return redirect()->route('guru.exams.show', $exam->id)
                ->with('success', 'Status ujian berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * POST /guru/exams/{id}/duplicate
     * Duplicate exam
     */
    public function duplicate($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with('questions.choices')
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Duplicate exam
            $newExam = $exam->replicate();
            $newExam->title = $exam->title . ' (Salinan)';
            $newExam->status = 'draft';
            $newExam->published_at = null;
            $newExam->save();

            // Duplicate questions
            foreach ($exam->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->exam_id = $newExam->id;
                $newQuestion->save();

                // Duplicate choices
                if ($question->choices->isNotEmpty()) {
                    foreach ($question->choices as $choice) {
                        $newChoice = $choice->replicate();
                        $newChoice->question_id = $newQuestion->id;
                        $newChoice->save();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('guru.exams.soal', $newExam->id)
                ->with('success', 'Ujian berhasil diduplikasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /guru/exams/{id}/preview
     * Preview exam
     */
    public function preview($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['questions' => function ($query) {
                $query->with('choices')->orderBy('order');
            }])
            ->findOrFail($id);

        return view('Guru.Exam.preview', compact('exam'));
    }

    /**
     * POST /guru/exams/{id}/publish
     * Publish exam
     */
    public function publish($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        try {
            $exam->update([
                'status' => 'active',
                'published_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dipublish',
                'exam_status' => $exam->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mempublish ujian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /guru/exams/{id}/unpublish
     * Unpublish exam
     */
    public function unpublish($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        try {
            $exam->update([
                'status' => 'draft',
                'published_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil diunpublish',
                'exam_status' => $exam->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunpublish ujian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /guru/exams/{id}/results/export
     * Export hasil ujian ke Excel (.xlsx) atau CSV fallback.
     * Query param: ?format=excel (default) | ?format=pdf
     */
    public function exportResults($id)
    {
        $exam = Exam::with(['subject', 'class', 'questions'])
            ->where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->findOrFail($id);

        // Ambil semua attempt yang sudah submit, JOIN ke students & users
        $attempts = \App\Models\ExamAttempt::select(
                'exam_attempts.*',
                'students.nis',
                'users.name as student_name'
            )
            ->leftJoin('students', 'exam_attempts.student_id', '=', 'students.id')
            ->leftJoin('users', 'students.user_id', '=', 'users.id')
            ->where('exam_attempts.exam_id', $id)
            ->whereIn('exam_attempts.status', ['submitted', 'timeout'])
            ->orderByDesc('exam_attempts.final_score')
            ->get();

        $format = request('format', 'excel');

        if ($format === 'excel') {
            return $this->doExportXlsx($exam, $attempts);
        }

        // PDF — fallback jika ada package PDF
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data = [
                'exam'       => $exam,
                'attempts'   => $attempts,
                'avgScore'   => $attempts->avg('final_score') ?? 0,
                'exportDate' => now()->format('d F Y H:i:s'),
            ];
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('guru.exams.results.export-pdf', $data);
            return $pdf->download('hasil-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '-' . now()->format('Y-m-d') . '.pdf');
        }

        // Default ke Excel jika PDF tidak tersedia
        return $this->doExportXlsx($exam, $attempts);
    }

    /**
     * Generate file .xlsx menggunakan PhpSpreadsheet.
     * Fallback ke CSV jika PhpSpreadsheet tidak terinstall.
     *
     * Install: composer require phpoffice/phpspreadsheet
     */
    private function doExportXlsx($exam, $attempts)
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->doExportCsv($exam, $attempts);
        }

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
        $info = [
            ['Mata Pelajaran', ($exam->subject->name_subject ?? $exam->subject->name ?? '-'), 'Kelas', ($exam->class->name_class ?? $exam->class->name ?? '-')],
            ['Jenis Ujian',    $exam->getDisplayType(),                                        'Tanggal Export', now()->format('d/m/Y H:i')],
            ['Total Peserta',  $attempts->count(),                                             'Rata-rata Nilai', number_format($attempts->avg('final_score') ?? 0, 2)],
        ];
        $r = 2;
        foreach ($info as $row) {
            $sheet->setCellValue('A' . $r, $row[0]);
            $sheet->setCellValue('B' . $r, ': ' . $row[1]);
            $sheet->setCellValue('D' . $r, $row[2]);
            $sheet->setCellValue('E' . $r, ': ' . $row[3]);
            $r++;
        }

        // ── Header kolom (baris 6) ──
        $headers = ['No', 'NIS', 'Nama Siswa', 'Nilai Akhir', 'Grade', 'Status', 'Waktu Submit'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $i => $h) {
            $cell = $cols[$i] . '6';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4F46E5');
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
                ? \Carbon\Carbon::parse($attempt->ended_at)->format('d/m/Y H:i')
                : '-');

            // Warna grade
            $gradeColor = match ($grade) {
                'A'     => 'FFD1FAE5',
                'B'     => 'FFDBEAFE',
                'C'     => 'FFFEF9C3',
                'D'     => 'FFFFEDD5',
                default => 'FFFEE2E2',
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

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $no++;
            $row++;
        }

        // ── Border & auto-width ──
        $sheet->getStyle('A6:G' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

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
     * Fallback CSV — bisa dibuka langsung di Excel.
     */
    private function doExportCsv($exam, $attempts)
    {
        $filename = 'hasil-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($exam, $attempts) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF"); // UTF-8 BOM agar Excel baca dengan benar

            fputcsv($f, ['Judul Ujian', $exam->title]);
            fputcsv($f, ['Mata Pelajaran', $exam->subject->name_subject ?? $exam->subject->name ?? '-']);
            fputcsv($f, ['Kelas', $exam->class->name_class ?? $exam->class->name ?? '-']);
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
                    $attempt->ended_at ? \Carbon\Carbon::parse($attempt->ended_at)->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($f);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /guru/exams/{id}/statistics
     * Get exam statistics
     */
    public function statistics($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['attempts' => function ($query) {
                $query->where('status', 'submitted');
            }])
            ->findOrFail($id);

        $totalAttempts = $exam->attempts->count();
        $averageScore = $exam->attempts->avg('final_score');
        $highestScore = $exam->attempts->max('final_score');
        $lowestScore = $exam->attempts->min('final_score');
        $passCount = $exam->attempts->where('final_score', '>=', $exam->min_pass_grade)->count();
        $failCount = $totalAttempts - $passCount;

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_attempts' => $totalAttempts,
                'average_score' => round($averageScore, 2),
                'highest_score' => round($highestScore, 2),
                'lowest_score' => round($lowestScore, 2),
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'pass_percentage' => $totalAttempts > 0 ? round(($passCount / $totalAttempts) * 100, 2) : 0,
                'min_pass_grade' => $exam->min_pass_grade
            ]
        ]);
    }

    /**
     * DELETE /guru/exams/{id}/clear-attempts
     * Clear all attempts
     */
    public function clearAttempts($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete all attempts and related answers
            foreach ($exam->attempts as $attempt) {
                $attempt->answers()->delete();
                $attempt->delete();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Semua percobaan ujian berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * GET /guru/exams/{id}/data
     * Get exam data for AJAX
     */
    public function getExamData($id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ') // Pastikan bukan quiz
            ->with(['subject', 'class', 'questions.choices'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'exam' => $exam,
            'security_settings' => [
                'fullscreen_mode' => $exam->fullscreen_mode,
                'block_new_tab' => $exam->block_new_tab,
                'prevent_copy_paste' => $exam->prevent_copy_paste,
                'disable_violations' => $exam->disable_violations,
                'violation_limit' => $exam->violation_limit,
            ],
        ]);
    }

    /**
     * POST /guru/exams/{id}/soal
     * Simpan soal baru (AJAX)
     */
    /** Tipe soal yang valid */
    private const VALID_QUESTION_TYPES = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];

    public function storeQuestion(Request $request, $id)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($id);

        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak dapat ditambah karena ujian sudah selesai.'
            ], 422);
        }

        $validator = validator($request->all(), [
            'question' => 'required|string|min:3',
            'type'     => 'required|in:' . implode(',', self::VALID_QUESTION_TYPES),
            'score'    => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $type = $request->type;
            $lastOrder = ExamQuestion::where('exam_id', $exam->id)->max('order') ?? 0;

            $questionData = [
                'exam_id'            => $exam->id,
                'type'               => $type,
                'question'           => trim($request->question),
                'score'              => (int) $request->score,
                'explanation'        => trim($request->explanation ?? ''),
                'order'              => $lastOrder + 1,
                'enable_skip'        => $request->boolean('enable_skip', true),
                'enable_mark_review' => $request->boolean('enable_mark_review', true),
                'randomize_choices'  => $request->boolean('randomize_choices', false),
            ];

            switch ($type) {
                case 'PG':
                case 'DD':
                    $this->validateChoicesExam($request);
                    $question = ExamQuestion::create($questionData);
                    $this->saveChoicesExam($question->id, $request->options, [$request->correct_answer]);
                    break;

                case 'PGK':
                    $this->validateChoicesExam($request, true);
                    $question = ExamQuestion::create($questionData);
                    $this->saveChoicesExam($question->id, $request->options, $request->correct_answers ?? []);
                    break;

                case 'BS':
                    if (!in_array($request->short_answer, ['benar', 'salah'])) {
                        throw new \Exception('Jawaban Benar/Salah harus "benar" atau "salah"');
                    }
                    $questionData['short_answers'] = json_encode([$request->short_answer]);
                    $question = ExamQuestion::create($questionData);
                    break;

                case 'IS':
                    if (empty(trim($request->short_answer ?? ''))) {
                        throw new \Exception('Jawaban tidak boleh kosong untuk soal Isian Singkat');
                    }
                    $answers = array_values(array_filter(array_map('trim', explode(',', $request->short_answer))));
                    $questionData['short_answers'] = json_encode([
                        'answers'        => $answers,
                        'case_sensitive' => (bool) ($request->case_sensitive ?? false),
                    ]);
                    $question = ExamQuestion::create($questionData);
                    break;

                case 'ES':
                    $questionData['short_answers'] = json_encode(['rubric' => trim($request->rubric ?? '')]);
                    $question = ExamQuestion::create($questionData);
                    break;

                case 'SK':
                    $scaleMin = (int) ($request->scale_min ?? 1);
                    $scaleMax = (int) ($request->scale_max ?? 5);
                    if ($scaleMax <= $scaleMin) throw new \Exception('Skala maksimum harus lebih besar dari minimum');
                    $questionData['short_answers'] = json_encode([
                        'min'       => $scaleMin,
                        'max'       => $scaleMax,
                        'min_label' => trim($request->scale_min_label ?? ''),
                        'max_label' => trim($request->scale_max_label ?? ''),
                        'correct'   => $request->scale_correct !== null ? (int) $request->scale_correct : null,
                    ]);
                    $question = ExamQuestion::create($questionData);
                    break;

                case 'MJ':
                    $pairs = $request->pairs ?? [];
                    if (count($pairs) < 2) throw new \Exception('Minimal 2 pasangan untuk soal Menjodohkan');
                    foreach ($pairs as $pair) {
                        if (empty($pair['left']) || empty($pair['right'])) {
                            throw new \Exception('Semua pasangan harus terisi');
                        }
                    }
                    $questionData['short_answers'] = json_encode($pairs);
                    $question = ExamQuestion::create($questionData);
                    break;

                default:
                    throw new \Exception('Tipe soal tidak dikenali');
            }

            DB::commit();

            $question->load(['choices' => fn($q) => $q->orderBy('order')]);

            return response()->json([
                'success'  => true,
                'message'  => 'Soal berhasil ditambahkan',
                'question' => $this->formatQuestionExam($question),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /guru/exams/{examId}/questions/{questionId}
     * Ambil data soal untuk diedit (AJAX)
     */
    public function getQuestion($examId, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->findOrFail($examId);

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->with('choices')
            ->findOrFail($questionId);

        return response()->json([
            'success' => true,
            'question' => [
                'id' => $question->id,
                'type' => $question->type,
                'question' => $question->question,
                'score' => $question->score,
                'short_answers' => $question->short_answers,
                'choices' => $question->choices->map(function ($choice) {
                    return [
                        'text' => $choice->text,
                        'is_correct' => $choice->is_correct,
                    ];
                }),
            ],
        ]);
    }

    /**
     * PUT /guru/exams/{examId}/questions/{questionId}
     * Update soal (AJAX)
     */
    public function updateQuestion(Request $request, $examId, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->findOrFail($examId);

        if ($exam->status === 'finished' || ($exam->end_at && \Carbon\Carbon::parse($exam->end_at)->isPast() && $exam->status === 'active')) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak dapat diubah karena ujian sudah selesai.'
            ], 422);
        }

        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($questionId);

        $validator = validator($request->all(), [
            'question' => 'required|string|min:3',
            'type'     => 'required|in:' . implode(',', self::VALID_QUESTION_TYPES),
            'score'    => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $type = $request->type;

            $question->update([
                'type'        => $type,
                'question'    => trim($request->question),
                'score'       => (int) $request->score,
                'explanation' => trim($request->explanation ?? ''),
            ]);

            // Hapus choices & short_answers lama
            $question->choices()->delete();
            $question->short_answers = null;
            $question->save();

            switch ($type) {
                case 'PG':
                case 'DD':
                    $this->validateChoicesExam($request);
                    $this->saveChoicesExam($question->id, $request->options, [$request->correct_answer]);
                    break;

                case 'PGK':
                    $this->validateChoicesExam($request, true);
                    $this->saveChoicesExam($question->id, $request->options, $request->correct_answers ?? []);
                    break;

                case 'BS':
                    if (!in_array($request->short_answer, ['benar', 'salah'])) {
                        throw new \Exception('Jawaban harus "benar" atau "salah"');
                    }
                    $question->short_answers = json_encode([$request->short_answer]);
                    $question->save();
                    break;

                case 'IS':
                    $answers = array_values(array_filter(array_map('trim', explode(',', $request->short_answer ?? ''))));
                    if (empty($answers)) throw new \Exception('Jawaban tidak boleh kosong');
                    $question->short_answers = json_encode([
                        'answers'        => $answers,
                        'case_sensitive' => (bool) ($request->case_sensitive ?? false),
                    ]);
                    $question->save();
                    break;

                case 'ES':
                    $question->short_answers = json_encode(['rubric' => trim($request->rubric ?? '')]);
                    $question->save();
                    break;

                case 'SK':
                    $scaleMin = (int) ($request->scale_min ?? 1);
                    $scaleMax = (int) ($request->scale_max ?? 5);
                    if ($scaleMax <= $scaleMin) throw new \Exception('Skala maksimum harus lebih besar dari minimum');
                    $question->short_answers = json_encode([
                        'min'       => $scaleMin,
                        'max'       => $scaleMax,
                        'min_label' => trim($request->scale_min_label ?? ''),
                        'max_label' => trim($request->scale_max_label ?? ''),
                        'correct'   => $request->scale_correct !== null ? (int) $request->scale_correct : null,
                    ]);
                    $question->save();
                    break;

                case 'MJ':
                    $pairs = $request->pairs ?? [];
                    if (count($pairs) < 2) throw new \Exception('Minimal 2 pasangan');
                    foreach ($pairs as $pair) {
                        if (empty($pair['left']) || empty($pair['right'])) {
                            throw new \Exception('Semua pasangan harus terisi');
                        }
                    }
                    $question->short_answers = json_encode($pairs);
                    $question->save();
                    break;
            }

            DB::commit();

            $question->load(['choices' => fn($q) => $q->orderBy('order')]);

            return response()->json([
                'success'  => true,
                'message'  => 'Soal berhasil diperbarui',
                'question' => $this->formatQuestionExam($question),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /* ===================== PRIVATE HELPERS QUESTION ===================== */

    private function validateChoicesExam(Request $request, bool $multiCorrect = false): void
    {
        $options = $request->options ?? [];
        $filled  = array_filter(array_map('trim', $options));

        if (count($filled) < 2) {
            throw new \Exception('Minimal 2 opsi jawaban harus diisi');
        }

        if ($multiCorrect) {
            $correct = $request->correct_answers ?? [];
            if (empty($correct)) throw new \Exception('Pilih minimal 1 jawaban yang benar');
        } else {
            if ($request->correct_answer === null || $request->correct_answer === '') {
                throw new \Exception('Pilih jawaban yang benar');
            }
        }
    }

    private function saveChoicesExam(int $questionId, array $options, array $correctIndexes): void
    {
        $correctIndexes = array_map('intval', $correctIndexes);

        foreach ($options as $index => $text) {
            $trimmed = trim($text ?? '');
            if ($trimmed === '') continue;

            ExamChoice::create([
                'question_id' => $questionId,
                'label'       => chr(65 + $index),
                'text'        => $trimmed,
                'is_correct'  => in_array($index, $correctIndexes),
                'order'       => $index,
            ]);
        }
    }

    private function formatQuestionExam(ExamQuestion $q): array
    {
        $data = [
            'id'            => $q->id,
            'type'          => $q->type,
            'question'      => $q->question,
            'score'         => $q->score,
            'explanation'   => $q->explanation,
            'short_answers' => null,
            'choices'       => [],
        ];

        if ($q->short_answers) {
            $raw = is_array($q->short_answers) ? $q->short_answers : json_decode($q->short_answers, true);
            $data['short_answers'] = $raw;
        }

        if (in_array($q->type, ['PG', 'PGK', 'DD'])) {
            $data['choices'] = $q->choices->map(fn($c) => [
                'id'         => $c->id,
                'label'      => $c->label,
                'text'       => $c->text,
                'is_correct' => (bool) $c->is_correct,
                'order'      => $c->order,
            ])->values()->toArray();
        }

        return $data;
    }

    /**
     * DELETE /guru/exams/{examId}/questions/{questionId}
     * Hapus soal (AJAX)
     */
    public function deleteQuestion($examId, $questionId)
    {
        $exam = Exam::where('teacher_id', $this->teacherId())
            ->where('type', '!=', 'QUIZ')
            ->findOrFail($examId);

        if ($exam->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak dapat dihapus karena ujian sudah selesai.'
            ], 422);
        }

        $question = ExamQuestion::where('exam_id', $exam->id)
            ->findOrFail($questionId);

        try {
            DB::beginTransaction();

            // Hapus pilihan jika ada
            if ($question->choices->isNotEmpty()) {
                $question->choices()->delete();
            }

            $question->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}   
