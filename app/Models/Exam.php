<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id', // Ini seharusnya merujuk ke id di tabel teachers
        'class_id',
        'subject_id',
        'title',
        'type',
        'duration',
        'time_per_question',
        'quiz_mode',
        // Kolom quiz
        'show_leaderboard',
        'enable_music',
        'enable_memes',
        'enable_powerups',
        'randomize_questions',
        'instant_feedback',
        'streak_bonus',
        'time_bonus',
        'difficulty_level',
        // Waktu ujian
        'start_at',
        'end_at',
        // Pengaturan dasar
        'shuffle_question',
        'shuffle_answer',
        'show_score',
        'allow_copy',
        'allow_screenshot',
        // Pengaturan keamanan
        'require_camera',
        'require_mic',
        'enable_proctoring',
        'block_new_tab',
        'fullscreen_mode',
        'auto_submit',
        'prevent_copy_paste',
        'limit_attempts',
        'min_pass_grade',
        'show_correct_answer',
        'show_result_after',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        // Boolean casts
        'shuffle_question' => 'boolean',
        'shuffle_answer' => 'boolean',
        'show_score' => 'boolean',
        'allow_copy' => 'boolean',
        'allow_screenshot' => 'boolean',
        'require_camera' => 'boolean',
        'require_mic' => 'boolean',
        'enable_proctoring' => 'boolean',
        'block_new_tab' => 'boolean',
        'fullscreen_mode' => 'boolean',
        'auto_submit' => 'boolean',
        'prevent_copy_paste' => 'boolean',
        'show_correct_answer' => 'boolean',
        'show_leaderboard' => 'boolean',
        'enable_music' => 'boolean',
        'enable_memes' => 'boolean',
        'enable_powerups' => 'boolean',
        'randomize_questions' => 'boolean',
        'instant_feedback' => 'boolean',
        'streak_bonus' => 'boolean',
        'time_bonus' => 'boolean',
        // Numeric casts
        'limit_attempts' => 'integer',
        'min_pass_grade' => 'decimal:2',
        'duration' => 'integer',
        'time_per_question' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /* ================= RELATIONS ================= */

    // PERBAIKAN: teacher() harus merujuk ke Teacher::class, bukan User::class
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // Untuk kompatibilitas dengan kode lama yang mungkin masih menggunakan creator()
    public function creator()
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
        return $this->hasMany(ExamQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    /* ================= ATTRIBUTE ACCESSORS ================= */

    // Untuk kompatibilitas dengan form yang menggunakan start_date/end_date
    public function getStartDateAttribute()
    {
        return $this->start_at;
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_at'] = $value;
    }

    public function getEndDateAttribute()
    {
        return $this->end_at;
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_at'] = $value;
    }

    /* ================= METHODS ================= */

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isQuiz()
    {
        return $this->type === 'QUIZ';
    }

    public function getTotalQuestions()
    {
        return $this->questions()->count();
    }

    public function getTotalScore()
    {
        return $this->questions()->sum('score');
    }

    public function getAllSettings()
    {
        return [
            // Security settings
            'require_camera' => (bool) $this->require_camera,
            'require_mic' => (bool) $this->require_mic,
            'enable_proctoring' => (bool) $this->enable_proctoring,
            'block_new_tab' => (bool) $this->block_new_tab,
            'fullscreen_mode' => (bool) $this->fullscreen_mode,
            'prevent_copy_paste' => (bool) $this->prevent_copy_paste,
            'allow_screenshot' => (bool) $this->allow_screenshot,
            'allow_copy' => (bool) $this->allow_copy,
            'shuffle_question' => (bool) $this->shuffle_question,
            'shuffle_answer' => (bool) $this->shuffle_answer,
            'limit_attempts' => (int) $this->limit_attempts,
            'violation_limit' => (int) ($this->violation_limit ?? 3),

            // Basic settings
            'show_score' => (bool) $this->show_score,
            'show_correct_answer' => (bool) $this->show_correct_answer,
            'show_result_after' => $this->show_result_after,
            'auto_submit' => (bool) $this->auto_submit,
            'min_pass_grade' => (float) $this->min_pass_grade,

            // Timing settings
            'duration' => (int) $this->duration,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,

            // Exam info
            'exam_title' => $this->title,
            'exam_type' => $this->type,
            'subject_name' => optional($this->subject)->name_subject ?? '',
            'class_name' => optional($this->class)->name_class ?? '',
        ];
    }


    public function getQuizSettings()
    {
        if (!$this->isQuiz()) {
            return null;
        }

        return [
            'time_per_question' => $this->time_per_question,
            'quiz_mode' => $this->quiz_mode,
            'show_leaderboard' => $this->show_leaderboard,
            'enable_music' => $this->enable_music,
            'enable_memes' => $this->enable_memes,
            'enable_powerups' => $this->enable_powerups,
            'randomize_questions' => $this->randomize_questions,
            'instant_feedback' => $this->instant_feedback,
            'streak_bonus' => $this->streak_bonus,
            'time_bonus' => $this->time_bonus,
            'difficulty_level' => $this->difficulty_level,
        ];
    }

    // Cek apakah ujian sedang berlangsung
    public function isOngoing()
    {
        $now = now();
        return $this->status === 'active'
            && $this->start_at <= $now
            && $this->end_at >= $now;
    }

    // Cek apakah ujian sudah selesai
    public function isFinished()
    {
        return $this->status === 'finished' || now() > $this->end_at;
    }

    // Cek apakah bisa diedit
    public function canBeEdited()
    {
        return $this->isDraft() || ($this->isActive() && now() < $this->start_at);
    }
}
