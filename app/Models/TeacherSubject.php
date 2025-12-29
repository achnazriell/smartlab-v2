<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    protected $table = 'teacher_subjects';

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

