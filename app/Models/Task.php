<?php

namespace App\Models;

use App\Models\Classroom;
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
        'date_collection'
    ];

    protected $casts = [
        'date_collection' => 'datetime',
    ];

    public function classes()
    {
        return $this->belongsToMany(
            Classroom::class,
            'class_task',
            'task_id',
            'class_id'
        );
    }


    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public function Materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }
    public function Assessment()
    {
        return $this->hasMany(Assessment::class);
    }
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
    public function assessments()
    {
        return $this->hasManyThrough(Assessment::class, Collection::class, 'task_id', 'collection_id');
    }
}
