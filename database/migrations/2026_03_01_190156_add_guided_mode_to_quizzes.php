<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan:
     * 1. exams.guided_current_index  — indeks soal aktif di Quiz Terpandu (guru kontrol)
     * 2. exams.quiz_mode enum update — tambah nilai 'guided'
     * 3. quiz_participants.violation_log — sudah ada, pastikan nullable json
     */
    public function up(): void
    {
        // ─── 1. Tambah kolom guided_current_index di exams ───────────────────
        if (!Schema::hasColumn('exams', 'guided_current_index')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->integer('guided_current_index')->default(0)->after('quiz_remaining_time')
                    ->comment('Indeks soal aktif untuk mode Quiz Terpandu');
            });
        }

        // ─── 2. Update enum quiz_mode di exams ──────────────────────────────
        // MySQL tidak bisa langsung ALTER ENUM lewat Blueprint->change() dengan aman,
        // jadi kita pakai raw SQL.
        DB::statement("
            ALTER TABLE exams
            MODIFY COLUMN quiz_mode ENUM('live', 'homework', 'guided') NULL
        ");

        // ─── 3. Pastikan violation_log di quiz_participants nullable json ────
        // Kolom ini sudah ada dari migration awal; tidak perlu diubah.
        // Namun jika belum ada, tambahkan:
        if (!Schema::hasColumn('quiz_participants', 'violation_log')) {
            Schema::table('quiz_participants', function (Blueprint $table) {
                $table->json('violation_log')->nullable()->after('violation_count');
            });
        }
    }

    public function down(): void
    {
        // Kembalikan enum
        DB::statement("
            ALTER TABLE exams
            MODIFY COLUMN quiz_mode ENUM('live', 'homework') NULL
        ");

        // Hapus guided_current_index
        if (Schema::hasColumn('exams', 'guided_current_index')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('guided_current_index');
            });
        }
    }
};
