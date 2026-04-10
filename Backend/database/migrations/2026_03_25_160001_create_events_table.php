<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('titre');
            $table->string('type_activite', 50); // conférence, atelier, séminaire, visite
            $table->date('date');
            $table->unsignedInteger('audience_estimee')->default(0);
            $table->string('image_path')->nullable(); // event photo
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
