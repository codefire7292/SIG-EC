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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('adresse_reelle')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('niveau_etude')->nullable();
            $table->string('dernier_diplome_libelle')->nullable();
            $table->string('fonction')->nullable();
            $table->string('etablissement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'adresse_reelle',
                'date_naissance',
                'lieu_naissance',
                'niveau_etude',
                'dernier_diplome_libelle',
                'fonction',
                'etablissement',
            ]);
        });
    }
};
