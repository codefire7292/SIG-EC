<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exams table
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->integer('duree_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_practice')->default(false); // PROMPT 9: Practice mode
            $table->timestamps();
        });

        // Questions table
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->text('enonce');
            $table->integer('points')->default(1);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        // Options table (for MCQ)
        Schema::create('options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('texte');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // Results table
        Schema::create('exam_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->timestamp('finished_at');
            $table->json('answers')->nullable(); // Answers summary
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
    }
};
