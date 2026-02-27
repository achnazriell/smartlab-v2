<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * ✅ CRITICAL FIX: Relationship students untuk quiz assignment
     * Ini adalah relationship yang SANGAT PENTING untuk sistem quiz
     * Tanpa ini, quiz tidak akan ter-assign ke siswa
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'exam_student', 'exam_id', 'student_id')
            ->withTimestamps();
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
            ->where('session_status', '!=', 'finished')
            ->latest();
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

    public function getActiveSessionAttribute()
    {
        if (!$this->relationLoaded('activeQuizSession')) {
            $this->load('activeQuizSession');
        }
        return $this->activeQuizSession;
    }

    /* ================= METHODS ================= */

    /**
     * ✅ FIX: Method getTotalScore() untuk kompatibilitas
     */
    public function getTotalScore()
    {
        return $this->total_score;
    }

    /**
     * ✅ Get all quiz settings
     */
    public function getAllSettings()
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'time_per_question' => $this->time_per_question,
            'quiz_mode' => $this->quiz_mode,
            'difficulty_level' => $this->difficulty_level,
            'shuffle_question' => $this->shuffle_question,
            'shuffle_answer' => $this->shuffle_answer,
            'fullscreen_mode' => $this->fullscreen_mode,
            'block_new_tab' => $this->block_new_tab,
            'prevent_copy_paste' => $this->prevent_copy_paste,
            'disable_violations' => $this->disable_violations,
            'violation_limit' => $this->violation_limit,
            'enable_proctoring' => $this->enable_proctoring,
            'require_camera' => $this->require_camera,
            'require_mic' => $this->require_mic,
            'show_score' => $this->show_score,
            'show_correct_answer' => $this->show_correct_answer,
            'show_result_after' => $this->show_result_after,
            'limit_attempts' => $this->limit_attempts,
            'min_pass_grade' => $this->min_pass_grade,
            'show_leaderboard' => $this->show_leaderboard,
            'enable_music' => $this->enable_music,
            'enable_memes' => $this->enable_memes,
            'enable_powerups' => $this->enable_powerups,
            'instant_feedback' => $this->instant_feedback,
            'streak_bonus' => $this->streak_bonus,
            'time_bonus' => $this->time_bonus,
            'enable_retake' => $this->enable_retake,
        ];
    }

    /* ================= ROOM MANAGEMENT METHODS ================= */

    /**
     * Check if student can access quiz
     */
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

    /**
     * Get room participants
     */
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

    /**
     * Get room statistics
     */
    public function getRoomStats()
    {
        $session = $this->activeSession;

        if (!$session) {
            return [
                'total_students' => 0,
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
            'total_students' => $this->students()->count(),
            'joined' => $participants->count(),
            'ready' => $participants->where('status', 'ready')->count(),
            'started' => $participants->where('status', 'started')->count(),
            'submitted' => $participants->where('status', 'submitted')->count()
        ];
    }

    /**
     * Get participant statistics
     */
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

    /**
     * Student joins room
     */
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

    /**
     * Open room with session code
     */
    public function openRoomWithCode()
    {
        if (!$this->is_quiz) {
            return null;
        }

        try {
            DB::beginTransaction();

            $sessionCode = strtoupper(Str::random(6));

            // Check if code already exists
            while (QuizSession::where('session_code', $sessionCode)->exists()) {
                $sessionCode = strtoupper(Str::random(6));
            }

            $session = QuizSession::create([
                'exam_id' => $this->id,
                'teacher_id' => $this->teacher_id,
                'session_code' => $sessionCode,
                'session_status' => 'waiting',
                'total_duration' => $this->duration,
            ]);

            $this->update([
                'is_room_open' => true,
                'is_quiz_started' => false,
                'quiz_started_at' => null,
                'quiz_remaining_time' => $this->duration * 60,
            ]);

            DB::commit();

            return $session;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error opening room: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Start quiz
     */
    public function startQuiz()
    {
        if (!$this->is_quiz || !$this->is_room_open || $this->is_quiz_started) {
            return false;
        }

        $totalQuestions = $this->questions()->count();
        $totalSeconds = $this->duration * 60; // Use duration in minutes

        $this->update([
            'is_quiz_started' => true,
            'quiz_started_at' => now(),
            'quiz_remaining_time' => $totalSeconds,
        ]);

        $session = $this->activeSession;
        if ($session) {
            $session->update([
                'session_status' => 'active',
                'session_started_at' => now(),
                'total_duration' => $totalSeconds,
            ]);
        }

        return true;
    }

    /**
     * Close room
     */
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

    /**
     * Start quiz for all ready students
     */
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

    /**
     * Check if quiz is currently running
     */
    public function isQuizRunning()
    {
        if (!$this->is_quiz || !$this->is_quiz_started || !$this->quiz_started_at) {
            return false;
        }

        $elapsedSeconds = now()->diffInSeconds($this->quiz_started_at);
        $totalSeconds = $this->duration * 60;

        return $elapsedSeconds < $totalSeconds;
    }

    /**
     * Get quiz time remaining in seconds
     */
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

    /**
     * Get formatted quiz time remaining
     */
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
        $secs = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Get total quiz time in seconds
     */
    public function getTotalQuizTime()
    {
        if (!$this->is_quiz) {
            return null;
        }

        return $this->duration * 60;
    }

    /**
     * Get room access URL
     */
    public function getRoomAccessUrl()
    {
        if (!$this->is_room_open) {
            return null;
        }

        $session = $this->activeSession;
        if (!$session) {
            return null;
        }

        return route('quiz.room', ['quiz' => $this->id]);
    }

    /**
     * Check if can start quiz
     */
    public function canStartQuiz()
    {
        $stats = $this->getParticipantStats();
        return $stats['ready'] > 0;
    }

    /**
     * Check if can start now
     */
    public function canStartNow()
    {
        if ($this->is_quiz) {
            return $this->canAccessQuiz() && $this->is_quiz_started;
        }

        return $this->canAccessRegularExam();
    }

    /**
     * Check if can access regular exam
     */
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
}
