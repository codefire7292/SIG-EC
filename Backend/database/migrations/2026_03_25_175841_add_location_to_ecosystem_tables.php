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
        Schema::table('events', function (Blueprint $table) {
            $table->string('lieu')->nullable()->after('titre');
        });

        Schema::table('partnerships', function (Blueprint $table) {
            $table->string('localisation_gps')->nullable()->after('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('lieu');
        });

        Schema::table('partnerships', function (Blueprint $table) {
            $table->dropColumn('localisation_gps');
        });
    }
};
