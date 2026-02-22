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
        'students_ready',
        'students_started',
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
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
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
     * Update session statistics
     */
    public function updateStats()
    {
        // Hitung stats dari participants
        $total = $this->participants()->count();
        $joined = $this->participants()->where('is_present', true)->count();
        $ready = $this->participants()->where('status', 'ready')->count();
        $started = $this->participants()->where('status', 'started')->count();
        $submitted = $this->participants()->where('status', 'submitted')->count();

        // Update session
        $this->update([
            'students_joined' => $joined,
            'students_ready' => $ready,
            'students_started' => $started,
            'students_submitted' => $submitted,
        ]);

        return [
            'total' => $total,
            'joined' => $joined,
            'ready' => $ready,
            'started' => $started,
            'submitted' => $submitted
        ];
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
     * Start the quiz session
     */
    public function startSession()
    {
        $this->update([
            'session_status' => 'active',
            'session_started_at' => now(),
        ]);

        // Update ready participants to started
        $this->participants()
            ->where('status', 'ready')
            ->update([
                'status' => 'started',
                'started_at' => now(),
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
}
