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
        Schema::table('internship_records', function (Blueprint $table) {
            $table->string('motivation_letter_path')->nullable();
            $table->string('cni_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('diploma_path')->nullable();
        });

        // PostgreSQL requires a bit more care with enum changes
        // We'll convert it to string for better flexibility
        DB::statement('ALTER TABLE internship_records ALTER COLUMN status DROP DEFAULT');
        DB::statement('ALTER TABLE internship_records ALTER COLUMN status TYPE VARCHAR(255)');
        DB::statement("ALTER TABLE internship_records ALTER COLUMN status SET DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_records', function (Blueprint $table) {
            $table->dropColumn(['motivation_letter_path', 'cni_path', 'cv_path', 'diploma_path']);
        });
        
        DB::statement('ALTER TABLE internship_records ALTER COLUMN status DROP DEFAULT');
        // This might fail if records have 'pending', so we update them first
        DB::statement("UPDATE internship_records SET status = 'active' WHERE status = 'pending'");
        DB::statement("ALTER TABLE internship_records ALTER COLUMN status TYPE VARCHAR(255)"); // keep as string to avoid issues
    }
};
