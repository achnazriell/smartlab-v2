<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassSubject extends Model
{
    protected $fillable = [
        'teacher_class_id',
        'subject_id',
    ];

    public function teacherClass()
    {
        return $this->belongsTo(TeacherClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

