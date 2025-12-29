<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_classes', function (Blueprint $table) {

            // 1️⃣ Drop FK lama (user_id → users)
            $table->dropForeign(['user_id']);

            // 2️⃣ Rename kolom
            $table->renameColumn('user_id', 'teacher_id');
        });

        Schema::table('teacher_classes', function (Blueprint $table) {

            // 3️⃣ Buat FK baru (teacher_id → teachers)
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_classes', function (Blueprint $table) {

            $table->dropForeign(['teacher_id']);
            $table->renameColumn('teacher_id', 'user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
