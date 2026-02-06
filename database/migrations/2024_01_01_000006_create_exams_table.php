<?php
// File: 2024_01_01_000006_create_exams_system_clean.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EXAMS (Template Ujian) - CLEAN VERSION
        |--------------------------------------------------------------------------
        | Total: 19 fields (dengan disable_violations)
        | Status: ✅ No conflicts, ✅ No redundancies
        */
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            // === BASIC INFO ===
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('subject_id')->nullable()->constrained();
            $table->string('title');
            $table->enum('type', ['UH', 'UTS', 'UAS', 'QUIZ', 'LAINNYA']);
            $table->string('custom_type')->nullable(); // Untuk type LAINNYA
            $table->integer('duration'); // menit

            // === TIMING ===
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();

            // === QUIZ SETTINGS (optional, hanya untuk type QUIZ) ===
            $table->integer('time_per_question')->nullable();
            $table->enum('quiz_mode', ['live', 'homework'])->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable();

            // === FLOW SETTINGS ===
            $table->boolean('shuffle_question')->default(false); // ✅ Unified for all exam types
            $table->boolean('shuffle_answer')->default(false);   // ✅ Clear purpose

            // === SECURITY SETTINGS ===
            $table->boolean('fullscreen_mode')->default(true);
            $table->boolean('block_new_tab')->default(true);
            $table->boolean('prevent_copy_paste')->default(true); // ✅ Single source of truth
            $table->boolean('disable_violations')->default(false); // ✅ NEW: Matikan semua pelanggaran
            $table->integer('violation_limit')->default(3);       // ✅ Auto-submit automatically when reached

            // === PROCTORING ===
            $table->boolean('enable_proctoring')->default(false);
            $table->boolean('require_camera')->default(false);
            $table->boolean('require_mic')->default(false);

            // === RESULT SETTINGS ===
            $table->boolean('show_score')->default(false);
            $table->boolean('show_correct_answer')->default(false); // ✅ Master control
            $table->enum('show_result_after', [
                'never',           // Tidak pernah
                'immediately',     // Setelah submit (langsung)
                'after_submit',    // Setelah semua siswa submit
                'after_exam'       // Setelah waktu ujian berakhir
            ])->default('never');
            $table->integer('limit_attempts')->default(1);
            $table->decimal('min_pass_grade', 5, 2)->default(0);

            // === QUIZ FEATURES (optional, hanya untuk type QUIZ) ===
            $table->boolean('show_leaderboard')->default(false);
            $table->boolean('enable_music')->default(false);
            $table->boolean('enable_memes')->default(false);
            $table->boolean('enable_powerups')->default(false);
            $table->boolean('instant_feedback')->default(false);
            $table->boolean('streak_bonus')->default(false);
            $table->boolean('time_bonus')->default(false);
            $table->boolean('enable_retake')->default(false);

            // === STATUS ===
            $table->enum('status', ['draft', 'active', 'finished', 'inactive'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM QUESTIONS - CLEAN VERSION
        |--------------------------------------------------------------------------
        | Total: 3 essential fields (down from 10+)
        | Status: ✅ No conflicts, ✅ No redundancies
        */
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();

            // === QUESTION CONTENT ===
            $table->enum('type', ['PG', 'IS']);
            $table->text('question');
            $table->integer('score')->default(1);
            $table->json('short_answers')->nullable(); // Untuk soal IS
            $table->text('explanation')->nullable();
            $table->integer('order')->default(0);

            // === ESSENTIAL QUESTION SETTINGS ===
            $table->boolean('enable_skip')->default(true);       // ✅ Berguna per soal
            $table->boolean('enable_mark_review')->default(true); // ✅ Berguna per soal

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM CHOICES
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();

            $table->string('label');
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM ATTEMPTS (Snapshot)
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            // === TIMING ===
            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->integer('remaining_time'); // detik
            $table->enum('status', ['in_progress', 'submitted', 'timeout']);

            // === SNAPSHOT SETTINGS ===
            $table->json('exam_settings');

            // === MONITORING ===
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('violation_count')->default(0);
            $table->json('violation_log')->nullable();
            $table->boolean('is_cheating_detected')->default(false);

            // === RESULT ===
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM ANSWERS
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('choice_id')->nullable()->constrained('exam_choices');
            $table->text('answer_text')->nullable();

            $table->integer('score')->default(0);
            $table->boolean('is_correct')->default(false);
            $table->integer('time_taken')->nullable();
            $table->timestamp('answered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_choices');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
