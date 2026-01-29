<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | EXAMS (Template Ujian)
        |--------------------------------------------------------------------------
        */
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('subject_id')->nullable()->constrained();

            $table->string('title');
            $table->enum('type', ['UH', 'UTS', 'UAS', 'QUIZ', 'LAINNYA']);
            $table->integer('duration'); // menit
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();

            /*
    |--------------------------------------------------------------------------
    | FLOW
    |--------------------------------------------------------------------------
    */
            $table->boolean('shuffle_question')->default(false);
            $table->boolean('shuffle_answer')->default(false);

            /*
    |--------------------------------------------------------------------------
    | SECURITY
    |--------------------------------------------------------------------------
    */
            $table->boolean('fullscreen_mode')->default(true);
            $table->boolean('block_new_tab')->default(true);
            $table->boolean('prevent_copy_paste')->default(true);
            $table->boolean('allow_copy')->default(false);        // ⬅️ TAMBAHAN
            $table->boolean('allow_screenshot')->default(false);  // ⬅️ TAMBAHAN
            $table->boolean('auto_submit')->default(true);

            /*
    |--------------------------------------------------------------------------
    | PROCTORING
    |--------------------------------------------------------------------------
    */
            $table->boolean('enable_proctoring')->default(false);
            $table->boolean('require_camera')->default(false);
            $table->boolean('require_mic')->default(false);
            $table->integer('violation_limit')->default(3);

            /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */
            $table->boolean('show_score')->default(false);
            $table->boolean('show_correct_answer')->default(false);
            $table->enum('show_result_after', [
                'never',
                'after_submit',
                'after_exam'
            ])->default('never');

            /*
    |--------------------------------------------------------------------------
    | BUSINESS RULE
    |--------------------------------------------------------------------------
    */
            $table->integer('limit_attempts')->default(1);

            $table->enum('status', ['draft', 'active', 'finished', 'inactive'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });


        /*
        |--------------------------------------------------------------------------
        | EXAM QUESTIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['PG', 'IS']);
            $table->text('question');
            $table->integer('score')->default(1);
            $table->json('short_answers')->nullable();
            $table->text('explanation')->nullable();
            $table->integer('order')->default(0);

            // === PER QUESTION SETTINGS ===
            $table->boolean('enable_timer')->default(false);
            $table->integer('time_limit')->nullable(); // detik
            $table->boolean('enable_skip')->default(true);
            $table->boolean('enable_mark_review')->default(true);
            $table->boolean('show_explanation')->default(false);
            $table->boolean('randomize_choices')->default(false);
            $table->boolean('require_all_options')->default(false);

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
