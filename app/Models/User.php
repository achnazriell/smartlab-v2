<?php

namespace App\Models;

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

    /**
     * Relasi ke guru (jika user adalah guru)
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Relasi ke siswa (jika user adalah siswa)
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Relasi ke tugas yang dibuat (sebagai guru)
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    /**
     * Relasi ke materi yang diunggah
     */
    public function materis()
    {
        return $this->hasMany(Materi::class, 'user_id');
    }

    /**
     * Relasi ke pengumpulan tugas (sebagai siswa)
     */
    public function collections()
    {
        return $this->hasMany(Collection::class, 'user_id');
    }

    /**
     * Relasi ke sesi kuis sebagai guru
     */
    public function quizSessionsAsTeacher()
    {
        return $this->hasMany(QuizSession::class, 'teacher_id');
    }

    /**
     * Relasi ke partisipasi kuis sebagai siswa
     */
    public function quizParticipations()
    {
        return $this->hasMany(QuizParticipant::class, 'student_id');
    }

    /**
     * Relasi ke percobaan ujian sebagai siswa
     */
    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class, 'student_id');
    }

    /**
     * Boot: ketika status user diubah menjadi lulus, hapus penempatan kelas
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($user) {
            if ($user->isDirty('status') && $user->status === 'lulus') {
                // Hapus semua penempatan kelas siswa yang terkait
                if ($user->student) {
                    $user->student->classAssignments()->delete();
                }
            }
        });
    }
}
