<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table) {
            // Update status constraints or just keep string for flexibility with new states:
            // brouillon, observation, a_corriger, valide_hierarchie, signe
            $table->text('rejection_reason')->nullable()->after('status');
            $table->text('officer_comments')->nullable()->after('rejection_reason');
        });

        // Apply same to core acts
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->text('officer_comments')->nullable()->after('status');
        });
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->text('officer_comments')->nullable()->after('status');
        });
        Schema::table('death_acts', function (Blueprint $table) {
            $table->text('officer_comments')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('civil_certificates', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'officer_comments']);
        });
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn('officer_comments');
        });
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->dropColumn('officer_comments');
        });
        Schema::table('death_acts', function (Blueprint $table) {
            $table->dropColumn('officer_comments');
        });
    }
};
