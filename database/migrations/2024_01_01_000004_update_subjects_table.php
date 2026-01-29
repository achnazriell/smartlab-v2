<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan teacher_id ke subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('name_subject')
                ->constrained('teachers')
                ->onDelete('cascade');
        });

        // Update teachers dengan subject_id
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('subject_id')
                ->nullable()
                ->after('nip')
                ->constrained('subjects');
        });

        // Update students dengan class_id
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('class_id')
                ->nullable()
                ->after('nis')
                ->constrained('classes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
