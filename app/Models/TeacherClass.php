<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    protected $fillable = [
        'classes_id',
        'user_id'
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'classes_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'class_user', 'class_id', 'user_id');
    }

    public function teacher()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'classes_id', 'teacher_id')
            ->withPivot('classes_id');
    }

}
