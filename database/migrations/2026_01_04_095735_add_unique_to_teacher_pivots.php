<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // UNIQUE guru + mapel
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'subject_id'],
                'teacher_subject_unique'
            );
        });

        // UNIQUE guru + kelas
        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'classes_id'],
                'teacher_class_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique('teacher_subject_unique');
        });

        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->dropUnique('teacher_class_unique');
        });
    }
};
