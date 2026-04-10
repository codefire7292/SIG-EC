<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->change();
            $table->string('cni_path')->nullable()->change();
            $table->string('diploma_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable(false)->change();
            $table->string('cni_path')->nullable(false)->change();
            $table->string('diploma_path')->nullable(false)->change();
        });
    }
};
