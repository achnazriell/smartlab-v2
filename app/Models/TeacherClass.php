<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    protected $fillable = [
        'classes_id',
        'teacher_id'
    ];

    // relasi ke kelas
    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id');
    }

    // relasi ke guru (user)
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }


    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_class_subjects',
            'teacher_class_id',
            'subject_id'
        );
    }
}
