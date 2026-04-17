<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Materi pelajaran (ditambah class_id, academic_year_id)
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->string('title_materi');
            $table->string('file_materi');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tugas (ditambah class_id, academic_year_id)
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('cascade');
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

        // === TABEL FEEDBACK (dari migration tambahan) ===
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['saran', 'kritik', 'pertanyaan', 'rating']);
            $table->integer('rating')->nullable()->comment('1-5');
            $table->text('message');
            $table->string('category')->nullable()->comment('umum, akademik, fasilitas, dll');
            $table->enum('status', ['pending', 'dibaca', 'ditindaklanjuti'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('materis');
    }
};
