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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('death_acts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('death_acts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
