<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('accent', 20)->nullable()->after('emoji');
            $table->string('accent_dark', 20)->nullable()->after('accent');
            $table->string('bg', 20)->nullable()->after('accent_dark');
            $table->string('type', 20)->default('Normal')->after('bg');
            $table->unsignedInteger('price')->nullable()->after('type');
            $table->text('description')->nullable()->after('price');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('difficulty_level_id')->nullable()->change();
            $table->string('kind', 40)->nullable()->after('type');
            $table->integer('order_num')->default(0)->after('answer');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('user_id');
            $table->string('child_name')->nullable()->after('parent_name');
            $table->string('item')->nullable()->after('child_name');
            $table->string('product_key')->nullable()->change();
            $table->string('product_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['parent_name', 'child_name', 'item']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['kind', 'order_num']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['accent', 'accent_dark', 'bg', 'type', 'price', 'description']);
        });
    }
};
