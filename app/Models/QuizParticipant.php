<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_session_id',
        'student_id',
        'exam_id',
        'status',
        'joined_at',
        'ready_at',
        'started_at',
        'submitted_at',
        'ip_address',
        'user_agent',
        'is_present',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'ready_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_present' => 'boolean',
    ];

    // PERBAIKAN RELATIONSHIP - Pastikan nama kolom benar
    public function quizSession()
    {
        return $this->belongsTo(QuizSession::class, 'quiz_session_id');
    }

    // PERBAIKAN: Relationship dengan User (bukan Student model terpisah)
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function attempt()
    {
        return $this->hasOne(ExamAttempt::class, 'student_id', 'student_id')
            ->where('exam_id', $this->exam_id)
            ->latest();
    }

    /**
     * Bergabung ke ruangan
     */
    public function joinRoom($ipAddress = null, $userAgent = null)
    {
        $this->update([
            'status' => 'waiting',
            'joined_at' => now(),
            'is_present' => true,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Update session stats
        if ($this->quizSession) {
            $this->quizSession->updateStats();
        }

        return $this;
    }

    /**
     * Tandai sebagai siap
     */
    public function markAsReady()
    {
        $this->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);

        // Update session stats
        if ($this->quizSession) {
            $this->quizSession->updateStats();
        }

        return $this;
    }

    /**
     * Mulai quiz
     */
    public function startQuiz()
    {
        $this->update([
            'status' => 'started',
            'started_at' => now(),
        ]);

        // Update session stats
        if ($this->quizSession) {
            $this->quizSession->updateStats();
        }

        return $this;
    }

    /**
     * Submit quiz
     */
    public function submitQuiz()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Update session stats
        if ($this->quizSession) {
            $this->quizSession->updateStats();
        }

        return $this;
    }

    /**
     * Keluarkan dari ruangan
     */
    public function kick()
    {
        $this->delete();

        // Update session stats
        if ($this->quizSession) {
            $this->quizSession->updateStats();
        }

        return true;
    }

    public function logViolation($type, $details = null)
    {
        $log = $this->violation_log ?? [];
        $log[] = [
            'type' => $type,
            'details' => $details,
            'timestamp' => now()->toDateTimeString(),
        ];
        $this->violation_count = ($this->violation_count ?? 0) + 1;
        $this->violation_log = $log;
        $this->save();
    }

    // Helper methods
    public function isWaiting()
    {
        return $this->status === 'waiting';
    }

    public function isReady()
    {
        return $this->status === 'ready';
    }

    public function isStarted()
    {
        return $this->status === 'started';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isDisconnected()
    {
        return $this->status === 'disconnected';
    }
}
