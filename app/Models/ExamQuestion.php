<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'type',
        'question',
        'score',
        'explanation',
        'short_answers',
        'enable_timer',
        'time_limit',
        'enable_skip',
        'show_explanation',
        'enable_mark_review',
        'randomize_choices',
        'require_all_options',
    ];

    protected $casts = [
        'short_answers' => 'array',
    ];

    public function getQuestionSettingsForStudent()
    {
        return [
            'enable_timer' => $this->enable_timer ?? false,
            'time_limit' => $this->time_limit,
            'enable_skip' => $this->enable_skip ?? true,
            'show_explanation' => $this->show_explanation ?? false,
            'enable_mark_review' => $this->enable_mark_review ?? true,
            'randomize_choices' => $this->randomize_choices ?? false,
            'require_all_options' => $this->require_all_options ?? false,
        ];
    }

    public function shouldRandomizeChoices()
    {
        return $this->randomize_choices ?? false;
    }

    public function canSkip()
    {
        return $this->enable_skip ?? true;
    }

    public function showExplanation()
    {
        return $this->show_explanation ?? false;
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // PERBAIKAN: Tentukan foreign key secara eksplisit
    public function choices()
    {
        return $this->hasMany(ExamChoice::class, 'question_id'); // Tambahkan parameter kedua
    }

    // Accessor untuk mendapatkan jawaban benar
    public function getCorrectAnswerAttribute()
    {
        if ($this->type === 'PG') {
            $correct = $this->choices()->where('is_correct', true)->first();
            return $correct ? [
                'label' => $correct->label,
                'text' => $correct->text,
                'index' => $this->choices->search(function ($item) use ($correct) {
                    return $item->id === $correct->id;
                })
            ] : null;
        } elseif ($this->type === 'IS') {
            return $this->short_answers ?? [];
        }
        return null;
    }

    // PERBAIKAN: Tambahkan parameter untuk orderBy
    public function getChoicesAttribute()
    {
        return $this->choices()->orderBy('order')->get();
    }

    public function getShortAnswersAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    public function setShortAnswersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['short_answers'] = json_encode($value);
        } else {
            $this->attributes['short_answers'] = $value;
        }
    }

    public function getRandomShortAnswers($count = 3)
    {
        if ($this->type !== 'IS' || empty($this->short_answers)) {
            return [];
        }

        $answers = $this->short_answers;
        $randomAnswers = [];

        if (count($answers) <= $count) {
            return $answers;
        }

        $randomKeys = array_rand($answers, $count);
        if (!is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        foreach ($randomKeys as $key) {
            $randomAnswers[] = $answers[$key];
        }

        return $randomAnswers;
    }

    // Accessor untuk question_text (jika diperlukan)
    public function getQuestionTextAttribute()
    {
        return $this->question; // 'question' adalah kolom di database
    }

    // Accessor untuk options
    public function getOptionsAttribute()
    {
        if ($this->type === 'PG') {
            return $this->choices->mapWithKeys(function ($choice) {
                return [$choice->id => $choice->text];
            })->toArray();
        }

        return [];
    }
}
