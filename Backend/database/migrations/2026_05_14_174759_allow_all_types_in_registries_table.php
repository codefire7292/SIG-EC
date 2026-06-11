<?php

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
        Schema::table('registries', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE registries DROP CONSTRAINT IF EXISTS registries_type_check');
        });
    }

    public function down(): void
    {
        Schema::table('registries', function (Blueprint $table) {
            // Unwise to add check back without wiping data, leave empty for safety
        });
    }
};
