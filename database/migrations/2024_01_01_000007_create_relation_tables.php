<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== TABEL BARU DARI MIGRASI TAMBAHAN ==========
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 2024/2025
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('student_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->unique(['student_id', 'academic_year_id'], 'student_academic_unique');
            $table->timestamps();
        });

        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('student_code_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained()->onDelete('set null');
            $table->string('student_code');
            $table->timestamps();
        });

        // ========== TABEL RELASI ASLI ==========
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['teacher_id', 'subject_id'], 'teacher_subject_unique');
            $table->timestamps();
        });

        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('classes_id')->constrained('classes')->cascadeOnDelete();
            $table->unique(['teacher_id', 'classes_id'], 'teacher_class_unique');
            $table->timestamps();
        });

        Schema::create('materi_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('materi_id')->constrained('materis')->cascadeOnDelete();
            $table->unique(['class_id', 'materi_id']);
            $table->timestamps();
        });

        Schema::create('class_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unique(['task_id', 'class_id']);
            $table->timestamps();
        });

        Schema::create('teacher_class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_class_id')->constrained('teacher_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['teacher_class_id', 'subject_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_subjects');
        Schema::dropIfExists('class_task');
        Schema::dropIfExists('materi_classes');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('student_code_histories');
        Schema::dropIfExists('teacher_subject_assignments');
        Schema::dropIfExists('student_class_assignments');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('departments');
    }
};
