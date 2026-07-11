<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('death_acts', function (Blueprint $table) {
            $table->time('time_of_death')->nullable()->after('date_of_death');
            $table->string('health_facility')->nullable()->after('place_of_death');
            $table->date('act_registration_date')->nullable()->after('health_facility');
            $table->enum('gender', ['M', 'F'])->nullable()->after('deceased_last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->boolean('is_judgment')->default(false)->after('cause_of_death');
            $table->string('judgment_number')->nullable()->after('is_judgment');
            $table->date('judgment_date')->nullable()->after('judgment_number');
            $table->string('judgment_court')->nullable()->after('judgment_date');
            
            // Pièces justificatives
            $table->string('doc_death_cert_path')->nullable()->after('judgment_court');
            $table->string('doc_deceased_id_path')->nullable()->after('doc_death_cert_path');
            $table->string('doc_declarant_id_path')->nullable()->after('doc_deceased_id_path');
            $table->string('doc_jugement_path')->nullable()->after('doc_declarant_id_path');
            $table->string('doc_autres_path')->nullable()->after('doc_jugement_path');

            // Métadonnées JSON
            $table->jsonb('death_metadata')->nullable()->after('doc_autres_path');
            $table->jsonb('witnesses_metadata')->nullable()->after('death_metadata');
        });
    }

    public function down(): void
    {
        Schema::table('death_acts', function (Blueprint $table) {
            $table->dropColumn([
                'time_of_death',
                'health_facility',
                'act_registration_date',
                'gender',
                'date_of_birth',
                'is_judgment',
                'judgment_number',
                'judgment_date',
                'judgment_court',
                'doc_death_cert_path',
                'doc_deceased_id_path',
                'doc_declarant_id_path',
                'doc_jugement_path',
                'doc_autres_path',
                'death_metadata',
                'witnesses_metadata',
            ]);
        });
    }
};
