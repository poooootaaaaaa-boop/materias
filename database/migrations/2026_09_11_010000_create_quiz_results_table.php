<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 30);
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total_questions');
            $table->timestamps();

            $table->index(['user_id', 'subject']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};