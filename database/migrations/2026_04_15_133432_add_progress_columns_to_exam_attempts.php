<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom progress ke tabel exam_attempts
 * agar posisi soal + streak bisa disimpan dan dipulihkan saat refresh.
 *
 * Jalankan: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            // Nomor soal terakhir yang sedang dikerjakan (0-based index)
            if (!Schema::hasColumn('exam_attempts', 'current_question')) {
                $table->unsignedSmallInteger('current_question')->default(0)->after('remaining_time');
            }

            // Streak saat ini (disimpan agar tidak reset saat refresh)
            if (!Schema::hasColumn('exam_attempts', 'streak_count')) {
                $table->unsignedSmallInteger('streak_count')->default(0)->after('current_question');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumnIfExists('current_question');
            $table->dropColumnIfExists('streak_count');
        });
    }
};
