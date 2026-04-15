<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom 'warnings' ke tabel quiz_participants
     * untuk menyimpan peringatan dari guru ke siswa (JSON array).
     *
     * Jalankan dengan: php artisan migrate
     */
    public function up(): void
    {
        Schema::table('quiz_participants', function (Blueprint $table) {
            $table->json('warnings')->nullable()->after('violation_count')
                  ->comment('Array JSON peringatan dari guru, format: [{id, message, sent_at, seen}]');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_participants', function (Blueprint $table) {
            $table->dropColumn('warnings');
        });
    }
};
