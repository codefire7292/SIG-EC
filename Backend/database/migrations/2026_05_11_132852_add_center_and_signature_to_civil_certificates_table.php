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
        Schema::table('civil_certificates', function (Blueprint $table): void {
            $table->string('center')->nullable()->after('type'); // For "par centre"
            $table->string('signature_path')->nullable()->after('pdf_path'); // For "signature électronique"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table): void {
            $table->dropColumn(['center', 'signature_path']);
        });
    }
};
