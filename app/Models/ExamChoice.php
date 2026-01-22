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
        'image_path',
        'order',
    ];

    // PERBAIKAN: Tambahkan timestamps karena di database ada created_at dan updated_at
    public $timestamps = true;

    protected $casts = [
        'is_correct' => 'boolean',
        'order' => 'integer',
    ];

    // PERBAIKAN: Tentukan foreign key dan local key
    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'choice_id');
    }

    public function getChoiceLabel()
    {
        $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
        return $labels[$this->order] ?? $this->label; // Perbaiki index (order dimulai dari 0)
    }

    public function hasImage()
    {
        return !empty($this->image_path);
    }

    public function getImageUrl()
    {
        if ($this->hasImage()) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
