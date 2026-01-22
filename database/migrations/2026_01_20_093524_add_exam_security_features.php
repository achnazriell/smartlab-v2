<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Update exams table dengan cara yang lebih sederhana
        Schema::table('exams', function (Blueprint $table) {
            // Tambahkan subject_id jika belum ada
            if (!Schema::hasColumn('exams', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('class_id')->constrained('subjects');
            }

            // Ubah tipe enum untuk menambahkan QUIZ dan Lainnya
            if (Schema::hasColumn('exams', 'type')) {
                \DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('UH', 'UTS', 'UAS', 'QUIZ', 'Lainnya') NOT NULL DEFAULT 'UH'");
            }

            // Ubah status enum
            if (Schema::hasColumn('exams', 'status')) {
                \DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('draft', 'active', 'finished', 'inactive') DEFAULT 'draft'");
            }

            // =========== TAMBAHKAN KOLOM KEAMANAN ===========

            // Kolom keamanan dasar
            if (!Schema::hasColumn('exams', 'allow_screenshot')) {
                $table->boolean('allow_screenshot')->default(false)->after('allow_copy');
            }

            if (!Schema::hasColumn('exams', 'require_camera')) {
                $table->boolean('require_camera')->default(false)->after('allow_screenshot');
            }

            if (!Schema::hasColumn('exams', 'require_mic')) {
                $table->boolean('require_mic')->default(false)->after('require_camera');
            }

            if (!Schema::hasColumn('exams', 'enable_proctoring')) {
                $table->boolean('enable_proctoring')->default(false)->after('require_mic');
            }

            if (!Schema::hasColumn('exams', 'block_new_tab')) {
                $table->boolean('block_new_tab')->default(true)->after('enable_proctoring');
            }

            if (!Schema::hasColumn('exams', 'fullscreen_mode')) {
                $table->boolean('fullscreen_mode')->default(true)->after('block_new_tab');
            }

            if (!Schema::hasColumn('exams', 'auto_submit')) {
                $table->boolean('auto_submit')->default(true)->after('fullscreen_mode');
            }

            if (!Schema::hasColumn('exams', 'prevent_copy_paste')) {
                $table->boolean('prevent_copy_paste')->default(true)->after('auto_submit');
            }

            if (!Schema::hasColumn('exams', 'limit_attempts')) {
                $table->integer('limit_attempts')->default(1)->after('prevent_copy_paste');
            }

            if (!Schema::hasColumn('exams', 'min_pass_grade')) {
                $table->decimal('min_pass_grade', 5, 2)->default(0.00)->after('limit_attempts');
            }

            if (!Schema::hasColumn('exams', 'show_correct_answer')) {
                $table->boolean('show_correct_answer')->default(false)->after('min_pass_grade');
            }

            if (!Schema::hasColumn('exams', 'show_result_after')) {
                $table->enum('show_result_after', ['never', 'immediately', 'after_submit', 'after_exam'])->default('never')->after('show_correct_answer');
            }

            // =========== KOLOM KHUSUS QUIZ ===========

            if (!Schema::hasColumn('exams', 'time_per_question')) {
                $table->integer('time_per_question')->nullable()->after('duration');
            }

            if (!Schema::hasColumn('exams', 'quiz_mode')) {
                $table->enum('quiz_mode', ['live', 'homework'])->nullable()->after('time_per_question');
            }

            if (!Schema::hasColumn('exams', 'show_leaderboard')) {
                $table->boolean('show_leaderboard')->default(false)->after('quiz_mode');
            }

            if (!Schema::hasColumn('exams', 'enable_music')) {
                $table->boolean('enable_music')->default(false)->after('show_leaderboard');
            }

            if (!Schema::hasColumn('exams', 'enable_memes')) {
                $table->boolean('enable_memes')->default(false)->after('enable_music');
            }

            if (!Schema::hasColumn('exams', 'enable_powerups')) {
                $table->boolean('enable_powerups')->default(false)->after('enable_memes');
            }

            if (!Schema::hasColumn('exams', 'randomize_questions')) {
                $table->boolean('randomize_questions')->default(true)->after('enable_powerups');
            }

            if (!Schema::hasColumn('exams', 'instant_feedback')) {
                $table->boolean('instant_feedback')->default(true)->after('randomize_questions');
            }

            if (!Schema::hasColumn('exams', 'streak_bonus')) {
                $table->boolean('streak_bonus')->default(true)->after('instant_feedback');
            }

            if (!Schema::hasColumn('exams', 'time_bonus')) {
                $table->boolean('time_bonus')->default(true)->after('streak_bonus');
            }

            if (!Schema::hasColumn('exams', 'difficulty_level')) {
                $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium')->after('time_bonus');
            }

            // Tambahkan soft deletes
            if (!Schema::hasColumn('exams', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 2. Update exam_attempts table
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_attempts', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('status');
            }

            if (!Schema::hasColumn('exam_attempts', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            if (!Schema::hasColumn('exam_attempts', 'violation_count')) {
                $table->integer('violation_count')->default(0)->after('user_agent');
            }

            if (!Schema::hasColumn('exam_attempts', 'violation_log')) {
                $table->text('violation_log')->nullable()->after('violation_count');
            }

            if (!Schema::hasColumn('exam_attempts', 'score')) {
                $table->decimal('score', 5, 2)->nullable()->after('violation_log');
            }

            if (!Schema::hasColumn('exam_attempts', 'final_score')) {
                $table->decimal('final_score', 5, 2)->nullable()->after('score');
            }

            if (!Schema::hasColumn('exam_attempts', 'is_cheating_detected')) {
                $table->boolean('is_cheating_detected')->default(false)->after('final_score');
            }
        });

        // 3. Update exam_answers table
        Schema::table('exam_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_answers', 'attempt_id')) {
                $table->foreignId('attempt_id')->nullable()->after('student_id')->constrained('exam_attempts')->onDelete('cascade');
            }

            if (!Schema::hasColumn('exam_answers', 'is_correct')) {
                $table->boolean('is_correct')->default(false)->after('score');
            }

            if (!Schema::hasColumn('exam_answers', 'time_taken')) {
                $table->integer('time_taken')->nullable()->after('is_correct');
            }

            if (!Schema::hasColumn('exam_answers', 'answered_at')) {
                $table->timestamp('answered_at')->nullable()->after('time_taken');
            }

            // Ubah choice_id menjadi nullable (untuk essay questions)
            $table->unsignedBigInteger('choice_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Hapus kolom dari exam_answers
        Schema::table('exam_answers', function (Blueprint $table) {
            if (Schema::hasColumn('exam_answers', 'attempt_id')) {
                $table->dropForeign(['attempt_id']);
                $table->dropColumn('attempt_id');
            }

            $columns = ['is_correct', 'time_taken', 'answered_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('exam_answers', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Kembalikan choice_id menjadi not null
            $table->unsignedBigInteger('choice_id')->nullable(false)->change();
        });

        // Hapus kolom dari exam_attempts
        Schema::table('exam_attempts', function (Blueprint $table) {
            $columns = [
                'ip_address', 'user_agent', 'violation_count',
                'violation_log', 'score', 'final_score', 'is_cheating_detected'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('exam_attempts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Hapus kolom dari exams (sebagian - tidak semua karena ada yang sudah ada sebelumnya)
        Schema::table('exams', function (Blueprint $table) {
            // Hapus kolom QUIZ
            $quizColumns = [
                'time_per_question', 'quiz_mode', 'show_leaderboard',
                'enable_music', 'enable_memes', 'enable_powerups',
                'randomize_questions', 'instant_feedback', 'streak_bonus',
                'time_bonus', 'difficulty_level'
            ];

            foreach ($quizColumns as $column) {
                if (Schema::hasColumn('exams', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Hapus kolom keamanan
            $securityColumns = [
                'allow_screenshot', 'require_camera', 'require_mic',
                'enable_proctoring', 'block_new_tab', 'fullscreen_mode',
                'auto_submit', 'prevent_copy_paste', 'limit_attempts',
                'min_pass_grade', 'show_correct_answer', 'show_result_after'
            ];

            foreach ($securityColumns as $column) {
                if (Schema::hasColumn('exams', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Hapus soft deletes
            if (Schema::hasColumn('exams', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            // Hapus subject_id
            if (Schema::hasColumn('exams', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }

            // Kembalikan enum ke nilai awal
            \DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('UH', 'UTS', 'UAS') NOT NULL DEFAULT 'UH'");
            \DB::statement("ALTER TABLE exams MODIFY COLUMN status ENUM('draft', 'active', 'finished') DEFAULT 'draft'");
        });
    }
};
