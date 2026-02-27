<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    /**
     * Tipe soal yang didukung:
     * PG  = Pilihan Ganda (1 jawaban benar)
     * PGK = Pilihan Ganda Kompleks (beberapa jawaban benar)
     * BS  = Benar/Salah
     * DD  = Dropdown (1 jawaban benar, tampilan dropdown)
     * IS  = Isian Singkat
     * ES  = Esai (dinilai manual)
     * SK  = Skala Linear
     * MJ  = Menjodohkan (matching)
     */
    public const VALID_TYPES = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];

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
        'order',
        'image_path',
    ];

    protected $casts = [
        'score'              => 'integer',
        'enable_timer'       => 'boolean',
        'enable_skip'        => 'boolean',
        'show_explanation'   => 'boolean',
        'enable_mark_review' => 'boolean',
        'randomize_choices'  => 'boolean',
        'require_all_options'=> 'boolean',
        'order'              => 'integer',
        // short_answers TIDAK di-cast 'array' di sini karena kita handle lewat accessor/mutator
    ];

    /* ===================== RELATIONSHIPS ===================== */

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function choices()
    {
        return $this->hasMany(ExamChoice::class, 'question_id')->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'question_id');
    }

    /* ===================== ACCESSORS / MUTATORS ===================== */

    /**
     * Selalu kembalikan array dari short_answers,
     * baik disimpan sebagai JSON string maupun sudah array.
     */
    public function getShortAnswersAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return json_decode($value, true) ?? [];
    }

    /**
     * Simpan short_answers sebagai JSON string.
     */
    public function setShortAnswersAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['short_answers'] = null;
        } elseif (is_array($value)) {
            $this->attributes['short_answers'] = json_encode($value);
        } else {
            // Sudah JSON string
            $this->attributes['short_answers'] = $value;
        }
    }

    /* ===================== COMPUTED ATTRIBUTES ===================== */

    /**
     * Jawaban benar berdasarkan tipe soal.
     */
    public function getCorrectAnswerAttribute()
    {
        switch ($this->type) {
            case 'PG':
            case 'DD':
                $correct = $this->choices()->where('is_correct', true)->first();
                return $correct ? [
                    'label' => $correct->label,
                    'text'  => $correct->text,
                    'index' => $correct->order,
                ] : null;

            case 'PGK':
                return $this->choices()->where('is_correct', true)->get()->map(fn($c) => [
                    'label' => $c->label,
                    'text'  => $c->text,
                    'index' => $c->order,
                ])->toArray();

            case 'BS':
            case 'IS':
            case 'ES':
            case 'SK':
            case 'MJ':
                return $this->short_answers;

            default:
                return null;
        }
    }

    /**
     * Alias untuk field question.
     */
    public function getQuestionTextAttribute()
    {
        return $this->question;
    }

    /* ===================== HELPERS ===================== */

    public function isMultipleChoice(): bool
    {
        return in_array($this->type, ['PG', 'PGK', 'DD']);
    }

    public function isShortAnswer(): bool
    {
        return $this->type === 'IS';
    }

    public function isEssay(): bool
    {
        return $this->type === 'ES';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'BS';
    }

    /* ===================== BOOT ===================== */

    /**
     * CATATAN: Boot event validasi IS dihapus karena menyebabkan
     * masalah saat saving secara bertahap. Validasi sudah dilakukan
     * di QuestionController sebelum menyimpan.
     */
    protected static function booted()
    {
        // Tidak ada validasi ketat di sini agar controller lebih fleksibel.
    }
}
