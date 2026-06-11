<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table) {
            // Link to the centers and registries (nullable for backward compat during migration)
            $table->foreignId('civil_registration_center_id')->nullable()->after('id')->constrained();
            $table->foreignId('registry_id')->nullable()->after('civil_registration_center_id')->constrained();
            
            // Add draft/cancelled statuses to existing status column implicitly via validation logic or update enum if DB supports it
            // For Postgres, we can update the check constraint or just allow the string
        });
    }

    public function down(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registry_id');
            $table->dropConstrainedForeignId('civil_registration_center_id');
        });
    }
};
