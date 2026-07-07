<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('registry_id');
        });

        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('registry_id');
        });
    }

    public function down(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn('certificate_path');
        });

        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->dropColumn('certificate_path');
        });
    }
};
