<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToExamQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Cek dan tambah kolom yang belum ada
            if (!Schema::hasColumn('exam_questions', 'show_explanation')) {
                $table->boolean('show_explanation')->default(false)->after('explanation');
            }

            if (!Schema::hasColumn('exam_questions', 'randomize_choices')) {
                $table->boolean('randomize_choices')->default(false)->after('enable_mark_review');
            }

            if (!Schema::hasColumn('exam_questions', 'require_all_options')) {
                $table->boolean('require_all_options')->default(false)->after('randomize_choices');
            }

            if (!Schema::hasColumn('exam_questions', 'order')) {
                $table->integer('order')->default(0)->after('require_all_options');
            }

            if (!Schema::hasColumn('exam_questions', 'image_path')) {
                $table->string('image_path')->nullable()->after('order');
            }

            if (!Schema::hasColumn('exam_questions', 'enable_timer')) {
                $table->boolean('enable_timer')->default(false)->after('short_answers');
            }

            if (!Schema::hasColumn('exam_questions', 'time_limit')) {
                $table->integer('time_limit')->nullable()->after('enable_timer');
            }
        });
    }

    public function down()
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Hanya drop kolom jika ada
            $columns = [
                'show_explanation',
                'randomize_choices',
                'require_all_options',
                'order',
                'image_path',
                'enable_timer',
                'time_limit'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('exam_questions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
