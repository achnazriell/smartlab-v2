<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // 1. TABEL DEPARTMENTS (JURUSAN)
        // ============================================
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // ============================================
        // 2. TABEL ACADEMIC YEARS (TAHUN AJARAN)
        // ============================================
        if (!Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // contoh: 2024/2025
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        // ============================================
        // 3. TABEL STUDENT CLASS ASSIGNMENTS
        // ============================================
        if (!Schema::hasTable('student_class_assignments')) {
            Schema::create('student_class_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
                $table->timestamps();

                // Seorang siswa hanya punya satu kelas per tahun ajaran
                $table->unique(['student_id', 'academic_year_id'], 'student_academic_unique');
            });
        }

        // ============================================
        // 4. TABEL TEACHER SUBJECT ASSIGNMENTS
        // ============================================
        if (!Schema::hasTable('teacher_subject_assignments')) {
            Schema::create('teacher_subject_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
                $table->timestamps();

                // Optional unique key jika diperlukan
                // $table->unique(['teacher_id', 'subject_id', 'class_id', 'academic_year_id'], 'teacher_subject_class_unique');
            });
        }

        // ============================================
        // 5. TAMBAH KOLOM department_id KE TABEL classes
        // ============================================
        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'department_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('departments')
                    ->onDelete('set null');
            });
        }

        // ============================================
        // 6. TAMBAH KOLOM student_code KE TABEL students
        // ============================================
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'student_code')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('student_code')
                    ->unique()
                    ->nullable()
                    ->after('nis');
            });
        }

        // ============================================
        // 7. TAMBAH KOLOM class_id DAN academic_year_id KE TABEL tasks
        // ============================================
        if (Schema::hasTable('tasks')) {
            if (!Schema::hasColumn('tasks', 'class_id')) {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->foreignId('class_id')
                        ->nullable()
                        ->after('subject_id')
                        ->constrained('classes')
                        ->onDelete('cascade');
                });
            }
            if (!Schema::hasColumn('tasks', 'academic_year_id')) {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->foreignId('academic_year_id')
                        ->nullable()
                        ->after('class_id')
                        ->constrained('academic_years')
                        ->onDelete('cascade');
                });
            }
        }

        // ============================================
        // 8. TAMBAH KOLOM class_id DAN academic_year_id KE TABEL materis
        // ============================================
        if (Schema::hasTable('materis')) {
            if (!Schema::hasColumn('materis', 'class_id')) {
                Schema::table('materis', function (Blueprint $table) {
                    $table->foreignId('class_id')
                        ->nullable()
                        ->after('subject_id')
                        ->constrained('classes')
                        ->onDelete('cascade');
                });
            }
            if (!Schema::hasColumn('materis', 'academic_year_id')) {
                Schema::table('materis', function (Blueprint $table) {
                    $table->foreignId('academic_year_id')
                        ->nullable()
                        ->after('class_id')
                        ->constrained('academic_years')
                        ->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus kolom dari tasks dan materis (jika ada)
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'academic_year_id')) {
                    $table->dropForeign(['academic_year_id']);
                    $table->dropColumn('academic_year_id');
                }
                if (Schema::hasColumn('tasks', 'class_id')) {
                    $table->dropForeign(['class_id']);
                    $table->dropColumn('class_id');
                }
            });
        }

        if (Schema::hasTable('materis')) {
            Schema::table('materis', function (Blueprint $table) {
                if (Schema::hasColumn('materis', 'academic_year_id')) {
                    $table->dropForeign(['academic_year_id']);
                    $table->dropColumn('academic_year_id');
                }
                if (Schema::hasColumn('materis', 'class_id')) {
                    $table->dropForeign(['class_id']);
                    $table->dropColumn('class_id');
                }
            });
        }

        // Hapus kolom student_code dari students
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'student_code')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('student_code');
            });
        }

        // Hapus kolom department_id dari classes
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'department_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // Hapus tabel (urutan terbalik)
        Schema::dropIfExists('teacher_subject_assignments');
        Schema::dropIfExists('student_class_assignments');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('departments');
    }
};
