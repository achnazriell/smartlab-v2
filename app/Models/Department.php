<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Relasi ke kelas-kelas yang memiliki jurusan ini
     */
    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    /**
     * Mendapatkan jumlah kelas berdasarkan jurusan
     */
    public function getClassCountAttribute()
    {
        return $this->classes()->count();
    }

    /**
     * Mendapatkan jumlah siswa berdasarkan jurusan (melalui kelas di tahun aktif)
     */
    public function getStudentCountAttribute()
    {
        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            return 0;
        }

        return StudentClassAssignment::whereHas('class', function ($q) {
            $q->where('department_id', $this->id);
        })->where('academic_year_id', $activeYear->id)
            ->distinct('student_id')
            ->count('student_id');
    }
}
