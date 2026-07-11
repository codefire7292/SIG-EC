<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            // 1. Heure de naissance
            $table->time('time_of_birth')->nullable()->after('date_of_birth');

            // 2. Formation sanitaire (lieu d'accouchement)
            $table->string('health_facility')->nullable()->after('place_of_birth');

            // 5. Date d'inscription de l'acte
            $table->date('act_registration_date')->nullable()->after('health_facility');

            // 7. Pièces justificatives — fichiers PDF distincts par catégorie
            $table->string('doc_cni_pere_path')->nullable()->after('certificate_path');
            $table->string('doc_cni_mere_path')->nullable()->after('doc_cni_pere_path');
            $table->string('doc_acte_naissance_path')->nullable()->after('doc_cni_mere_path');
            $table->string('doc_cni_declarant_path')->nullable()->after('doc_acte_naissance_path');
            $table->string('doc_autres_path')->nullable()->after('doc_cni_declarant_path');
        });
    }

    public function down(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn([
                'time_of_birth',
                'health_facility',
                'act_registration_date',
                'doc_cni_pere_path',
                'doc_cni_mere_path',
                'doc_acte_naissance_path',
                'doc_cni_declarant_path',
                'doc_autres_path',
            ]);
        });
    }
};
