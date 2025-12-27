<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = [
        'name_subject'
    ];

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

}
