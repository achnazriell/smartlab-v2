<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'NIP',
    ];

    // 🔹 RELASI KE USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 RELASI MAPEL (teacher_subjects)
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects',
            'teacher_id',
            'subject_id'
        );
    }

    // 🔹 RELASI KELAS (teacher_classes)
    public function classes()
    {
        return $this->belongsToMany(
            Classes::class,
            'teacher_classes',
            'teacher_id',
            'classes_id'
        );
    }
}
