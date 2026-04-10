<?php

declare(strict_types=1);

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
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->string('nom_groupe');
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('formateur_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('responsable_groupe_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('annee_academique', 9); // e.g. "2024-2025"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
