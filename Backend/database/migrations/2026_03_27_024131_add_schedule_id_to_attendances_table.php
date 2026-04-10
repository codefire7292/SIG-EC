<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('group_id')->constrained()->nullOnDelete();
            
            // Drop old unique if it exists (depends on db state)
            // But usually we can just add a new one or replace it
            try {
                $table->dropUnique('attendances_user_group_date_unique');
            } catch (\Exception $e) {}

            $table->unique(['user_id', 'group_id', 'date', 'schedule_id'], 'attendances_full_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_full_unique');
            $table->dropConstrainedForeignId('schedule_id');
            $table->unique(['user_id', 'group_id', 'date'], 'attendances_user_group_date_unique');
        });
    }
};
