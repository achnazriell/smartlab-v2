<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamChoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * ==========================================
     * HELPER METHOD - GET STUDENT CLASS ID
     * ==========================================
     * ✅ PERBAIKAN: Helper untuk mendapatkan class_id siswa aktif
     * dari relasi classAssignments dengan tahun ajaran aktif
     */
    private function getStudentClassId($student)
    {
        if (!$student) {
            return null;
        }

        $assignment = $student->classAssignments()
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->first();

        return $assignment ? $assignment->class_id : null;
    }

    /**
     * ==========================================
     * INDEX - TAMPILKAN DAFTAR UJIAN
     * ==========================================
     * GET /soal
     * Tampilkan semua ujian yang tersedia untuk siswa
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            // Validasi student
            if (!$student) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Data siswa tidak ditemukan. Hubungi administrator.',
                    'hasClass' => false
                ]);
            }

            // ✅ PERBAIKAN: Ambil kelas aktif dari relasi (BUKAN dari $student->class_id)
            $classId = $this->getStudentClassId($student);
            $hasClass = !is_null($classId);

            if (!$classId) {
                return view('Siswa.soal', [
                    'exams' => collect(),
                    'error' => 'Anda belum memiliki kelas. Hubungi administrator untuk ditugaskan ke kelas.',
                    'hasClass' => false
                ]);
            }

            // ✅ Query exam dengan filter yang benar
            $query = Exam::where('class_id', $classId)
                ->where('type', '!=', 'QUIZ')  // Exclude quiz
                ->where('status', 'active')    // Hanya yang active
                ->with(['subject', 'class', 'questions.choices', 'teacher']);

            // Filter waktu - tampilkan yang sedang berlangsung
            if (!$request->has('show_all') || $request->show_all != 'true') {
                $query->where(function ($q) {
                    $q->whereNull('start_at')
                      ->orWhere('start_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_at')
                      ->orWhere('end_at', '>=', now());
                });
            }

            // Filter pencarian
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('subject', function ($q) use ($search) {
                          $q->where('name_subject', 'like', "%{$search}%");
                      });
                });
            }

            // Filter by type
            if ($request->has('type') && $request->type != '') {
                $query->where('type', $request->type);
            }

            // Filter by status
            if ($request->has('exam_status') && $request->exam_status != '') {
                $status = $request->exam_status;
                if ($status === 'aktif') {
                    $query->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('start_at')->orWhere('start_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('end_at')->orWhere('end_at', '>=', now());
                        });
                } elseif ($status === 'belum_dimulai') {
                    $query->where('status', 'active')
                        ->whereNotNull('start_at')
                        ->where('start_at', '>', now());
                } elseif ($status === 'selesai') {
                    $query->where(function ($q) {
                        $q->where('status', 'inactive')
                          ->orWhere(function ($subQ) {
                              $subQ->where('status', 'active')
                                   ->whereNotNull('end_at')
                                   ->where('end_at', '<', now());
                          });
                    });
                }
            }

            $exams = $query->orderBy('created_at', 'desc')->get();

            // ✅ DEBUGGING: Log hasil query untuk troubleshooting
            Log::info('Exam Query Result', [
                'student_id' => $student->id,
                'user_id' => $user->id,
                'class_id' => $classId,
                'exam_count' => $exams->count(),
                'exam_ids' => $exams->pluck('id')->toArray(),
                'filters' => [
                    'search' => $request->search ?? null,
                    'type' => $request->type ?? null,
                    'show_all' => $request->show_all ?? null,
                    'exam_status' => $request->exam_status ?? null
                ]
            ]);

            // Enrich data dengan informasi attempt
            foreach ($exams as $exam) {
                $this->enrichExamData($exam, $user->id);
            }

            return view('Siswa.soal', compact('exams', 'hasClass'));

        } catch (\Exception $e) {
            Log::error('Error in MuridExamController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return view('Siswa.soal', [
                'exams' => collect(),
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'hasClass' => false
            ]);
        }
    }

    /**
     * ==========================================
     * SHOW DETAIL - TAMPILKAN DETAIL UJIAN
     * ==========================================
     * GET /soal/{examId}
     * Tampilkan halaman detail ujian sebelum dikerjakan
     */
    public function showDetail($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // ✅ PERBAIKAN: Ambil kelas aktif
            $classId = $this->getStudentClassId($student);

            if (!$classId) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda belum memiliki kelas.');
            }

            // Load exam dengan semua relasi yang dibutuhkan
            $exam = Exam::with([
                'questions' => function($q) {
                    $q->orderBy('order')->with(['choices' => function($q) {
                        $q->orderBy('order');
                    }]);
                },
                'subject',
                'class',
                'teacher.user'
            ])->where('id', $examId)->first();

            if (!$exam) {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }

            // ✅ PERBAIKAN: Validasi akses kelas
            if ($exam->class_id != $classId) {
                Log::warning('Unauthorized exam access attempt', [
                    'student_id' => $student->id,
                    'student_class_id' => $classId,
                    'exam_id' => $exam->id,
                    'exam_class_id' => $exam->class_id
                ]);

                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak tersedia untuk kelas Anda.');
            }

            // Validasi status
            if ($exam->status !== 'active') {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian belum aktif.');
            }

            // Cek semua attempt siswa untuk exam ini
            $attempts = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Cek attempt yang sedang berlangsung
            $activeAttempt = $attempts->where('status', 'in_progress')->first();

            // Cek attempt terakhir
            $latestAttempt = $attempts->first();

            // Hitung jumlah attempt yang sudah selesai
            $completedAttempts = $attempts->whereIn('status', ['submitted', 'timeout'])->count();

            // Cek apakah masih bisa mengerjakan
            $canStart = true;
            $canStartMessage = null;

            // Cek limit attempts
            if ($exam->limit_attempts && $exam->limit_attempts > 0) {
                if ($completedAttempts >= $exam->limit_attempts) {
                    $canStart = false;
                    $canStartMessage = "Anda telah mencapai batas maksimal percobaan ({$exam->limit_attempts}x).";
                }
            }

            // Cek waktu ujian
            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                $canStart = false;
                $canStartMessage = "Ujian akan dimulai pada " . $exam->start_at->format('d M Y, H:i');
            }

            if ($exam->end_at && $now > $exam->end_at) {
                $canStart = false;
                $canStartMessage = "Waktu ujian telah berakhir pada " . $exam->end_at->format('d M Y, H:i');
            }

            // Enrich exam data
            $this->enrichExamData($exam, $user->id);

            return view('Siswa.soal-detail', compact(
                'exam',
                'attempts',
                'activeAttempt',
                'latestAttempt',
                'completedAttempts',
                'canStart',
                'canStartMessage'
            ));

        } catch (\Exception $e) {
            Log::error('Error in MuridExamController@showDetail', [
                'error' => $e->getMessage(),
                'exam_id' => $examId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ==========================================
     * START - MULAI UJIAN
     * ==========================================
     * POST /soal/{examId}/start
     * Buat attempt baru dan redirect ke halaman mengerjakan
     */
    public function start(Request $request, $examId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // ✅ PERBAIKAN: Ambil kelas aktif
            $classId = $this->getStudentClassId($student);

            if (!$classId) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda belum memiliki kelas.');
            }

            $exam = Exam::find($examId);

            if (!$exam) {
                DB::rollBack();
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }

            // ✅ PERBAIKAN: Validasi akses kelas
            if ($exam->class_id != $classId) {
                DB::rollBack();
                Log::warning('Unauthorized exam start attempt', [
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'student_class' => $classId,
                    'exam_class' => $exam->class_id
                ]);

                return redirect()->route('soal.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }

            // Validasi status ujian
            if ($exam->status !== 'active') {
                DB::rollBack();
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian belum aktif.');
            }

            // Validasi waktu
            $now = now();
            if ($exam->start_at && $now < $exam->start_at) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum dimulai. Silakan tunggu hingga waktu mulai.');
            }

            if ($exam->end_at && $now > $exam->end_at) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Waktu ujian telah berakhir.');
            }

            // Cek attempt yang sedang berlangsung
            $activeAttempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->first();

            if ($activeAttempt) {
                DB::rollBack();
                Log::info('Redirecting to existing attempt', [
                    'attempt_id' => $activeAttempt->id,
                    'student_id' => $student->id
                ]);

                return redirect()->route('soal.attempt', $examId)
                    ->with('info', 'Anda sudah memiliki sesi ujian yang sedang berlangsung.');
            }

            // Cek limit attempts
            if ($exam->limit_attempts && $exam->limit_attempts > 0) {
                $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['submitted', 'timeout'])
                    ->count();

                if ($attemptCount >= $exam->limit_attempts) {
                    DB::rollBack();
                    return redirect()->route('soal.detail', $examId)
                        ->with('error', "Anda telah mencapai batas maksimal percobaan ({$exam->limit_attempts}x).");
                }
            }

            // Validasi jumlah soal
            $questionCount = $exam->questions()->count();
            if ($questionCount == 0) {
                DB::rollBack();
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Ujian belum memiliki soal. Hubungi guru pengampu.');
            }

            // Buat attempt baru dengan settings default
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => now(),
                'status' => 'in_progress',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'remaining_time' => $exam->duration * 60, // dalam detik
                'exam_settings' => $exam->getAllSettings(),
                'violation_count' => 0,
                'violation_log' => [],
                'score' => 0,
                'final_score' => 0,
                'is_cheating_detected' => false,
            ]);

            DB::commit();

            Log::info('Exam attempt started successfully', [
                'attempt_id' => $attempt->id,
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'started_at' => $attempt->started_at
            ]);

            return redirect()->route('soal.attempt', $examId)
                ->with('success', 'Ujian dimulai. Selamat mengerjakan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting exam', [
                'error' => $e->getMessage(),
                'exam_id' => $examId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan saat memulai ujian: ' . $e->getMessage());
        }
    }

    /**
     * ==========================================
     * ATTEMPT - TAMPILKAN HALAMAN MENGERJAKAN
     * ==========================================
     * GET /soal/{examId}/attempt
     * Tampilkan interface untuk mengerjakan soal
     */
    public function attempt($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // ✅ PERBAIKAN: Ambil kelas aktif
            $classId = $this->getStudentClassId($student);

            if (!$classId) {
                return redirect()->route('soal.index')
                    ->with('error', 'Anda belum memiliki kelas.');
            }

            // Load exam dengan soal dan pilihan
            $exam = Exam::with(['questions.choices'])
                ->where('id', $examId)
                ->where('class_id', $classId)  // ✅ Gunakan $classId
                ->where('status', 'active')
                ->first();

            if (!$exam) {
                return redirect()->route('soal.index')
                    ->with('error', 'Ujian tidak ditemukan atau tidak tersedia.');
            }

            // Cek attempt aktif
            $attempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return redirect()->route('soal.detail', $examId)
                    ->with('error', 'Sesi ujian tidak ditemukan. Silakan mulai ujian terlebih dahulu.');
            }

            // Cek timeout
            if ($exam->duration && $exam->duration > 0) {
                $elapsed = $attempt->started_at->diffInMinutes(now());
                if ($elapsed >= $exam->duration) {
                    // Auto-submit karena timeout
                    $attempt->timeout();

                    return redirect()->route('soal.result', $attempt->id)
                        ->with('info', 'Waktu ujian telah habis. Jawaban Anda otomatis tersimpan.');
                }
            }

            // Load jawaban yang sudah ada
            $existingAnswers = ExamAnswer::where('attempt_id', $attempt->id)
                ->get()
                ->keyBy('question_id');

            // Hitung sisa waktu secara akurat (dalam detik)
            $totalSeconds = ($exam->duration ?? 0) * 60;
            $elapsedSeconds = $attempt->started_at->diffInSeconds(now());
            $timeRemaining = max(0, $totalSeconds - $elapsedSeconds);

            // Shuffle questions jika diperlukan
            $questions = $exam->questions;
            if ($exam->shuffle_question) {
                $questions = $questions->shuffle();
            }

            // Shuffle choices untuk setiap soal jika diperlukan
            if ($exam->shuffle_answer) {
                foreach ($questions as $question) {
                    if ($question->choices && $question->choices->count() > 0) {
                        $question->setRelation('choices', $question->choices->shuffle());
                    }
                }
            }

            // Format questions untuk JavaScript: tambahkan question_text alias dan options object
            $questionsFormatted = $questions->map(function ($q) use ($existingAnswers) {
                $data = $q->toArray();

                // Alias field: blade menggunakan question_text
                $data['question_text'] = $q->question;

                // Format choices menjadi options object { "A": "teks", "B": "teks", ... }
                // Ini yang dipakai template PG, DD, PGK di Alpine.js
                if (in_array($q->type, ['PG', 'PGK', 'DD']) && $q->choices->count() > 0) {
                    $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
                    $options = [];
                    foreach ($q->choices as $idx => $choice) {
                        $label = $labels[$idx] ?? chr(65 + $idx);
                        $options[$label] = $choice->text;
                    }
                    $data['options'] = $options;
                }

                // BS (Benar/Salah): hardcode options
                if ($q->type === 'BS') {
                    $data['options'] = ['Benar' => 'Benar', 'Salah' => 'Salah'];
                }

                // IS (Isian Singkat) dan ES (Esai): tidak perlu options
                // SK (Skala): scale_min / scale_max sudah ada di model
                // MJ (Menjodohkan): pairs dan mj_options dari short_answers
                if ($q->type === 'MJ') {
                    $shortAnswers = $q->short_answers;
                    if (is_array($shortAnswers)) {
                        $pairs = [];
                        $mjOptions = [];
                        foreach ($shortAnswers as $item) {
                            if (isset($item['left'], $item['right'])) {
                                $pairs[] = ['left' => $item['left'], 'right' => $item['right']];
                                $mjOptions[] = $item['right'];
                            }
                        }
                        $data['pairs'] = $pairs;
                        $data['mj_options'] = $mjOptions;
                    }
                }

                return $data;
            });

            // Security settings untuk blade
            $securitySettings = [
                'fullscreen_mode'     => (bool) $exam->fullscreen_mode,
                'block_new_tab'       => (bool) $exam->block_new_tab,
                'prevent_copy_paste'  => (bool) $exam->prevent_copy_paste,
                'disable_violations'  => (bool) $exam->disable_violations,
                'violation_limit'     => (int) ($exam->violation_limit ?? 3),
                'enable_proctoring'   => (bool) $exam->enable_proctoring,
                'require_camera'      => (bool) $exam->require_camera,
                'require_mic'         => (bool) $exam->require_mic,
            ];

            // Marked for review dari attempt settings
            $attemptSettings  = $attempt->exam_settings ?? [];
            $markedForReview  = $attemptSettings['marked_for_review'] ?? [];

            return view('murid.exams.attempt', compact(
                'exam',
                'attempt',
                'questions',
                'existingAnswers',
                'timeRemaining',
                'securitySettings',
                'markedForReview'
            ))->with('questionsFormatted', $questionsFormatted);

        } catch (\Exception $e) {
            Log::error('Error accessing exam attempt', [
                'error' => $e->getMessage(),
                'exam_id' => $examId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('soal.index')
                ->with('error', 'Tidak dapat mengakses ujian: ' . $e->getMessage());
        }
    }

    /**
     * ==========================================
     * SAVE ANSWER - SIMPAN JAWABAN (AJAX)
     * ==========================================
     * POST /soal/{examId}/answer/{questionId}
     * Simpan jawaban siswa untuk satu soal
     */
    public function saveAnswer(Request $request, $examId, $questionId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 403);
            }

            // Cek attempt aktif
            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian tidak ditemukan atau sudah berakhir.'
                ], 404);
            }

            // Cek timeout
            $exam = $attempt->exam;
            if ($exam->duration && $exam->duration > 0) {
                $elapsed = $attempt->started_at->diffInMinutes(now());
                if ($elapsed >= $exam->duration) {
                    $attempt->timeout();
                    return response()->json([
                        'success' => false,
                        'message' => 'Waktu ujian telah habis.',
                        'timeout' => true
                    ], 400);
                }
            }

            // Load question
            $question = ExamQuestion::where('exam_id', $examId)
                ->where('id', $questionId)
                ->with('choices')
                ->first();

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Soal tidak ditemukan.'
                ], 404);
            }

            // Validasi input berdasarkan tipe soal
            $validator = null;

            if (in_array($question->type, ['PG', 'PGK', 'DD'])) {
                // Multiple choice
                $validator = Validator::make($request->all(), [
                    'choice_id' => 'required|exists:exam_choices,id'
                ]);
            } elseif (in_array($question->type, ['IS', 'ES', 'BS'])) {
                // Text answer
                $validator = Validator::make($request->all(), [
                    'answer_text' => 'required|string'
                ]);
            }

            if ($validator && $validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan atau update jawaban
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'student_id' => $student->id,
                    'attempt_id' => $attempt->id,
                ],
                [
                    'choice_id' => $request->choice_id ?? null,
                    'answer_text' => $request->answer_text ?? null,
                    'answered_at' => now(),
                ]
            );

            // Auto-calculate score untuk pilihan ganda
            if (in_array($question->type, ['PG', 'DD'])) {
                $answer->calculateScore();
            }

            Log::info('Answer saved', [
                'answer_id' => $answer->id,
                'question_id' => $questionId,
                'student_id' => $student->id,
                'attempt_id' => $attempt->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan.',
                'answer' => [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'answered_at' => $answer->answered_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving answer', [
                'error' => $e->getMessage(),
                'exam_id' => $examId,
                'question_id' => $questionId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * SUBMIT - SUBMIT UJIAN
     * ==========================================
     * POST /soal/{examId}/submit
     * Submit semua jawaban dan hitung nilai
     */
    public function submit(Request $request, $examId)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // Cek attempt aktif
            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                DB::rollBack();
                return redirect()->route('soal.index')
                    ->with('error', 'Sesi ujian tidak ditemukan.');
            }

            // =========================================================
            // PROSES JAWABAN DARI FORM (answers JSON dari Alpine.js)
            // Format: {"questionId": "choiceLabel/answerText", ...}
            // =========================================================
            $submittedAnswers = [];
            if ($request->has('answers') && !empty($request->answers)) {
                try {
                    $submittedAnswers = json_decode($request->answers, true) ?? [];
                } catch (\Exception $e) {
                    $submittedAnswers = [];
                }
            }

            if (!empty($submittedAnswers)) {
                // Load semua soal beserta choices untuk mapping label -> choice_id
                $questions = ExamQuestion::where('exam_id', $examId)
                    ->with('choices')
                    ->get()
                    ->keyBy('id');

                // Label mapping untuk PG (A=0, B=1, C=2, dst)
                $labelMap = ['A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5];

                foreach ($submittedAnswers as $questionId => $answerValue) {
                    $questionId = (int) $questionId;
                    $question = $questions->get($questionId);
                    if (!$question) continue;

                    $choiceId  = null;
                    $answerText = null;
                    $isCorrect  = false;
                    $score      = 0;

                    if (in_array($question->type, ['PG', 'DD'])) {
                        // answerValue = label "A","B","C",...
                        $labelStr = strtoupper(trim((string) $answerValue));
                        $labelIndex = $labelMap[$labelStr] ?? null;
                        if ($labelIndex !== null) {
                            $sortedChoices = $question->choices->sortBy('order')->values();
                            $choice = $sortedChoices->get($labelIndex);
                            if ($choice) {
                                $choiceId = $choice->id;
                                $isCorrect = (bool) $choice->is_correct;
                                $score = $isCorrect ? $question->score : 0;
                            }
                        }
                    } elseif ($question->type === 'PGK') {
                        // answerValue = "A,B,C" (multiple labels)
                        $answerText = (string) $answerValue;
                        $selectedLabels = array_map('trim', explode(',', $answerText));
                        $sortedChoices = $question->choices->sortBy('order')->values();
                        $correctLabels = [];
                        foreach ($sortedChoices as $idx => $choice) {
                            if ($choice->is_correct) {
                                $correctLabels[] = $labelMap[$idx] ?? chr(65 + $idx);
                            }
                        }
                        // Semua label harus cocok
                        sort($selectedLabels); sort($correctLabels);
                        $isCorrect = ($selectedLabels === $correctLabels);
                        $score = $isCorrect ? $question->score : 0;
                    } elseif (in_array($question->type, ['BS', 'IS', 'ES', 'SK', 'MJ'])) {
                        // answerValue = text/string
                        $answerText = (string) $answerValue;

                        if ($question->type === 'BS') {
                            // Jawaban = "benar" atau "salah"
                            $sa = $question->short_answers;
                            $correct = is_array($sa) ? strtolower($sa[0] ?? '') : strtolower((string)$sa);
                            $isCorrect = strtolower(trim($answerText)) === $correct;
                            $score = $isCorrect ? $question->score : 0;
                        } elseif ($question->type === 'IS') {
                            // Isian singkat — bandingkan dengan daftar jawaban diterima
                            $sa = $question->short_answers;
                            $acceptedAnswers = $sa['answers'] ?? (is_array($sa) ? $sa : []);
                            $caseSensitive = $sa['case_sensitive'] ?? false;
                            foreach ($acceptedAnswers as $acc) {
                                $given = $caseSensitive ? trim($answerText) : strtolower(trim($answerText));
                                $expected = $caseSensitive ? trim($acc) : strtolower(trim($acc));
                                if ($given === $expected) { $isCorrect = true; break; }
                            }
                            $score = $isCorrect ? $question->score : 0;
                        } elseif ($question->type === 'SK') {
                            // Skala linear
                            $sa = $question->short_answers;
                            if (isset($sa['correct'])) {
                                $isCorrect = trim($answerText) === trim((string)$sa['correct']);
                                $score = $isCorrect ? $question->score : 0;
                            }
                        }
                        // ES dan MJ: dinilai manual (score & is_correct tetap 0/false)
                    } else {
                        $answerText = (string) $answerValue;
                    }

                    // Simpan atau update jawaban
                    ExamAnswer::updateOrCreate(
                        [
                            'attempt_id'  => $attempt->id,
                            'exam_id'     => $examId,
                            'question_id' => $questionId,
                            'student_id'  => $student->id,
                        ],
                        [
                            'choice_id'   => $choiceId,
                            'answer_text' => $answerText,
                            'is_correct'  => $isCorrect,
                            'score'       => $score,
                            'answered_at' => now(),
                        ]
                    );
                }
            }

            // Hitung ulang total score dan final_score sebelum submit
            $totalScore = ExamAnswer::where('attempt_id', $attempt->id)->sum('score');
            $maxScore   = ExamQuestion::where('exam_id', $examId)->sum('score');
            $finalScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;

            $attempt->score       = $totalScore;
            $attempt->final_score = $finalScore;
            $attempt->status      = 'submitted';
            $attempt->ended_at    = now();
            $attempt->save();

            // Panggil submit() model jika masih diperlukan untuk cleanup lain
            // $attempt->submit(); // Dikomentari karena sudah handle manual di atas

            DB::commit();

            Log::info('Exam submitted successfully', [
                'attempt_id'  => $attempt->id,
                'exam_id'     => $examId,
                'student_id'  => $student->id,
                'total_score' => $totalScore,
                'final_score' => $finalScore,
                'answers_count' => count($submittedAnswers),
            ]);

            return redirect()->route('soal.result', $attempt->id)
                ->with('success', 'Ujian berhasil dikumpulkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting exam', [
                'error'   => $e->getMessage(),
                'exam_id' => $examId,
                'user_id' => Auth::id(),
                'trace'   => $e->getTraceAsString()
            ]);

            return redirect()->route('soal.index')
                ->with('error', 'Terjadi kesalahan saat mengumpulkan ujian: ' . $e->getMessage());
        }
    }

    /**
     * ==========================================
     * RESULT - TAMPILKAN HASIL UJIAN
     * ==========================================
     * GET /soal/result/{attemptId}
     * Tampilkan hasil ujian setelah dikumpulkan
     */
    public function result($attemptId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('soal.index')
                    ->with('error', 'Data siswa tidak ditemukan.');
            }

            // Load attempt dengan semua relasi yang dibutuhkan blade
            $attempt = ExamAttempt::with([
                'exam.questions.choices',
                'exam.subject',
                'exam.class',
                'exam.teacher.user',
                'answers.question.choices',
                'answers.choice',
            ])
            ->where('id', $attemptId)
            ->where('student_id', $student->id)
            ->first();

            if (!$attempt) {
                return redirect()->route('soal.index')
                    ->with('error', 'Hasil ujian tidak ditemukan. Pastikan Anda sudah mengerjakan ujian ini.');
            }

            // Pastikan status sudah submitted atau timeout (bukan in_progress)
            if ($attempt->status === 'in_progress') {
                return redirect()->route('soal.attempt', $attempt->exam_id)
                    ->with('error', 'Ujian masih berlangsung. Kumpulkan terlebih dahulu.');
            }

            $exam = $attempt->exam;

            // Cek visibilitas hasil
            $canViewResult        = true;
            $canViewCorrectAnswer = (bool) ($exam->show_correct_answer ?? false);

            if (($exam->show_result_after ?? '') === 'never') {
                $canViewResult = false;
            } elseif (($exam->show_result_after ?? '') === 'after_exam_end'
                && $exam->end_at && now() < $exam->end_at) {
                $canViewResult = false;
            }

            // Hitung statistik
            $totalQuestions    = $exam->questions->count();
            $answeredQuestions = $attempt->answers->count();
            $correctAnswers    = $attempt->answers->where('is_correct', true)->count();
            $wrongAnswers      = $answeredQuestions - $correctAnswers;
            $unansweredQuestions = $totalQuestions - $answeredQuestions;

            return view('murid.exams.result', compact(
                'attempt',
                'exam',
                'canViewResult',
                'canViewCorrectAnswer',
                'totalQuestions',
                'answeredQuestions',
                'correctAnswers',
                'wrongAnswers',
                'unansweredQuestions'
            ));

        } catch (\Exception $e) {
            Log::error('Error viewing exam result', [
                'error'      => $e->getMessage(),
                'attempt_id' => $attemptId,
                'user_id'    => Auth::id(),
                'trace'      => $e->getTraceAsString()
            ]);

            return redirect()->route('soal.index')
                ->with('error', 'Tidak dapat menampilkan hasil ujian: ' . $e->getMessage());
        }
    }

    /**
     * ==========================================
     * ADDITIONAL METHODS
     * ==========================================
     */

    /**
     * GET /soal/active
     * Tampilkan ujian yang sedang aktif
     */
    public function active()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return view('Siswa.Exam.active', ['exams' => collect()]);
        }

        $classId = $this->getStudentClassId($student);

        if (!$classId) {
            return view('Siswa.Exam.active', [
                'exams' => collect(),
                'message' => 'Anda belum memiliki kelas.'
            ]);
        }

        $exams = Exam::where('class_id', $classId)
            ->where('type', '!=', 'QUIZ')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->with(['subject', 'class'])
            ->orderBy('end_at', 'asc')
            ->get();

        foreach ($exams as $exam) {
            $this->enrichExamData($exam, $user->id);
        }

        return view('Siswa.Exam.active', compact('exams'));
    }

    /**
     * GET /soal/upcoming
     * Tampilkan ujian yang akan datang
     */
    public function upcoming()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return view('Siswa.Exam.upcoming', ['exams' => collect()]);
        }

        $classId = $this->getStudentClassId($student);

        if (!$classId) {
            return view('Siswa.Exam.upcoming', [
                'exams' => collect(),
                'message' => 'Anda belum memiliki kelas.'
            ]);
        }

        $exams = Exam::where('class_id', $classId)
            ->where('type', '!=', 'QUIZ')
            ->where('status', 'active')
            ->where('start_at', '>', now())
            ->with(['subject', 'class'])
            ->orderBy('start_at', 'asc')
            ->get();

        foreach ($exams as $exam) {
            $this->enrichExamData($exam, $user->id);
        }

        return view('Siswa.Exam.upcoming', compact('exams'));
    }

    /**
     * GET /soal/completed
     * Tampilkan ujian yang sudah selesai
     */
    public function completed()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return view('Siswa.Exam.completed', ['exams' => collect()]);
        }

        $classId = $this->getStudentClassId($student);

        if (!$classId) {
            return view('Siswa.Exam.completed', [
                'exams' => collect(),
                'message' => 'Anda belum memiliki kelas.'
            ]);
        }

        $exams = Exam::where('class_id', $classId)
            ->where('type', '!=', 'QUIZ')
            ->where(function ($query) {
                $query->where('status', 'inactive')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                          ->whereNotNull('end_at')
                          ->where('end_at', '<', now());
                    });
            })
            ->with(['subject', 'class'])
            ->orderBy('end_at', 'desc')
            ->get();

        foreach ($exams as $exam) {
            $this->enrichExamData($exam, $user->id);
        }

        return view('Siswa.Exam.completed', compact('exams'));
    }

    /**
     * ==========================================
     * HELPER METHODS
     * ==========================================
     */

    /**
     * Enrich exam data dengan informasi tambahan untuk siswa
     */
    private function enrichExamData($exam, $userId)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return $exam;
        }

        // Add attempt information
        $exam->user_attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        $exam->attempt_count = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        // Add accessibility info
        $exam->can_start = $this->canStartExam($exam, $student);
        $exam->access_message = $this->getAccessMessage($exam);

        return $exam;
    }

    /**
     * Cek apakah siswa bisa mulai ujian
     */
    private function canStartExam($exam, $student)
    {
        // Cek status
        if ($exam->status !== 'active') {
            return false;
        }

        // Cek waktu
        $now = now();
        if ($exam->start_at && $now < $exam->start_at) {
            return false;
        }

        if ($exam->end_at && $now > $exam->end_at) {
            return false;
        }

        // Cek attempt yang sedang berjalan
        $activeAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeAttempt) {
            return true; // Bisa lanjut attempt
        }

        // Cek limit attempts
        if ($exam->limit_attempts && $exam->limit_attempts > 0) {
            $attemptCount = ExamAttempt::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'timeout'])
                ->count();

            if ($attemptCount >= $exam->limit_attempts) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get access message for exam
     */
    private function getAccessMessage($exam)
    {
        if ($exam->status !== 'active') {
            return 'Ujian belum aktif';
        }

        $now = now();

        if ($exam->start_at && $now < $exam->start_at) {
            return 'Ujian dimulai pada ' . $exam->start_at->format('d M Y, H:i');
        }

        if ($exam->end_at && $now > $exam->end_at) {
            return 'Waktu ujian telah berakhir pada ' . $exam->end_at->format('d M Y, H:i');
        }

        return null;
    }

    /**
     * POST /soal/{examId}/violation
     * Log violation (untuk proctoring)
     */
    public function logViolation(Request $request, $examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 403);
            }

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian tidak ditemukan.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'details' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $attempt->logViolation($request->type, $request->details);

            return response()->json([
                'success' => true,
                'message' => 'Pelanggaran tercatat',
                'violation_count' => $attempt->violation_count,
                'is_cheating_detected' => $attempt->is_cheating_detected
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging violation', [
                'error' => $e->getMessage(),
                'exam_id' => $examId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * GET /soal/{examId}/time-remaining
     * Get remaining time (AJAX)
     */
    public function getTimeRemaining($examId)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 403);
            }

            $attempt = ExamAttempt::where('exam_id', $examId)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian tidak ditemukan.',
                    'timeout' => true
                ], 404);
            }

            $remaining = $attempt->getTimeRemaining();

            if ($remaining <= 0) {
                $attempt->timeout();
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu habis',
                    'timeout' => true,
                    'remaining' => 0
                ]);
            }

            return response()->json([
                'success' => true,
                'remaining' => $remaining,
                'formatted' => gmdate('H:i:s', $remaining)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting time remaining', [
                'error' => $e->getMessage(),
                'exam_id' => $examId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }
}
