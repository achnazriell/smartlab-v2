<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== BUAT ACADEMIC YEARS TERLEBIH DAHULU ==========
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // contoh: 2024/2025
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // ========== MATERI ==========
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->string('title_materi');
            $table->string('file_materi');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ========== TASKS ==========
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('materi_id')->nullable()->constrained('materis');
            $table->foreignId('user_id')->constrained('users');
            $table->string('title_task');
            $table->string('file_task')->nullable();
            $table->text('description_task')->nullable();
            $table->timestamp('date_collection');
            $table->timestamps();
        });

        // ========== COLLECTIONS ==========
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks');
            $table->foreignId('user_id')->constrained('users');
            $table->string('file_collection')->nullable();
            $table->string('status')->default('Belum mengumpulkan');
            $table->timestamps();
        });

        // ========== ASSESSMENTS ==========
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status')->default('Belum Di-nilai');
            $table->string('mark_task')->nullable();
            $table->timestamps();
        });

        // ========== FEEDBACKS ==========
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // saran, kritik, pertanyaan, rating
            $table->integer('rating')->nullable()->comment('1-5');
            $table->text('message');
            $table->string('category')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('materis');
        Schema::dropIfExists('academic_years');
    }
};
