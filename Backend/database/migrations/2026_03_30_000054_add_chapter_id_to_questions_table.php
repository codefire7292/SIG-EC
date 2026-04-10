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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->change();
            $table->foreignId('chapter_id')->nullable()->after('exam_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('qcm')->after('chapter_id'); // 'qcm' or 'open'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable(false)->change();
            $table->dropForeign(['chapter_id']);
            $table->dropColumn(['chapter_id', 'type']);
        });
    }
};
