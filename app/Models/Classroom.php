<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'classes'; // PENTING kalau nama tabel "classes"

    protected $fillable = [
        'name_class',
        'description',
    ];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'class_task');
    }

    public function teacherClasses()
    {
        return $this->hasMany(TeacherClass::class);
    }
}
