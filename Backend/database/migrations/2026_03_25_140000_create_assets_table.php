<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique(); // QR Code identifier
            $table->string('nom');
            $table->string('serie')->nullable(); // numéro de série
            $table->string('etat', 50)->default('bon'); // bon, endommagé, hors_service
            $table->string('status', 30)->default('disponible'); // disponible, prete, maintenance
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
