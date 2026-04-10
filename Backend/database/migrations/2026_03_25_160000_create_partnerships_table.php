<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnerships', function (Blueprint $table): void {
            $table->id();
            $table->string('nom');
            $table->string('type', 50); // institutionnel, privé, ONG, académique
            $table->text('description')->nullable();
            $table->date('date_signature')->nullable();
            $table->string('document_path')->nullable(); // PDF convention
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnerships');
    }
};
