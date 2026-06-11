<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('civil_registration_center_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['naissance', 'mariage', 'deces', 'divers']);
            $table->integer('year');
            $table->string('reference_prefix'); // Ex: N-2026-KOL
            $table->integer('last_number')->default(0); // For auto-increment per registry
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->date('opening_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->timestamps();
            
            $table->unique(['civil_registration_center_id', 'type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registries');
    }
};
