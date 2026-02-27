<?php
// File: 2024_01_01_000006_create_exams_table.php (FINAL VERSION)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EXAMS (Template Ujian) - COMPLETE VERSION
        |--------------------------------------------------------------------------
        */
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            // === BASIC INFO ===
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('subject_id')->nullable()->constrained('subjects');
            $table->string('title');
            $table->enum('type', ['UH', 'UTS', 'UAS', 'QUIZ', 'LAINNYA']);
            $table->string('custom_type')->nullable();
            $table->integer('duration'); // menit

            // === TIMING (optional untuk QUIZ, wajib untuk exam lain) ===
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();

            // === QUIZ SETTINGS (optional, hanya untuk type QUIZ) ===
            $table->integer('time_per_question')->nullable();
            $table->enum('quiz_mode', ['live', 'homework'])->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable();

            // === FLOW SETTINGS ===
            $table->boolean('shuffle_question')->default(false);
            $table->boolean('shuffle_answer')->default(false);

            // === SECURITY SETTINGS ===
            $table->boolean('fullscreen_mode')->default(true);
            $table->boolean('block_new_tab')->default(true);
            $table->boolean('prevent_copy_paste')->default(true);
            $table->boolean('disable_violations')->default(false);
            $table->integer('violation_limit')->default(3);

            // === PROCTORING ===
            $table->boolean('enable_proctoring')->default(false);
            $table->boolean('require_camera')->default(false);
            $table->boolean('require_mic')->default(false);

            // === RESULT SETTINGS ===
            $table->boolean('show_score')->default(false);
            $table->boolean('show_correct_answer')->default(false);
            $table->enum('show_result_after', [
                'never',
                'immediately',
                'after_submit',
                'after_exam'
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

            // === ROOM SETTINGS (untuk quiz live) ===
            $table->boolean('is_room_open')->default(false);
            $table->timestamp('room_opened_at')->nullable();
            $table->boolean('is_quiz_started')->default(false);
            $table->timestamp('quiz_started_at')->nullable();
            $table->integer('quiz_remaining_time')->nullable(); // detik

            // === STATUS ===
            $table->enum('status', ['draft', 'active', 'finished', 'inactive'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_room_open', 'is_quiz_started']);
            $table->index(['teacher_id', 'status']);
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM STUDENT (Pivot assignment quiz ke siswa)
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete(); // student_id = user_id
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index('exam_id');
            $table->index('student_id');
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM QUESTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();

            // Gunakan string agar dapat menampung semua tipe soal
            $table->string('type', 10);
            $table->text('question');
            $table->integer('score')->default(1);
            $table->json('short_answers')->nullable(); // Untuk soal IS
            $table->text('explanation')->nullable();

            $table->boolean('show_explanation')->default(false);
            $table->boolean('enable_skip')->default(true);
            $table->boolean('enable_mark_review')->default(true);
            $table->boolean('randomize_choices')->default(false);
            $table->boolean('require_all_options')->default(false);
            $table->boolean('enable_timer')->default(false);
            $table->integer('time_limit')->nullable();

            $table->string('image_path')->nullable();
            $table->integer('order')->default(0);

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
        | QUIZ SESSIONS (untuk monitoring live quiz)
        |--------------------------------------------------------------------------
        */
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();

            $table->string('session_code', 6)->unique();
            $table->enum('session_status', ['waiting', 'active', 'finished'])->default('waiting');

            $table->datetime('session_started_at')->nullable();
            $table->datetime('session_ended_at')->nullable();
            $table->integer('total_duration')->nullable(); // detik

            $table->integer('total_students')->default(0);
            $table->integer('students_joined')->default(0);
            $table->integer('students_ready')->default(0);
            $table->integer('students_started')->default(0);
            $table->integer('students_submitted')->default(0);

            $table->timestamps();

            $table->index('session_code');
            $table->index('session_status');
            $table->index(['exam_id', 'session_status']);
        });

        /*
        |--------------------------------------------------------------------------
        | QUIZ PARTICIPANTS (siswa yang masuk ruangan)
        |--------------------------------------------------------------------------
        */
        Schema::create('quiz_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_session_id')->constrained('quiz_sessions')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['waiting', 'ready', 'started', 'submitted', 'disconnected'])->default('waiting');

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('disqualified_at')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_present')->default(true);

            // Violation tracking
            $table->boolean('is_violation')->default(false);
            $table->string('violation_type')->nullable();
            $table->integer('violation_count')->default(0);
            $table->json('violation_log')->nullable();

            $table->timestamps();

            $table->index(['quiz_session_id', 'student_id']);
            $table->index(['exam_id', 'student_id']);
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | EXAM ATTEMPTS
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_session_id')->nullable()->constrained('quiz_sessions')->nullOnDelete();

            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->integer('remaining_time')->default(0); // detik
            $table->enum('status', ['in_progress', 'submitted', 'timeout']);

            $table->json('exam_settings')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('violation_count')->default(0);
            $table->json('violation_log')->nullable();
            $table->boolean('is_cheating_detected')->default(false);

            $table->decimal('score', 5, 2)->default(0.00);
            $table->decimal('final_score', 5, 2)->default(0.00);

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
            // Kolom answer untuk menyimpan jawaban mentah
            $table->text('answer')->nullable();
            $table->text('answer_text')->nullable(); // tetap dipertahankan untuk kompatibilitas

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
        Schema::dropIfExists('quiz_participants');
        Schema::dropIfExists('quiz_sessions');
        Schema::dropIfExists('exam_choices');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_student');
        Schema::dropIfExists('exams');
    }
};
