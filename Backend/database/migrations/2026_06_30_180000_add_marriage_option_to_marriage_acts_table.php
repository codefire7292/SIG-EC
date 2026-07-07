<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->string('marriage_option')->nullable()->after('marriage_place');
        });
    }

    public function down(): void
    {
        Schema::table('marriage_acts', function (Blueprint $table) {
            $table->dropColumn('marriage_option');
        });
    }
};
