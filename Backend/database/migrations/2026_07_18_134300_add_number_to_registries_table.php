<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registries', function (Blueprint $table) {
            $table->integer('number')->default(1);
            
            // Drop old unique constraint
            $table->dropUnique(['civil_registration_center_id', 'type', 'year']);
            
            // Add new unique constraint
            $table->unique(['civil_registration_center_id', 'type', 'year', 'number']);
        });
    }

    public function down(): void
    {
        Schema::table('registries', function (Blueprint $table) {
            $table->dropUnique(['civil_registration_center_id', 'type', 'year', 'number']);
            $table->dropColumn('number');
            $table->unique(['civil_registration_center_id', 'type', 'year']);
        });
    }
};
