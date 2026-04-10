<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'admitted', 'rejected'])->default('pending');
            $table->string('cni_path');
            $table->string('diploma_path');
            $table->text('commentaires')->nullable();
            $table->timestamps();

            // Un utilisateur ne peut postuler qu'une seule fois par module
            $table->unique(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
