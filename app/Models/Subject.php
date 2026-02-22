<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = ['name_subject'];

    /**
     * Relasi ke penempatan guru mengajar
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    /**
     * Guru yang mengajar mata pelajaran ini (tahun ajaran aktif)
     */
    public function currentTeachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_subject_assignments',
            'subject_id',
            'teacher_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                  ->from('academic_years')
                  ->where('is_active', true)
                  ->limit(1);
        })->withPivot('class_id')->withTimestamps();
    }

    /**
     * Kelas di mana mata pelajaran ini diajarkan (tahun ajaran aktif)
     */
    public function currentClasses()
    {
        return $this->belongsToMany(
            Classes::class,
            'teacher_subject_assignments',
            'subject_id',
            'class_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                  ->from('academic_years')
                  ->where('is_active', true)
                  ->limit(1);
        })->withPivot('teacher_id')->withTimestamps();
    }

    /**
     * Relasi ke materi
     */
    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    /**
     * Relasi ke tugas
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relasi ke ujian
     */
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
