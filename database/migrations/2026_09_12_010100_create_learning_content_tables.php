<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji', 10)->default('📚');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('difficulty_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('difficulty_level_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('choice');
            $table->text('question');
            $table->json('options')->nullable();
            $table->text('answer')->nullable();
            $table->string('speak')->nullable();
            $table->string('speak_lang', 10)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('difficulty_levels');
        Schema::dropIfExists('subjects');
    }
};