<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_mentions', function (Blueprint $table): void {
            $table->id();
            $table->string('titre')->nullable();
            $table->string('lien_externe')->nullable(); // external URL
            $table->string('fichier_path')->nullable(); // uploaded file (screenshot, PDF)
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_mentions');
    }
};
