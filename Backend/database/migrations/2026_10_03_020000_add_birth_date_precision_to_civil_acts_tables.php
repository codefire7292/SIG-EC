<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->string('birth_date_type')->default('exact')->after('date_of_birth');
            $table->smallInteger('birth_year')->nullable()->after('birth_date_type');
            $table->smallInteger('presumed_age')->nullable()->after('birth_year');
        });

        Schema::table('death_acts', function (Blueprint $table) {
            $table->string('birth_date_type')->default('exact')->nullable()->after('date_of_birth');
            $table->smallInteger('birth_year')->nullable()->after('birth_date_type');
            $table->smallInteger('presumed_age')->nullable()->after('birth_year');
        });
    }

    public function down(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn(['birth_date_type', 'birth_year', 'presumed_age']);
        });

        Schema::table('death_acts', function (Blueprint $table) {
            $table->dropColumn(['birth_date_type', 'birth_year', 'presumed_age']);
        });
    }
};
