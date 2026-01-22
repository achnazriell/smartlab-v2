<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix exam_questions table
        Schema::table('exam_questions', function (Blueprint $table) {
            // Ubah tipe kolom 'type' dari multiple_choice/essay ke PG/IS
            if (Schema::hasColumn('exam_questions', 'type')) {
                \DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('PG', 'IS') NOT NULL DEFAULT 'PG'");
            }

            // Pastikan short_answers sebagai JSON
            if (!Schema::hasColumn('exam_questions', 'short_answers')) {
                $table->json('short_answers')->nullable()->after('score');
            }
        });

        // 2. Update exam_choices untuk mendukung PG
        Schema::table('exam_choices', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_choices', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            \DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('multiple_choice', 'essay') NOT NULL DEFAULT 'multiple_choice'");
        });
    }
};
