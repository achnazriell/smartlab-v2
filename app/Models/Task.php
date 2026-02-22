<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'materi_id',
        'user_id',
        'title_task',
        'file_task',
        'description_task',
        'date_collection',
        'academic_year_id', // tambahan
    ];

    protected $casts = [
        'date_collection' => 'datetime',
    ];

    /**
     * Relasi ke kelas (langsung)
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
     * Relasi ke mata pelajaran
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relasi ke materi (jika terkait)
     */
    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    /**
     * Relasi ke user pembuat (guru)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke pengumpulan tugas
     */
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Relasi ke penilaian melalui collection
     */
    public function assessments()
    {
        return $this->hasManyThrough(Assessment::class, Collection::class, 'task_id', 'collection_id');
    }

    /**
     * Relasi many-to-many ke kelas (jika menggunakan pivot class_task)
     */
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_task', 'task_id', 'class_id');
    }
}
