<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes');
            $table->string('title');
            $table->enum('type', ['UH', 'UTS', 'UAS']);
            $table->integer('duration'); // menit
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();


            $table->boolean('shuffle_question')->default(false);
            $table->boolean('shuffle_answer')->default(false);
            $table->boolean('show_score')->default(false);
            $table->boolean('allow_copy')->default(false);

            $table->enum('status', ['draft', 'active', 'finished'])->default('draft');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
