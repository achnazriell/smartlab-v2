<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ✅ RELASI UTAMA
    public function classes()
    {
        return $this->belongsToMany(
            Classes::class,
            'teacher_classes',
            'teacher_id',
            'classes_id'
        );
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // fitur lain
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            if ($user->isDirty('status') && $user->status === 'lulus') {
                optional($user->teacher)->teacherClasses()->delete();
            }
        });
    }
}
