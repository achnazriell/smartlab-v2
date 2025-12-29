<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClass extends Model
{
    protected $fillable = [
        'classes_id',
        'user_id'
    ];

    // relasi ke kelas
    public function classes()
    {
        return $this->belongsTo(Classes::class, 'classes_id');
    }

    // relasi ke guru (user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
