<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'teacher_id',
        'session_code',
        'session_status',
        'session_started_at',
        'session_ended_at',
        'total_duration',
        'total_students',
        'students_joined',
        'students_submitted',
    ];

    protected $casts = [
        'session_started_at' => 'datetime',
        'session_ended_at' => 'datetime',
    ];

    protected $appends = [
        'is_active',
        'is_waiting',
        'is_finished',
        'formatted_started_at',
        'formatted_ended_at',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function participants()
    {
        return $this->hasMany(QuizParticipant::class, 'quiz_session_id');
    }

    /**
     * Generate a unique session code
     */
    public static function generateSessionCode()
    {
        $code = null;
        $attempts = 0;

        do {
            // Generate 6 character alphanumeric code
            $code = strtoupper(Str::random(6));
            $attempts++;

            // Check if code already exists
            $exists = self::where('session_code', $code)->exists();

            // Safety check to prevent infinite loop
            if ($attempts > 10) {
                // If can't generate unique code in 10 attempts, use timestamp
                $code = strtoupper(Str::random(4) . rand(10, 99));
                break;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Create a new quiz session for an exam
     */
    public static function createForExam($examId, $teacherId)
    {
        $sessionCode = self::generateSessionCode();

        return self::create([
            'exam_id' => $examId,
            'teacher_id' => $teacherId,
            'session_code' => $sessionCode,
            'session_status' => 'waiting',
            'session_started_at' => now(),
        ]);
    }

    /**
     * Update session statistics
     */
    public function updateStats()
    {
        // Hitung stats
        $total = $this->participants()->count();
        $joined = $this->participants()->where('is_present', true)->count();
        $ready = $this->participants()->where('status', 'ready')->count();
        $started = $this->participants()->where('status', 'started')->count();

        // Update jika perlu
        $this->save();

        return compact('total', 'joined', 'ready', 'started');
    }

    /**
     * Get active participants
     */
    public function getActiveParticipants()
    {
        return $this->participants()
            ->where('status', '!=', 'disconnected')
            ->where('is_present', true)
            ->get();
    }

    /**
     * Get waiting participants (not yet ready)
     */
    public function getWaitingParticipants()
    {
        return $this->participants()
            ->where('status', 'waiting')
            ->where('is_present', true)
            ->get();
    }

    /**
     * Get ready participants
     */
    public function getReadyParticipants()
    {
        return $this->participants()
            ->where('status', 'ready')
            ->where('is_present', true)
            ->get();
    }

    /**
     * Get started participants
     */
    public function getStartedParticipants()
    {
        return $this->participants()
            ->where('status', 'started')
            ->where('is_present', true)
            ->get();
    }

    /**
     * Get submitted participants
     */
    public function getSubmittedParticipants()
    {
        return $this->participants()
            ->where('status', 'submitted')
            ->get();
    }

    /**
     * Check if session can be started (minimal 1 participant ready)
     */
    public function canStart()
    {
        return $this->getReadyParticipants()->count() > 0;
    }

    /**
     * Start the quiz session
     */
    public function startSession($totalDuration = null)
    {
        if (!$this->canStart()) {
            return false;
        }

        $this->update([
            'session_status' => 'active',
            'total_duration' => $totalDuration ?? $this->total_duration,
        ]);

        // Update all ready participants to started
        $this->participants()
            ->where('status', 'ready')
            ->update([
                'status' => 'started',
                'started_at' => now(),
            ]);

        return true;
    }

    /**
     * Finish the quiz session
     */
    public function finishSession()
    {
        $this->update([
            'session_status' => 'finished',
            'session_ended_at' => now(),
        ]);

        // Update all active participants to submitted
        $this->participants()
            ->whereIn('status', ['waiting', 'ready', 'started'])
            ->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

        return true;
    }

    /**
     * Check if session is active
     */
    public function getIsActiveAttribute()
    {
        return $this->session_status === 'active';
    }

    /**
     * Check if session is waiting
     */
    public function getIsWaitingAttribute()
    {
        return $this->session_status === 'waiting';
    }

    /**
     * Check if session is finished
     */
    public function getIsFinishedAttribute()
    {
        return $this->session_status === 'finished';
    }

    /**
     * Get formatted started at time
     */
    public function getFormattedStartedAtAttribute()
    {
        return $this->session_started_at
            ? $this->session_started_at->format('H:i')
            : null;
    }

    /**
     * Get formatted ended at time
     */
    public function getFormattedEndedAtAttribute()
    {
        return $this->session_ended_at
            ? $this->session_ended_at->format('H:i')
            : null;
    }

    /**
     * Get remaining time for active session
     */
    public function getRemainingTime()
    {
        if (!$this->is_active || !$this->total_duration || !$this->session_started_at) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->session_started_at);
        $remaining = max(0, $this->total_duration - $elapsed);

        return $remaining;
    }

    /**
     * Get formatted remaining time
     */
    public function getFormattedRemainingTime()
    {
        $seconds = $this->getRemainingTime();

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
}
