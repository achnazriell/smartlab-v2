<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'ended_at',
        'submitted_at',
        'remaining_time',
        'status',
        'ip_address',
        'user_agent',
        'violation_count',
        'violation_log',
        'score',
        'final_score',
        'is_cheating_detected',
        'exam_settings',
        'quiz_session_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'submitted_at' => 'datetime',

        'score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'is_cheating_detected' => 'boolean',
        'remaining_time' => 'integer',

        'exam_settings' => 'array',
        'violation_log' => 'array',

    ];

    protected $attributes = [
        'exam_settings' => '{}',
        'violation_log' => '[]',
        'violation_count' => 0,
        'score' => 0,
        'final_score' => 0,
        'is_cheating_detected' => false,
        'remaining_time' => 0,
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'attempt_id');
    }

    public function quizSession()
    {
        return $this->belongsTo(QuizSession::class, 'quiz_session_id');
    }

    /* ================= METHODS ================= */

    /**
     * ✅ FIX: Ganti getTotalScore() dengan total_score (accessor)
     */
    public function calculateScore()
    {
        $totalScore = $this->answers()->sum('score');
        $totalPossibleScore = $this->exam->total_score; // ✅ perbaikan di sini

        $finalScore = $totalPossibleScore > 0 ? ($totalScore / $totalPossibleScore) * 100 : 0;

        $this->score = $totalScore;
        $this->final_score = $finalScore;
        $this->save();

        return $finalScore;
    }

    public function submit()
    {
        $this->calculateScore();
        $this->ended_at = now();
        $this->submitted_at = now();
        $this->status = 'submitted';
        $this->save();
    }

    public function timeout()
    {
        $this->ended_at = now();
        $this->submitted_at = now();
        $this->status = 'timeout';
        $this->calculateScore();
        $this->save();
    }

    public function logViolation($type, $details = null)
    {
        $log = $this->violation_log ?? [];
        $log[] = [
            'type' => $type,
            'details' => $details,
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip()
        ];

        $this->violation_count = $this->violation_count + 1;
        $this->violation_log = $log;

        if ($this->violation_count >= 3) {
            $this->is_cheating_detected = true;
        }

        $this->save();
    }

    public function getViolationTypes()
    {
        return [
            'tab_switch' => 'Beralih ke tab lain',
            'fullscreen_exit' => 'Keluar dari mode layar penuh',
            'copy_paste' => 'Melakukan copy-paste',
            'screenshot' => 'Mengambil screenshot',
            'multiple_windows' => 'Membuka window baru',
            'no_camera' => 'Kamera tidak aktif',
            'no_mic' => 'Mikrofon tidak aktif',
            'suspicious_movement' => 'Pergerakan mencurigakan',
        ];
    }

    public function getTimeElapsed()
    {
        if (!$this->started_at) return 0;

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    public function getTimeRemaining()
    {
        if (!$this->started_at) return 0;

        $totalTime = $this->exam->duration * 60;
        $elapsed = $this->getTimeElapsed();

        return max(0, $totalTime - $elapsed);
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isTimeout()
    {
        return $this->status === 'timeout';
    }

    public function getDefaultExamSettings()
    {
        return [
            'quiz_title' => $this->exam->title ?? '',
            'quiz_mode' => $this->exam->quiz_mode ?? 'normal',
            'difficulty_level' => $this->exam->difficulty_level ?? 'medium',
            'enable_powerups' => (bool) ($this->exam->enable_powerups ?? false),
            'enable_music' => (bool) ($this->exam->enable_music ?? false),
            'enable_memes' => (bool) ($this->exam->enable_memes ?? false),
            'shuffle_question' => (bool) ($this->exam->shuffle_question ?? false),
            'shuffle_answer' => (bool) ($this->exam->shuffle_answer ?? false),
            'show_explanation' => (bool) ($this->exam->show_explanation ?? false),
            'show_leaderboard' => (bool) ($this->exam->show_leaderboard ?? false),
            'instant_feedback' => (bool) ($this->exam->instant_feedback ?? false),
            'streak_bonus' => (bool) ($this->exam->streak_bonus ?? false),
            'time_bonus' => (bool) ($this->exam->time_bonus ?? false),
            'time_per_question' => (int) ($this->exam->time_per_question ?? 30),
            'total_questions' => $this->exam->questions()->count() ?? 0,
            'fullscreen_mode' => (bool) ($this->exam->fullscreen_mode ?? false),
            'block_new_tab' => (bool) ($this->exam->block_new_tab ?? false),
            'prevent_copy_paste' => (bool) ($this->exam->prevent_copy_paste ?? false),
            'disable_violations' => (bool) ($this->exam->disable_violations ?? false),
            'violation_limit' => (int) ($this->exam->violation_limit ?? 3),
            'enable_proctoring' => (bool) ($this->exam->enable_proctoring ?? false),
            'require_camera' => (bool) ($this->exam->require_camera ?? false),
            'require_mic' => (bool) ($this->exam->require_mic ?? false),
            'quiz_stats' => [
                'streak_count' => 0,
                'time_spent' => 0,
                'bonus_points' => 0,
                'time_bonus' => 0,
                'streak_bonus' => 0,
            ],
            'powerups_used' => [],
            'bonuses_claimed' => [],
            'progress' => [
                'current_question' => 0,
                'last_saved' => null,
                'answers' => [],
            ]
        ];
    }

    public static function createWithDefaults(array $attributes = [])
    {
        $defaults = [
            'exam_settings' => '{}',
            'violation_log' => '[]',
            'violation_count' => 0,
            'score' => 0,
            'final_score' => 0,
            'is_cheating_detected' => false,
            'remaining_time' => 0,
            'status' => 'in_progress',
            'submitted_at' => null,
        ];

        return static::create(array_merge($defaults, $attributes));
    }

    public function addPowerupUsage($type, $data = [])
    {
        $settings = $this->exam_settings ?? [];
        $powerups = $settings['powerups_used'] ?? [];
        $powerups[] = array_merge([
            'type' => $type,
            'used_at' => now()->toDateTimeString(),
        ], $data);
        $settings['powerups_used'] = $powerups;
        $this->exam_settings = $settings;
        $this->save();
    }

    public function addBonus($type, $points)
    {
        $settings = $this->exam_settings ?? [];
        $bonuses = $settings['bonuses_claimed'] ?? [];
        $bonuses[] = [
            'type' => $type,
            'points' => $points,
            'claimed_at' => now()->toDateTimeString(),
        ];
        $settings['bonuses_claimed'] = $bonuses;
        $this->exam_settings = $settings;
        $this->save();
    }

    public function getActiveMultiplier()
    {
        $settings = $this->exam_settings ?? [];
        $active = $settings['active_multiplier'] ?? null;
        if ($active && now()->lt($active['expires_at'])) {
            return $active['value'];
        }
        return 1;
    }

    public function setActiveMultiplier($multiplier, $durationSeconds)
    {
        $settings = $this->exam_settings;
        $settings['active_multiplier'] = [
            'value' => $multiplier,
            'expires_at' => now()->addSeconds($durationSeconds)->timestamp
        ];
        $this->exam_settings = $settings;
        $this->save();
    }
}
