<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            // Tambah kolom submitted_at setelah ended_at
            $table->timestamp('submitted_at')->nullable()->after('ended_at');
        });

        // Isi submitted_at dari ended_at untuk data yang sudah ada dengan status submitted/timeout
        DB::table('exam_attempts')
            ->whereIn('status', ['submitted', 'timeout'])
            ->whereNotNull('ended_at')
            ->update(['submitted_at' => DB::raw('ended_at')]);
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};
