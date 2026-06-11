<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Birth Acts
        Schema::create('birth_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registry_id')->constrained('registries')->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->uuid('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('place_of_birth');
            $table->enum('gender', ['M', 'F']);
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->jsonb('parents_metadata')->nullable();
            $table->enum('status', ['brouillon', 'valide', 'signe', 'annule'])->default('brouillon');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        // Marriage Acts
        Schema::create('marriage_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registry_id')->constrained('registries')->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->uuid('uuid')->unique();
            $table->string('husband_first_name');
            $table->string('husband_last_name');
            $table->string('wife_first_name');
            $table->string('wife_last_name');
            $table->date('marriage_date');
            $table->string('marriage_place');
            $table->jsonb('witnesses_metadata')->nullable();
            $table->enum('status', ['brouillon', 'valide', 'signe', 'annule'])->default('brouillon');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        // Death Acts
        Schema::create('death_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registry_id')->constrained('registries')->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->uuid('uuid')->unique();
            $table->string('deceased_first_name');
            $table->string('deceased_last_name');
            $table->date('date_of_death');
            $table->string('place_of_death');
            $table->string('cause_of_death')->nullable();
            $table->enum('status', ['brouillon', 'valide', 'signe', 'annule'])->default('brouillon');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_acts');
        Schema::dropIfExists('marriage_acts');
        Schema::dropIfExists('birth_acts');
    }
};
