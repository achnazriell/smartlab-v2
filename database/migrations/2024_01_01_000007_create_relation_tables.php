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
        // 1. Guru mengajar mata pelajaran
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['teacher_id', 'subject_id'], 'teacher_subject_unique');
            $table->timestamps();
        });

        // 2. Guru mengajar kelas
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('classes_id')->constrained('classes')->cascadeOnDelete();
            $table->unique(['teacher_id', 'classes_id'], 'teacher_class_unique');
            $table->timestamps();
        });

        // 3. Materi untuk kelas tertentu
        Schema::create('materi_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('materi_id')->constrained('materis')->cascadeOnDelete();
            $table->unique(['class_id', 'materi_id']);
            $table->timestamps();
        });

        // 4. Tugas untuk kelas tertentu
        Schema::create('class_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unique(['task_id', 'class_id']);
            $table->timestamps();
        });

        // 5. Guru mengajar kelas dengan mata pelajaran tertentu
        Schema::create('teacher_class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_class_id')->constrained('teacher_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['teacher_class_id', 'subject_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class_subjects');
        Schema::dropIfExists('class_task');
        Schema::dropIfExists('materi_classes');
        Schema::dropIfExists('teacher_classes');
        Schema::dropIfExists('teacher_subjects');
    }
};
