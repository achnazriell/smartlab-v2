<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = [
        'classes_id',
        'subject_id',
        'title_materi',
        'file_materi',
        'description',
        'user_id'
    ];
    public function Task()
    {
        return $this->hasMany(Task::class);
    }
    public function subject()
    {
        return $this->belongsTo(subject::class, 'subject_id');
    }
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'materi_classes', 'materi_id', 'class_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
