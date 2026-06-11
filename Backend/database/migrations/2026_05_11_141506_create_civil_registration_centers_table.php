<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civil_registration_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Ex: KOL, DAK
            $table->string('commune')->nullable();
            $table->string('region')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->nullable(); // Contact, chef de centre, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civil_registration_centers');
    }
};
