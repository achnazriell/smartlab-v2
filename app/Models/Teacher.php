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

    public function exams()
    {
        // BENAR: hasMany dengan foreign key 'teacher_id'
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class);
    }

    // 🔹 RELASI KELAS (teacher_classes)
    public function classes()
    {
        return $this->belongsToMany(
            Classes::class,
            'teacher_classes',
            'teacher_id',
            relatedPivotKey: 'classes_id'
        );
    }

    // app/Models/Teacher.php
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects',
            'teacher_id',
            'subject_id'
        );
    }
}
