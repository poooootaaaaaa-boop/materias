<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('name');
            $table->string('parent_email')->nullable()->after('email');
            $table->string('school')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('state', 120)->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'parent_email', 'school', 'gender', 'country', 'state', 'terms_accepted_at']);
        });
    }
};