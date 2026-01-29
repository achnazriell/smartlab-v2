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
        // Materi pelajaran
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->string('title_materi');
            $table->string('file_materi');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tugas (TANPA class_id - akan menggunakan pivot table)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('materi_id')->nullable()->constrained('materis');
            $table->foreignId('user_id')->constrained('users');
            $table->string('title_task');
            $table->string('file_task')->nullable();
            $table->text('description_task')->nullable();
            $table->timestamp('date_collection');
            $table->timestamps();
        });

        // Pengumpulan tugas
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks');
            $table->foreignId('user_id')->constrained('users');
            $table->string('file_collection')->nullable();
            $table->enum('status', ['Belum mengumpulkan', 'Sudah mengumpulkan', 'Tidak mengumpulkan'])->default('Belum mengumpulkan');
            $table->timestamps();
        });

        // Penilaian tugas
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['Belum Di-nilai', 'Sudah Di-nilai']);
            $table->string('mark_task')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('materis');
    }
};
