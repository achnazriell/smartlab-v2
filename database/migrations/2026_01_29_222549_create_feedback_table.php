<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};
