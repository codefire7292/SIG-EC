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
            $table->boolean('is_signed')->default(false)->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table): void {
            $table->dropColumn('is_signed');
        });
    }
};
