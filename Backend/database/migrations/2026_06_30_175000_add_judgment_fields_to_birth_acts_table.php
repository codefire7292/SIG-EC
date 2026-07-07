<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->boolean('is_judgment')->default(false)->after('gender');
            $table->string('judgment_number')->nullable()->after('is_judgment');
            $table->date('judgment_date')->nullable()->after('judgment_number');
            $table->string('judgment_court')->nullable()->after('judgment_date');
        });
    }

    public function down(): void
    {
        Schema::table('birth_acts', function (Blueprint $table) {
            $table->dropColumn(['is_judgment', 'judgment_number', 'judgment_date', 'judgment_court']);
        });
    }
};
