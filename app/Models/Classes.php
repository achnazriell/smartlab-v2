<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'classes_id', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'classes_id', 'teacher_id');
    }

    public function Task()
    {
        return $this->belongsTo(Task::class);
    }
    public function materis()
    {
        return $this->belongsToMany(Materi::class, 'materi_classes', 'class_id', 'materi_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_classes', 'classes_id', 'teacher_id');
    }
    public function students()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'classes_id', 'user_id');
    }

    public function studentList()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

}
