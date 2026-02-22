<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'title_materi',
        'file_materi',
        'description',
    ];

    /**
     * Relasi ke user pengunggah
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke mata pelajaran
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relasi ke kelas
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Relasi ke tahun ajaran
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relasi many-to-many ke kelas (jika menggunakan tabel pivot)
     */
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'materi_classes', 'materi_id', 'class_id');
    }

    /**
     * Relasi ke tugas yang menggunakan materi ini
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'materi_id');
    }
}
