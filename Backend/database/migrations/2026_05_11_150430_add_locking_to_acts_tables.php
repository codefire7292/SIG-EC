<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['civil_certificates', 'birth_acts', 'marriage_acts', 'death_acts'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamp('locked_at')->nullable()->after('status');
                $table->boolean('is_locked')->default(false)->after('locked_at');
            });
        }
    }

    public function down(): void
    {
        $tables = ['civil_certificates', 'birth_acts', 'marriage_acts', 'death_acts'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['locked_at', 'is_locked']);
            });
        }
    }
};
