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
        'remaining_time',
        'status',
        'ip_address',
        'user_agent',
        'violation_count',
        'violation_log',
        'score',
        'final_score',
        'is_cheating_detected',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'violation_log' => 'array',
        'violation_count' => 'integer',
        'score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'is_cheating_detected' => 'boolean',
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

    /* ================= METHODS ================= */

    public function calculateScore()
    {
        $totalScore = $this->answers()->sum('score');
        $totalPossibleScore = $this->exam->getTotalScore();

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
        $this->status = 'submitted';
        $this->save();
    }

    public function timeout()
    {
        $this->ended_at = now();
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

        // Jika violation count >= 3, tandai sebagai cheating
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
}
