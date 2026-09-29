<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('join_code', 12)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('classroom_user', function (Blueprint $table) {
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['classroom_id', 'user_id']);
        });

        Schema::create('classroom_activity', function (Blueprint $table) {
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['classroom_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_activity');
        Schema::dropIfExists('classroom_user');
        Schema::dropIfExists('classrooms');
    }
};