<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';
    protected $guarded = ['id'];

    /**
     * Relasi ke department (jurusan)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relasi ke penempatan guru mengajar (teacher_subject_assignments)
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Relasi ke penempatan siswa (student_class_assignments)
     */
    public function studentAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'class_id');
    }

    /**
     * Mendapatkan siswa yang saat ini berada di kelas ini (tahun ajaran aktif)
     */
    public function currentStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_class_assignments',
            'class_id',
            'student_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                ->from('academic_years')
                ->where('is_active', true)
                ->limit(1);
        })->withTimestamps();
    }

    /**
     * Mendapatkan guru yang mengajar di kelas ini (tahun ajaran aktif)
     */
    public function currentTeachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_subject_assignments',
            'class_id',
            'teacher_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                ->from('academic_years')
                ->where('is_active', true)
                ->limit(1);
        })->withPivot('subject_id')->withTimestamps();
    }

    /**
     * Mendapatkan mata pelajaran yang diajarkan di kelas ini (tahun ajaran aktif)
     */
    public function subjectsTaught()
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subject_assignments',
            'class_id',
            'subject_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                ->from('academic_years')
                ->where('is_active', true)
                ->limit(1);
        })->withPivot('teacher_id')->withTimestamps();
    }

    /**
     * Relasi ke tugas (jika tugas memiliki class_id langsung)
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'class_id');
    }

    /**
     * Relasi ke materi (jika materi memiliki class_id langsung)
     */
    public function materis()
    {
        return $this->hasMany(Materi::class, 'class_id');
    }

    /**
     * Relasi many-to-many ke materi melalui tabel pivot (jika ada)
     */
    public function materisViaPivot()
    {
        return $this->belongsToMany(Materi::class, 'materi_classes', 'class_id', 'materi_id');
    }
}
