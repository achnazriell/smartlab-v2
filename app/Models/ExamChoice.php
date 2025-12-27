<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamChoice extends Model
{
    protected $fillable = [
        'question_id',
        'label',
        'text',
        'is_correct',
    ];

    public $timestamps = false;

    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
