<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCodeHistory extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'student_code',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class);
    }
}
