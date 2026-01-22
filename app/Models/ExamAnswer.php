<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_id',
        'question_id',
        'student_id',
        'attempt_id',
        'choice_id',
        'answer_text',
        'score',
        'is_correct',
        'time_taken',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    public function choice()
    {
        return $this->belongsTo(ExamChoice::class, 'choice_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    /* ================= METHODS ================= */

    public function calculateScore()
    {
        if ($this->question->type === 'multiple_choice') {
            $correctChoice = $this->question->choices()
                ->where('is_correct', true)
                ->first();

            if ($correctChoice && $this->choice_id == $correctChoice->id) {
                $this->score = $this->question->score;
                $this->is_correct = true;
            } else {
                $this->score = 0;
                $this->is_correct = false;
            }
        } elseif ($this->question->type === 'essay') {
            // Untuk essay, skor default 0 sampai diperiksa guru
            $this->score = $this->score ?? 0;
            $this->is_correct = $this->score > 0;
        }

        $this->save();
        return $this->score;
    }

    public function isMultipleChoice()
    {
        return $this->question->type === 'multiple_choice';
    }

    public function isEssay()
    {
        return $this->question->type === 'essay';
    }

    public function getAnswerText()
    {
        if ($this->isMultipleChoice() && $this->choice) {
            return $this->choice->text;
        }

        return $this->answer_text;
    }
}
