<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke teacher_subject_assignments (mengajar)
    public function assignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    // Ambil mata pelajaran yang diajar melalui assignments (dengan tahun ajaran tertentu)
    public function subjectsTaughtInAcademicYear($academicYearId = null)
    {
        $query = $this->belongsToMany(
            Subject::class,
            'teacher_subject_assignments',
            'teacher_id',
            'subject_id'
        )->withPivot('class_id', 'academic_year_id');

        if ($academicYearId) {
            $query->wherePivot('academic_year_id', $academicYearId);
        }

        return $query;
    }

    // Relasi ke kelas melalui assignments
    public function classesTaughtInAcademicYear($academicYearId = null)
    {
        $query = $this->belongsToMany(
            Classes::class,
            'teacher_subject_assignments',
            'teacher_id',
            'class_id'
        )->withPivot('subject_id', 'academic_year_id');

        if ($academicYearId) {
            $query->wherePivot('academic_year_id', $academicYearId);
        }

        return $query;
    }

    
    // (Opsional) hapus relasi lama jika masih ada
    // public function teacherClasses() { ... } // sebaiknya dihapus/diadaptasi
    // public function subjects() { ... }       // digantikan subjectsTaught...
}
