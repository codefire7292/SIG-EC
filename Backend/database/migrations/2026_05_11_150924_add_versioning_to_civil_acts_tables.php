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
                $table->foreignId('parent_id')->nullable()->constrained($table->getTable())->onDelete('cascade');
                $table->integer('version_number')->default(1);
                $table->text('rectification_reason')->nullable();
                $table->boolean('is_current')->default(true);
            });
        }
    }

    public function down(): void
    {
        $tables = ['civil_certificates', 'birth_acts', 'marriage_acts', 'death_acts'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn(['parent_id', 'version_number', 'rectification_reason', 'is_current']);
            });
        }
    }
};
