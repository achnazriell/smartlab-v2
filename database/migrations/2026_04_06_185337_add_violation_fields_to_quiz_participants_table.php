<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom violation_count dan violation_log ke tabel quiz_participants.
     *
     * Jalankan dengan: php artisan migrate
     */
    public function up(): void
    {
        Schema::table('quiz_participants', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada (aman untuk dijalankan ulang)
            if (! Schema::hasColumn('quiz_participants', 'violation_count')) {
                $table->unsignedInteger('violation_count')->default(0)->after('is_present')
                    ->comment('Jumlah pelanggaran yang dilakukan siswa');
            }
            if (! Schema::hasColumn('quiz_participants', 'violation_log')) {
                $table->json('violation_log')->nullable()->after('violation_count')
                    ->comment('Riwayat detail pelanggaran dalam format JSON array');
            }
            if (! Schema::hasColumn('quiz_participants', 'warnings')) {
                $table->json('warnings')->nullable()->after('violation_log')
                    ->comment('Peringatan dari guru dalam format JSON array');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_participants', function (Blueprint $table) {
            $table->dropColumn(['violation_count', 'violation_log', 'warnings']);
        });
    }
};
