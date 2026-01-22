<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';
    protected $guarded = ['id'];

    /**
     * ========================
     * RELASI GURU
     * ========================
     */

    // pivot utama guru-kelas
    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class, 'classes_id');
    }

    // akses Teacher langsung
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_classes',
            'classes_id',
            'teacher_id'
        );
    }

    // AKSES USER GURU (UNTUK VIEW LAMA)
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'teacher_classes',
            'classes_id',
            'teacher_id'
        );
    }

    /**
     * ========================
     * RELASI SISWA
     * ========================
     */

    // siswa dari tabel students
    public function studentList()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    // AKSES USER SISWA (jika diperlukan)
    public function students()
    {
        return $this->hasManyThrough(
            User::class,
            Student::class,
            'class_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * ========================
     * RELASI PEMBELAJARAN
     * ========================
     */

    public function tasks()
    {
        return $this->hasMany(Task::class, 'class_id');
    }

    public function materis()
    {
        return $this->belongsToMany(
            Materi::class,
            'materi_classes',
            'class_id',
            'materi_id'
        );
    }

    public function subjects()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
