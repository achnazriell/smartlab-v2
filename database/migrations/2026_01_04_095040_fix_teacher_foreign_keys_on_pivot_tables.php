<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ===============================
        // FIX teacher_subjects
        // ===============================
        Schema::table('teacher_subjects', function (Blueprint $table) {
            // drop FK lama
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('teacher_subjects', function (Blueprint $table) {
            // pastikan tipe sesuai
            $table->unsignedBigInteger('teacher_id')->change();

            // FK baru ke teachers.id
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();
        });

        // ===============================
        // FIX teacher_classes
        // ===============================
        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->change();

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // ===============================
        // rollback teacher_subjects
        // ===============================
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // ===============================
        // rollback teacher_classes
        // ===============================
        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
