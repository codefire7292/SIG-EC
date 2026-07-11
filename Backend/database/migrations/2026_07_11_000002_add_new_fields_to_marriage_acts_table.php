<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->boolean('is_judgment')->default(false)->after('matrimonial_regime');
            $table->string('judgment_number')->nullable()->after('is_judgment');
            $table->date('judgment_date')->nullable()->after('judgment_number');
            $table->string('doc_cni_husband_path')->nullable()->after('judgment_date');
            $table->string('doc_cni_wife_path')->nullable()->after('doc_cni_husband_path');
            $table->string('doc_birth_husband_path')->nullable()->after('doc_cni_wife_path');
            $table->string('doc_birth_wife_path')->nullable()->after('doc_birth_husband_path');
            $table->string('doc_domicile_path')->nullable()->after('doc_birth_wife_path');
            $table->string('doc_medical_path')->nullable()->after('doc_domicile_path');
            $table->string('doc_parental_auth_path')->nullable()->after('doc_medical_path');
            $table->string('doc_jugement_path')->nullable()->after('doc_parental_auth_path');
            $table->string('doc_autres_path')->nullable()->after('doc_jugement_path');
            $table->jsonb('spouses_metadata')->nullable()->after('doc_autres_path');
        });
    }

    public function down(): void
    {
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->dropColumn([
                'is_judgment',
                'judgment_number',
                'judgment_date',
                'doc_cni_husband_path',
                'doc_cni_wife_path',
                'doc_birth_husband_path',
                'doc_birth_wife_path',
                'doc_domicile_path',
                'doc_medical_path',
                'doc_parental_auth_path',
                'doc_jugement_path',
                'doc_autres_path',
                'spouses_metadata',
            ]);
        });
    }
};
