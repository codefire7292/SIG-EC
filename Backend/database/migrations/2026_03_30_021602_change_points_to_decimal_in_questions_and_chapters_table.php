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
        Schema::table('questions', function (Blueprint $table) {
            $table->decimal('points', 8, 2)->change();
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->decimal('exercise_points', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('points')->change();
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->integer('exercise_points')->change();
        });
    }
};
