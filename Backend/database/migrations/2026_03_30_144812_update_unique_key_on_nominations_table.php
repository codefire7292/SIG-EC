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
        Schema::table('nominations', function (Blueprint $table) {
            $table->dropUnique('nominations_group_user_unique');
            $table->unique(['group_id', 'user_id', 'role'], 'nominations_group_user_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nominations', function (Blueprint $table) {
            $table->dropUnique('nominations_group_user_role_unique');
            $table->unique(['group_id', 'user_id'], 'nominations_group_user_unique');
        });
    }
};
