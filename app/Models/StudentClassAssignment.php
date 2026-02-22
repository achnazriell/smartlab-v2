<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassAssignment extends Model
{
    protected $table = 'student_class_assignments';

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
