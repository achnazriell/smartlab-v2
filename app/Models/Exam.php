<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Classes;
use App\Models\ExamQuestion;
use App\Models\ExamAttempt;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'title',
        'type',
        'duration',
        'start_at',
        'end_at',
        'shuffle_question',
        'shuffle_answer',
        'show_score',
        'allow_copy',
        'status',
    ];

    protected $casts = [
        'shuffle_question' => 'boolean',
        'shuffle_answer' => 'boolean',
        'show_score' => 'boolean',
        'allow_copy' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /* ================= RELATIONS ================= */

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

}
