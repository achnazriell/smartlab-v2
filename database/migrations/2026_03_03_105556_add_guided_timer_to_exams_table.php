<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Guided mode: timer per soal
            $table->unsignedBigInteger('guided_question_deadline')->nullable()->after('guided_current_index')
                  ->comment('Unix timestamp — kapan waktu soal aktif habis');
            $table->boolean('guided_show_answer')->default(false)->after('guided_question_deadline')
                  ->comment('true = fase tampil jawaban benar (hanya guru)');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['guided_question_deadline', 'guided_show_answer']);
        });
    }
};
