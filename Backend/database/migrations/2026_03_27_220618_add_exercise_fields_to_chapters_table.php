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
        Schema::table('chapters', function (Blueprint $table) {
            $table->enum('exercise_type', ['none', 'online', 'file'])->default('none')->after('is_published');
            $table->string('exercise_title')->nullable()->after('exercise_type');
            $table->text('exercise_instructions')->nullable()->after('exercise_title');
            $table->integer('exercise_points')->default(20)->after('exercise_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['exercise_type', 'exercise_title', 'exercise_instructions', 'exercise_points']);
        });
    }
};
