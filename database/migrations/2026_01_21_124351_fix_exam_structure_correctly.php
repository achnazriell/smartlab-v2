<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom yang benar-benar belum ada di exam_questions
        if (Schema::hasTable('exam_questions')) {
            // Hanya tambah kolom yang belum ada
            $columns = Schema::getColumnListing('exam_questions');

            if (!in_array('short_answers', $columns)) {
                Schema::table('exam_questions', function (Blueprint $table) {
                    $table->json('short_answers')->nullable()->after('score');
                });
            }

            if (!in_array('order', $columns)) {
                Schema::table('exam_questions', function (Blueprint $table) {
                    $table->integer('order')->default(0)->after('short_answers');
                });
            }

            if (!in_array('explanation', $columns)) {
                Schema::table('exam_questions', function (Blueprint $table) {
                    $table->text('explanation')->nullable()->after('order');
                });
            }
        }

        // 2. Tambah kolom yang belum ada di exam_choices
        if (Schema::hasTable('exam_choices')) {
            $columns = Schema::getColumnListing('exam_choices');

            if (!in_array('order', $columns)) {
                Schema::table('exam_choices', function (Blueprint $table) {
                    $table->integer('order')->default(0)->after('is_correct');
                });
            }
        }

        // 3. Tambah kolom yang belum ada di exam_answers
        if (Schema::hasTable('exam_answers')) {
            $columns = Schema::getColumnListing('exam_answers');

            if (!in_array('attempt_id', $columns)) {
                Schema::table('exam_answers', function (Blueprint $table) {
                    $table->foreignId('attempt_id')->nullable()->after('id');
                });

                // Tambah foreign key setelah kolom dibuat
                Schema::table('exam_answers', function (Blueprint $table) {
                    $table->foreign('attempt_id')->references('id')->on('exam_attempts')->onDelete('cascade');
                });
            }

            if (!in_array('is_correct', $columns)) {
                Schema::table('exam_answers', function (Blueprint $table) {
                    $table->boolean('is_correct')->default(false)->after('score');
                });
            }
        }
    }

    public function down(): void
    {
        // Optional: Hapus kolom jika perlu
        Schema::table('exam_questions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_questions', 'short_answers')) {
                $table->dropColumn('short_answers');
            }
            if (Schema::hasColumn('exam_questions', 'order')) {
                $table->dropColumn('order');
            }
            if (Schema::hasColumn('exam_questions', 'explanation')) {
                $table->dropColumn('explanation');
            }
        });

        Schema::table('exam_choices', function (Blueprint $table) {
            if (Schema::hasColumn('exam_choices', 'order')) {
                $table->dropColumn('order');
            }
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            if (Schema::hasColumn('exam_answers', 'attempt_id')) {
                $table->dropForeign(['attempt_id']);
                $table->dropColumn('attempt_id');
            }
            if (Schema::hasColumn('exam_answers', 'is_correct')) {
                $table->dropColumn('is_correct');
            }
        });
    }
};
