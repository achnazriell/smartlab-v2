<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = [
        'name_subject'
    ];

    // Di model Subject.php
    public function teacherClassSubjects()
    {
        return $this->hasMany(TeacherClassSubject::class, 'subject_id');
    }

    public function classesThroughMateri()
    {
        return $this->hasManyThrough(
            Classes::class,
            Materi::class,
            'subject_id', // Foreign key on Materi table
            'id', // Foreign key on Classes table
            'id', // Local key on Subject table
            'class_id' // Local key on Materi table
        );
    }

    public function classesThroughTask()
    {
        return $this->hasManyThrough(
            Classes::class,
            Task::class,
            'subject_id', // Foreign key on Task table
            'id', // Foreign key on Classes table
            'id', // Local key on Subject table
            'class_id' // Local key on Task table
        );
    }

    public function Classes()
    {
        return $this->hasMany(Classes::class);
    }
    public function materi()
    {
        return $this->hasMany(Materi::class);
    }

    public function Task()
    {
        return $this->hasMany(Task::class);
    }
    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(
            \App\Models\Teacher::class,
            'teacher_subjects',
            'subject_id',   // FK ke subjects.id
            'teacher_id'    // FK ke teachers.id
        );
    }
}