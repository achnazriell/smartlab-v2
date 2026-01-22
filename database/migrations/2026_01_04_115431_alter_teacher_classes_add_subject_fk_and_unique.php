<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_classes', function (Blueprint $table) {

            // pastikan kolom ada & nullable
            if (!Schema::hasColumn('teacher_classes', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('classes_id');
            } else {
                $table->unsignedBigInteger('subject_id')->nullable()->change();
            }

            // foreign key ke subjects
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnDelete();

            // UNIQUE kombinasi (INI PENTING)
            $table->unique(
                ['teacher_id', 'classes_id', 'subject_id'],
                'teacher_classes_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('teacher_classes', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropUnique('teacher_classes_unique');
        });
    }
};
