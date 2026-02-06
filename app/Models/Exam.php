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

        // TIMING
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

        // STATUS
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',

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

        // NUMERIC
        'duration' => 'integer',
        'time_per_question' => 'integer',
        'violation_limit' => 'integer',
        'limit_attempts' => 'integer',
        'min_pass_grade' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'is_draft',
        'exam_status',
        'total_questions',
        'total_score',
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

    /* ================= METHODS ================= */

    /**
     * Get all settings as array untuk snapshot
     */
    public function getAllSettings()
    {
        return [
            // BASIC INFO
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,

            // TIMING
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,

            // QUIZ SETTINGS
            'time_per_question' => $this->time_per_question,
            'quiz_mode' => $this->quiz_mode,
            'difficulty_level' => $this->difficulty_level,

            // FLOW SETTINGS
            'shuffle_question' => (bool) $this->shuffle_question,
            'shuffle_answer' => (bool) $this->shuffle_answer,

            // SECURITY SETTINGS
            'fullscreen_mode' => (bool) $this->fullscreen_mode,
            'block_new_tab' => (bool) $this->block_new_tab,
            'prevent_copy_paste' => (bool) $this->prevent_copy_paste,
            'disable_violations' => (bool) $this->disable_violations,
            'violation_limit' => (int) ($this->violation_limit ?? 3),

            // PROCTORING
            'enable_proctoring' => (bool) $this->enable_proctoring,
            'require_camera' => (bool) $this->require_camera,
            'require_mic' => (bool) $this->require_mic,

            // RESULT SETTINGS
            'show_score' => (bool) $this->show_score,
            'show_correct_answer' => (bool) $this->show_correct_answer,
            'show_result_after' => $this->show_result_after ?? 'never',
            'limit_attempts' => (int) ($this->limit_attempts ?? 1),
            'min_pass_grade' => (float) ($this->min_pass_grade ?? 0),

            // QUIZ FEATURES
            'is_quiz' => $this->is_quiz,
            'show_leaderboard' => (bool) $this->show_leaderboard,
            'instant_feedback' => (bool) $this->instant_feedback,
            'enable_music' => (bool) $this->enable_music,
            'enable_memes' => (bool) $this->enable_memes,
            'enable_powerups' => (bool) $this->enable_powerups,
            'streak_bonus' => (bool) $this->streak_bonus,
            'time_bonus' => (bool) $this->time_bonus,
            'enable_retake' => (bool) $this->enable_retake,
        ];
    }

    /**
     * Check apakah harus auto submit karena violation
     */
    public function shouldAutoSubmit($violationCount)
    {
        // Jika violations dinonaktifkan, tidak auto submit
        if ($this->disable_violations) {
            return false;
        }

        // Auto submit jika mencapai limit
        return $violationCount >= ($this->violation_limit ?? 3);
    }

    /**
     * Get display type untuk UI
     */
    // Tambahkan di model Exam.php
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

    public function isUpcoming()
    {
        return $this->start_at && now() < $this->start_at && $this->status === 'active';
    }

    public function isFinished()
    {
        return $this->end_at && now() > $this->end_at;
    }

    public function isAvailableForStudent($studentId, $classId)
    {
        // Cek kelas
        if ($this->class_id != $classId) {
            return false;
        }

        // Cek status
        if ($this->status !== 'active') {
            return false;
        }

        // Cek waktu
        if (!$this->isOngoing() && !$this->isUpcoming()) {
            return false;
        }

        // Cek attempt
        $attemptCount = ExamAttempt::where('exam_id', $this->id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        if ($this->limit_attempts > 0 && $attemptCount >= $this->limit_attempts) {
            return false;
        }

        return true;
    }

    /**
     * Check apakah draft
     */
    public function getIsDraftAttribute()
    {
        return $this->status === 'draft';
    }

    /**
     * Check apakah exam bisa dikerjakan sekarang
     */
    public function canStartNow()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        // Jika tidak ada start_at, bisa mulai kapan saja
        if (!$this->start_at) {
            return true;
        }

        // Cek apakah sudah melewati start_at
        if ($now < $this->start_at) {
            return false;
        }

        // Cek apakah sudah melewati end_at
        if ($this->end_at && $now > $this->end_at) {
            return false;
        }

        return true;
    }

    /**
     * Get exam status string
     */
    public function getExamStatusAttribute()
    {
        $now = now();

        if ($this->status !== 'active') {
            return $this->status;
        }

        if ($this->start_at && $now < $this->start_at) {
            return 'belum_dimulai';
        }

        if ($this->end_at && $now > $this->end_at) {
            return 'selesai';
        }

        return 'aktif';
    }

    /**
     * Get time remaining untuk ujian
     */
    public function getTimeRemaining()
    {
        if (!$this->end_at) {
            return null;
        }

        $now = now();
        if ($now > $this->end_at) {
            return 0;
        }

        return $this->end_at->diffInSeconds($now);
    }

    /**
     * Get formatted time remaining
     */
    public function getFormattedTimeRemaining()
    {
        $seconds = $this->getTimeRemaining();

        if ($seconds === null) {
            return 'Tidak ada batasan waktu';
        }

        if ($seconds <= 0) {
            return 'Waktu habis';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Check apakah student sudah mengerjakan exam
     */
    public function hasAttempt($studentId)
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * Get latest attempt untuk student
     */
    public function getLatestAttempt($studentId)
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->latest()
            ->first();
    }

    /**
     * Check apakah student bisa mencoba lagi
     */
    public function canRetry($studentId)
    {
        if (!$this->enable_retake) {
            return false;
        }

        if (!$this->limit_attempts || $this->limit_attempts == 0) {
            return true;
        }

        $attemptCount = $this->attempts()
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();

        return $attemptCount < $this->limit_attempts;
    }

    /**
     * Get total attempts untuk student
     */
    public function getAttemptCount($studentId)
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'timeout'])
            ->count();
    }

    // Tambahkan di model Exam.php


    public function isAccessibleForStudent()
    {
        // Cek status aktif
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        // Cek waktu mulai
        if ($this->start_at && $now < $this->start_at) {
            return false;
        }

        // Cek waktu selesai
        if ($this->end_at && $now > $this->end_at) {
            return false;
        }

        // Jika tidak ada waktu mulai dan selesai, anggap bisa diakses
        // selama status active
        return true;
    }

    /**
     * Check apakah exam sedang berlangsung (ongoing)
     */
    public function isOngoing()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        // Jika tidak ada waktu, selalu ongoing jika status active
        if (!$this->start_at && !$this->end_at) {
            return true;
        }

        // Cek jika sudah mulai dan belum selesai
        $hasStarted = !$this->start_at || $now >= $this->start_at;
        $hasEnded = $this->end_at && $now > $this->end_at;

        return $hasStarted && !$hasEnded;
    }

    /**
     * Get status waktu ujian
     */
    public function getTimeStatus()
    {
        if ($this->status !== 'active') {
            return 'inactive';
        }

        $now = now();

        if ($this->start_at && $now < $this->start_at) {
            return 'upcoming';
        }

        if ($this->end_at && $now > $this->end_at) {
            return 'finished';
        }

        // Jika dalam rentang waktu atau tidak ada batasan waktu
        if ((!$this->start_at || $now >= $this->start_at) &&
            (!$this->end_at || $now <= $this->end_at)
        ) {
            return 'ongoing';
        }

        return 'unknown';
    }

    public function canBeAccessedByStudent($studentId, $classId)
    {
        // Log untuk debugging
        \Log::info('Exam access check', [
            'exam_id' => $this->id,
            'student_id' => $studentId,
            'exam_class' => $this->class_id,
            'student_class' => $classId,
            'status' => $this->status
        ]);

        // Cek kelas
        if ($this->class_id != $classId) {
            \Log::warning('Class mismatch', [
                'exam_id' => $this->id,
                'expected_class' => $this->class_id,
                'actual_class' => $classId
            ]);
            return false;
        }

        // Cek status
        if ($this->status !== 'active') {
            \Log::warning('Exam not active', [
                'exam_id' => $this->id,
                'status' => $this->status
            ]);
            return false;
        }

        // Cek waktu
        $now = now();

        if ($this->start_at && $now < $this->start_at) {
            \Log::info('Exam not started yet', [
                'exam_id' => $this->id,
                'start_at' => $this->start_at,
                'now' => $now
            ]);
            return false;
        }

        if ($this->end_at && $now > $this->end_at) {
            \Log::info('Exam already finished', [
                'exam_id' => $this->id,
                'end_at' => $this->end_at,
                'now' => $now
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get simple access status
     */
    public function getSimpleAccessStatus()
    {
        if ($this->status !== 'active') {
            return 'inactive';
        }

        $now = now();

        if ($this->start_at && $now < $this->start_at) {
            return 'not_started';
        }

        if ($this->end_at && $now > $this->end_at) {
            return 'finished';
        }

        return 'available';
    }
}
