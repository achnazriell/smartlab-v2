<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /**
     * Relasi ke penempatan siswa di kelas (riwayat)
     */
    public function studentAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class);
    }

    /**
     * Relasi ke penempatan guru mengajar
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    /**
     * Scope untuk mengambil tahun ajaran yang sedang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
