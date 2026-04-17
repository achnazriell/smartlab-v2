<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat departments TERLEBIH DAHULU
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Buat classes dengan foreign key ke departments
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name_class');
            $table->text('description')->nullable();
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('set null');
            $table->timestamps();
        });

        // 3. Buat subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name_subject');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('departments');
    }
};
