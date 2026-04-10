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
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('is_published')->default(false);
        });

        Schema::create('exercise_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->text('student_comment')->nullable();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('trainer_feedback')->nullable();
            $table->string('status')->default('pending'); // pending, graded
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_submissions');
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['content', 'video_url', 'attachments', 'is_published']);
        });
    }
};
