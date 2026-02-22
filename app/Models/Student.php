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
        'student_code',
        'status',
        'graduation_date',
    ];

    protected $casts = [
        'graduation_date' => 'date',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userAccount()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class);
    }

    public function codeHistories()
    {
        return $this->hasMany(StudentCodeHistory::class);
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function currentClass()
    {
        return $this->belongsToMany(
            Classes::class,
            'student_class_assignments',
            'student_id',
            'class_id'
        )->wherePivot('academic_year_id', function ($query) {
            $query->select('id')
                ->from('academic_years')
                ->where('is_active', true)
                ->limit(1);
        })->withTimestamps();
    }

    public function classHistory()
    {
        return $this->belongsToMany(
            Classes::class,
            'student_class_assignments',
            'student_id',
            'class_id'
        )->withPivot('academic_year_id')
            ->withTimestamps()
            ->orderBy('academic_year_id', 'desc');
    }

    // =========================================================================
    // HELPER STATIS — PARSING NAMA KELAS
    // =========================================================================

    /**
     * Ekstrak grade (X, XI, XII) dari nama kelas.
     * Contoh: "XI RPL 1" → "XI"
     */
    public static function extractGrade(string $className): string
    {
        $parts = explode(' ', trim($className));
        return strtoupper($parts[0] ?? 'X');
    }

    /**
     * Ekstrak nomor kelas (angka terakhir) dari nama kelas.
     * Contoh: "XI RPL 1" → "1"
     */
    public static function extractClassNumber(string $className): string
    {
        if (preg_match('/(\d+)$/', trim($className), $matches)) {
            return $matches[1];
        }
        return '1';
    }

    /**
     * Ekstrak kode jurusan dari objek kelas.
     */
    public static function extractDeptCode(Classes $class): string
    {
        return $class->department ? $class->department->code : 'UMUM';
    }

    /**
     * Konversi nama tahun ajaran ke format YY/YY.
     * Contoh: "2025/2026" → "25/26"
     */
    public static function formatYearCode(string $academicYearName): string
    {
        $parts = explode('/', $academicYearName);
        if (count($parts) === 2) {
            return substr($parts[0], -2) . '/' . substr($parts[1], -2);
        }
        return substr($academicYearName, -2);
    }

    // =========================================================================
    // GENERATE / ASSIGN KODE SISWA
    // =========================================================================

    public static function generateStudentCode(int $classId, int $academicYearId): ?string
    {
        $class        = Classes::with('department')->find($classId);
        $academicYear = AcademicYear::find($academicYearId);

        if (! $class || ! $academicYear) {
            return null;
        }

        $yearCode    = self::formatYearCode($academicYear->name);
        $grade       = self::extractGrade($class->name_class);
        $deptCode    = self::extractDeptCode($class);
        $classNumber = self::extractClassNumber($class->name_class);
        $prefix      = $yearCode . $grade . $deptCode . $classNumber;

        $lastStudent = Student::where('student_code', 'like', $prefix . '%')
            ->orderBy('student_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->student_code, -2);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    public function assignNewCode(int $classId, int $academicYearId)
    {
        $class        = Classes::with('department')->find($classId);
        $academicYear = AcademicYear::find($academicYearId);

        if (! $class || ! $academicYear) {
            return false;
        }

        $yearCode    = self::formatYearCode($academicYear->name);
        $grade       = self::extractGrade($class->name_class);
        $deptCode    = self::extractDeptCode($class);
        $classNumber = self::extractClassNumber($class->name_class);

        $studentsInClass = Student::whereHas('classAssignments', function ($q) use ($classId, $academicYearId) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $academicYearId);
        })->with('user:id,name')->get()->sortBy('user.name')->values();

        $position = null;
        foreach ($studentsInClass as $index => $s) {
            if ($s->id === $this->id) {
                $position = $index + 1;
                break;
            }
        }

        if ($position === null) {
            $position = $studentsInClass->count() + 1;
        }

        $newCode = $yearCode . $grade . $deptCode . $classNumber
                 . str_pad($position, 2, '0', STR_PAD_LEFT);

        $this->codeHistories()->updateOrCreate(
            [
                'academic_year_id' => $academicYearId,
                'class_id'         => $classId,
            ],
            [
                'student_code' => $newCode,
            ]
        );

        $this->student_code = $newCode;
        $this->save();

        return $newCode;
    }

    public static function updateCodesForAcademicYear(int $academicYearId): void
    {
        $assignments = StudentClassAssignment::with('student')
            ->where('academic_year_id', $academicYearId)
            ->get();

        foreach ($assignments as $assignment) {
            if ($assignment->student) {
                $assignment->student->assignNewCode(
                    $assignment->class_id,
                    $academicYearId
                );
            }
        }
    }
}