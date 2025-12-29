<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects', // pivot table
            'teacher_id',
            'subject_id'
        );
    }

    public function class()
    {
        return $this->belongsToMany(Classes::class, 'teacher_classes', 'teacher_id', 'classes_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'teacher_classes', 'teacher_id', 'classes_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($user) {
            if ($user->created_at) {
                $createDate = $user->created_at;
                if ($createDate->lte(now()->subYear(1))) {
                    $user->class()->detach();
                }
                if ($createDate->lte(now()->subYears(2))) {
                    $user->class()->detach();
                }
            }
        });

        static::updated(function ($user) {
            if ($user->isDirty('status') && $user->status === 'lulus') {
                $user->class()->detach();
            }
        });
    }
}
