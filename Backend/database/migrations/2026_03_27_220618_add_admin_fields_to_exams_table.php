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
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('type', ['online', 'paper'])->default('online')->after('titre');
            $table->string('document_path')->nullable()->after('type');
            $table->datetime('scheduled_at')->nullable()->after('document_path');
            $table->decimal('total_points', 8, 2)->default(20.00)->after('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['type', 'document_path', 'scheduled_at', 'total_points']);
        });
    }
};
