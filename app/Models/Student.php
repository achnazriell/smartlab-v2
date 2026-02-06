<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nis',
        'class_id',
        'status',
        'graduation_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // Relasi ke ExamAttempt
    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    // Atau mungkin nama relasinya berbeda
    public function userRelation()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Atau coba dengan alias lain
    public function userAccount()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
