<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the check constraint if it exists (PostgreSQL specific fix)
        // Usually created as table_column_check when using ->enum()
        try {
            DB::statement('ALTER TABLE internship_records DROP CONSTRAINT IF EXISTS internship_records_status_check');
        } catch (\Exception $e) {
            // Silently ignore if already dropped
        }

        // Also ensure it's varchar(255) for all future cases
        Schema::table('internship_records', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding the constraint is risky if 'pending' exists, so we leave it as string
    }
};
