<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // BASIC INFO
        'teacher_id',
        'class_id',
        'subject_id',
        'title',
        'type',
        'custom_type',
        'duration',

        // TIMING (optional untuk quiz)
        'start_at',
        'end_at',

        // QUIZ SETTINGS
        'time_per_question',
        'quiz_mode',
        'difficulty_level',

        // FLOW SETTINGS
        'shuffle_question',
        'shuffle_answer',

        // SECURITY SETTINGS
        'fullscreen_mode',
        'block_new_tab',
        'prevent_copy_paste',
        'disable_violations',
        'violation_limit',

        // PROCTORING
        'enable_proctoring',
        'require_camera',
        'require_mic',
        'show_explanation',

        // RESULT SETTINGS
        'show_score',
        'show_correct_answer',
        'show_result_after',
        'limit_attempts',
        'min_pass_grade',

        // QUIZ FEATURES
        'show_leaderboard',
        'enable_music',
        'enable_memes',
        'enable_powerups',
        'instant_feedback',
        'streak_bonus',
        'time_bonus',
        'enable_retake',

        // ROOM SETTINGS
        'is_room_open',
        'is_quiz_started',
        'quiz_started_at',
        'quiz_remaining_time',

        // STATUS
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'quiz_started_at' => 'datetime',

        // BOOLEANS
        'shuffle_question' => 'boolean',
        'shuffle_answer' => 'boolean',
        'show_score' => 'boolean',
        'show_correct_answer' => 'boolean',
        'fullscreen_mode' => 'boolean',
        'block_new_tab' => 'boolean',
        'prevent_copy_paste' => 'boolean',
        'disable_violations' => 'boolean',
        'enable_proctoring' => 'boolean',
        'require_camera' => 'boolean',
        'require_mic' => 'boolean',
        'show_leaderboard' => 'boolean',
        'instant_feedback' => 'boolean',
        'enable_music' => 'boolean',
        'enable_memes' => 'boolean',
        'enable_powerups' => 'boolean',
        'streak_bonus' => 'boolean',
        'time_bonus' => 'boolean',
        'enable_retake' => 'boolean',
        'is_room_open' => 'boolean',
        'is_quiz_started' => 'boolean',

        // NUMERIC
        'duration' => 'integer',
        'time_per_question' => 'integer',
        'violation_limit' => 'integer',
        'limit_attempts' => 'integer',
        'min_pass_grade' => 'decimal:2',
        'quiz_remaining_time' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'is_draft',
        'exam_status',
        'total_questions',
        'total_score',
        'is_quiz_live',
        'room_status',
    ];

    /* ================= RELATIONSHIPS ================= */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function quizSessions()
    {
        return $this->hasMany(QuizSession::class, 'exam_id');
    }

    public function activeSession()
    {
        return $this->hasOne(QuizSession::class, 'exam_id')
            ->where('session_status', '!=', 'finished')
            ->latest();
    }

    public function activeQuizSession()
    {
        return $this->hasOne(QuizSession::class, 'exam_id')
            ->where('session_status', '!=', 'ended')
            ->latestOfMany();
    }

    public function participants()
    {
        return $this->hasManyThrough(
            QuizParticipant::class,
            QuizSession::class,
            'exam_id',
            'quiz_session_id'
        );
    }

    /* ================= ATTRIBUTE ACCESSORS ================= */
    public function getIsQuizAttribute()
    {
        return $this->type === 'QUIZ';
    }

    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }

    public function getTotalScoreAttribute()
    {
        return $this->questions()->sum('score');
    }

    public function getIsQuizLiveAttribute()
    {
        return $this->is_quiz && $this->is_room_open;
    }

    public function getRoomStatusAttribute()
    {
        if (!$this->is_quiz) return 'not_applicable';

        if ($this->is_quiz_started) return 'quiz_started';
        if ($this->is_room_open) return 'room_open';
        return 'room_closed';
    }

    /* ================= METHODS ================= */

    /**
     * ✅ FIX: Tambahkan method getTotalScore() untuk kompatibilitas
     * Mengembalikan total skor maksimal dari semua soal.
     */
    public function getTotalScore()
    {
        return $this->total_score; // memanggil accessor
    }

    public function getRoomParticipants()
    {
        $session = $this->activeSession;

        if (!$session) {
            return collect();
        }

        return $session->participants()
            ->with(['student' => function ($query) {
                $query->select('id', 'name', 'email');
            }])
            ->where('is_present', true)
            ->orderBy('joined_at', 'asc')
            ->get();
    }

    public function getRoomStats()
    {
        $session = $this->activeSession;

        if (!$session) {
            return [
                'total_students' => $this->class ? $this->class->students()->count() : 0,
                'joined' => 0,
                'ready' => 0,
                'started' => 0,
                'submitted' => 0
            ];
        }

        $participants = $session->participants()
            ->where('is_present', true)
            ->get();

        return [
            'total_students' => $this->class ? $this->class->students()->count() : 0,
            'joined' => $participants->count(),
            'ready' => $participants->where('status', 'ready')->count(),
            'started' => $participants->where('status', 'started')->count(),
            'submitted' => $participants->where('status', 'submitted')->count()
        ];
    }

    public function joinRoom($studentId, $request = null)
    {
        if (!$this->is_room_open || $this->is_quiz_started) {
            return false;
        }

        $session = $this->activeSession;

        if (!$session) {
            return false;
        }

        $participant = QuizParticipant::where([
            'quiz_session_id' => $session->id,
            'student_id' => $studentId
        ])->first();

        if ($participant) {
            $participant->update([
                'is_present' => true,
                'status' => 'waiting',
                'joined_at' => now()
            ]);
        } else {
            $participant = QuizParticipant::create([
                'quiz_session_id' => $session->id,
                'student_id' => $studentId,
                'exam_id' => $this->id,
                'status' => 'waiting',
                'joined_at' => now(),
                'is_present' => true,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->header('User-Agent') : null,
            ]);
        }

        $session->updateStats();

        return $participant;
    }

    public function getAllSettings()
    {
        $settings = [
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'time_per_question' => $this->time_per_question,
            'quiz_mode' => $this->quiz_mode,
            'difficulty_level' => $this->difficulty_level,
            'shuffle_question' => (bool) $this->shuffle_question,
            'shuffle_answer' => (bool) $this->shuffle_answer,
            'fullscreen_mode' => (bool) $this->fullscreen_mode,
            'block_new_tab' => (bool) $this->block_new_tab,
            'prevent_copy_paste' => (bool) $this->prevent_copy_paste,
            'disable_violations' => (bool) $this->disable_violations,
            'violation_limit' => (int) ($this->violation_limit ?? 3),
            'enable_proctoring' => (bool) $this->enable_proctoring,
            'require_camera' => (bool) $this->require_camera,
            'require_mic' => (bool) $this->require_mic,
            'show_score' => (bool) $this->show_score,
            'show_correct_answer' => (bool) $this->show_correct_answer,
            'show_result_after' => $this->show_result_after ?? 'never',
            'limit_attempts' => (int) ($this->limit_attempts ?? 1),
            'min_pass_grade' => (float) ($this->min_pass_grade ?? 0),
            'is_quiz' => $this->is_quiz,
            'show_leaderboard' => (bool) $this->show_leaderboard,
            'instant_feedback' => (bool) $this->instant_feedback,
            'enable_music' => (bool) $this->enable_music,
            'enable_memes' => (bool) $this->enable_memes,
            'enable_powerups' => (bool) $this->enable_powerups,
            'streak_bonus' => (bool) $this->streak_bonus,
            'time_bonus' => (bool) $this->time_bonus,
            'enable_retake' => (bool) $this->enable_retake,
            'is_room_open' => (bool) $this->is_room_open,
            'is_quiz_started' => (bool) $this->is_quiz_started,
            'quiz_started_at' => $this->quiz_started_at,
            'quiz_remaining_time' => $this->quiz_remaining_time,
        ];

        if (!$this->is_quiz) {
            unset(
                $settings['time_per_question'],
                $settings['quiz_mode'],
                $settings['difficulty_level'],
                $settings['show_leaderboard'],
                $settings['instant_feedback'],
                $settings['enable_music'],
                $settings['enable_memes'],
                $settings['enable_powerups'],
                $settings['streak_bonus'],
                $settings['time_bonus'],
                $settings['is_room_open'],
                $settings['is_quiz_started'],
                $settings['quiz_started_at'],
                $settings['quiz_remaining_time']
            );
        }

        return $settings;
    }

    public function shouldAutoSubmit($violationCount)
    {
        if ($this->disable_violations) {
            return false;
        }

        return $violationCount >= ($this->violation_limit ?? 3);
    }

    public function getDisplayType()
    {
        if ($this->type === 'LAINNYA' && $this->custom_type) {
            return $this->custom_type;
        }

        $types = [
            'UH' => 'Ulangan Harian',
            'UTS' => 'Ujian Tengah Semester',
            'UAS' => 'Ujian Akhir Semester',
            'QUIZ' => 'Kuis',
            'LAINNYA' => 'Ujian Lainnya'
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function canAccessQuiz()
    {
        if (!$this->is_quiz) {
            return false;
        }

        if ($this->status !== 'active') {
            return false;
        }

        if (!$this->is_room_open) {
            return false;
        }

        return true;
    }

    public function openRoom()
    {
        if (!$this->is_quiz) {
            return false;
        }

        $this->update([
            'is_room_open' => true,
            'is_quiz_started' => false,
            'quiz_started_at' => null,
            'quiz_remaining_time' => null,
        ]);

        QuizSession::create([
            'exam_id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'session_status' => 'waiting',
        ]);

        return true;
    }

    public function startQuiz()
    {
        if (!$this->is_quiz || !$this->is_room_open || $this->is_quiz_started) {
            return false;
        }

        $totalQuestions = $this->questions()->count();
        $totalSeconds = $totalQuestions * ($this->time_per_question ?? 30);

        $this->update([
            'is_quiz_started' => true,
            'quiz_started_at' => now(),
            'quiz_remaining_time' => $totalSeconds,
        ]);

        $this->activeSession()->update([
            'session_status' => 'active',
            'session_started_at' => now(),
            'total_duration' => $totalSeconds,
        ]);

        return true;
    }

    public function closeRoom()
    {
        if (!$this->is_quiz) {
            return false;
        }

        $this->update([
            'is_room_open' => false,
            'is_quiz_started' => false,
            'quiz_started_at' => null,
            'quiz_remaining_time' => null,
        ]);

        if ($session = $this->activeSession) {
            $session->update([
                'session_status' => 'finished',
                'session_ended_at' => now(),
            ]);
        }

        return true;
    }

    public function isQuizRunning()
    {
        if (!$this->is_quiz || !$this->is_quiz_started || !$this->quiz_started_at) {
            return false;
        }

        $elapsedSeconds = now()->diffInSeconds($this->quiz_started_at);
        $totalSeconds = $this->quiz_remaining_time ?? ($this->questions()->count() * ($this->time_per_question ?? 30));

        return $elapsedSeconds < $totalSeconds;
    }

    public function canAccessRegularExam()
    {
        if ($this->is_quiz) {
            return false;
        }

        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->start_at && $now < $this->start_at) {
            return false;
        }

        if ($this->end_at && $now > $this->end_at) {
            return false;
        }

        return true;
    }

    public function getExamStatusAttribute()
    {
        if ($this->status !== 'active') {
            return $this->status;
        }

        if ($this->is_quiz) {
            if ($this->is_quiz_started) {
                return 'quiz_berlangsung';
            }
            if ($this->is_room_open) {
                return 'ruangan_terbuka';
            }
            return 'aktif';
        } else {
            $now = now();

            if ($this->start_at && $now < $this->start_at) {
                return 'belum_dimulai';
            }

            if ($this->end_at && $now > $this->end_at) {
                return 'selesai';
            }

            return 'aktif';
        }
    }

    public function getIsDraftAttribute()
    {
        return $this->status === 'draft';
    }

    public function canStartNow()
    {
        if ($this->is_quiz) {
            return $this->canAccessQuiz() && $this->is_quiz_started;
        }

        return $this->canAccessRegularExam();
    }

    public function getTotalQuizTime()
    {
        if (!$this->is_quiz) {
            return null;
        }

        $totalQuestions = $this->questions()->count();
        return $totalQuestions * ($this->time_per_question ?? 30);
    }

    public function startQuizForStudents()
    {
        if (!$this->is_quiz || !$this->is_room_open || !$this->is_quiz_started) {
            return false;
        }

        $session = $this->activeSession;
        if (!$session) return false;

        $participants = $session->participants()
            ->whereIn('status', ['waiting', 'ready'])
            ->get();

        foreach ($participants as $participant) {
            $participant->update(['status' => 'started', 'started_at' => now()]);
        }

        return true;
    }

    public function getParticipantStats()
    {
        $session = $this->activeSession;

        if (!$session) {
            return [
                'total' => 0,
                'joined' => 0,
                'waiting' => 0,
                'ready' => 0,
                'started' => 0,
                'submitted' => 0,
            ];
        }

        $participants = $session->participants;

        return [
            'total' => $participants->count(),
            'joined' => $participants->where('is_present', true)->count(),
            'waiting' => $participants->where('status', 'waiting')->count(),
            'ready' => $participants->where('status', 'ready')->count(),
            'started' => $participants->where('status', 'started')->count(),
            'submitted' => $participants->where('status', 'submitted')->count(),
        ];
    }

    public function getActiveSessionAttribute()
    {
        if (!$this->relationLoaded('activeQuizSession')) {
            $this->load('activeQuizSession');
        }
        return $this->activeQuizSession;
    }

    public function canStartQuiz()
    {
        $stats = $this->getParticipantStats();
        return $stats['students_ready'] > 0;
    }

    public function getQuizTimeRemaining()
    {
        if (!$this->is_quiz_started || !$this->quiz_started_at) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->quiz_started_at);
        $totalTime = $this->duration * 60;
        $remaining = max(0, $totalTime - $elapsed);

        return $remaining;
    }

    public function getFormattedQuizTimeRemaining()
    {
        $seconds = $this->getQuizTimeRemaining();

        if ($seconds === null) {
            return 'Belum dimulai';
        }

        if ($seconds <= 0) {
            return 'Waktu habis';
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function openRoomWithCode()
    {
        if (!$this->is_quiz) {
            return null;
        }

        try {
            DB::beginTransaction();

            $sessionCode = strtoupper(Str::random(6));

            $session = QuizSession::create([
                'exam_id' => $this->id,
                'teacher_id' => $this->teacher_id,
                'session_code' => $sessionCode,
                'session_status' => 'waiting',
                'session_started_at' => now(),
            ]);

            $this->update([
                'is_room_open' => true,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => null,
            ]);

            DB::commit();

            return $session;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error opening room: ' . $e->getMessage());
            return null;
        }
    }

    public function getRoomAccessUrl()
    {
        if (!$this->is_room_open) {
            return null;
        }

        $session = $this->activeSession;
        if (!$session) {
            return null;
        }

        return route('siswa.quiz.join', [
            'code' => $session->session_code,
            'exam_id' => $this->id
        ]);
    }
}
