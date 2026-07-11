<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->string('doc_jugement_path')->nullable()->after('doc_autres_path');
        });
    }

    public function down(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn('doc_jugement_path');
        });
    }
};
