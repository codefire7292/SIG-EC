<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. birth_acts
        DB::statement('ALTER TABLE birth_acts DROP CONSTRAINT IF EXISTS birth_acts_status_check');
        DB::statement("ALTER TABLE birth_acts ADD CONSTRAINT birth_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text, 'a_corriger'::text, 'rejete'::text]))");

        // 2. marriage_acts
        DB::statement('ALTER TABLE marriage_acts DROP CONSTRAINT IF EXISTS marriage_acts_status_check');
        DB::statement("ALTER TABLE marriage_acts ADD CONSTRAINT marriage_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text, 'a_corriger'::text, 'rejete'::text]))");

        // 3. death_acts
        DB::statement('ALTER TABLE death_acts DROP CONSTRAINT IF EXISTS death_acts_status_check');
        DB::statement("ALTER TABLE death_acts ADD CONSTRAINT death_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text, 'a_corriger'::text, 'rejete'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. birth_acts
        DB::statement('ALTER TABLE birth_acts DROP CONSTRAINT IF EXISTS birth_acts_status_check');
        DB::statement("ALTER TABLE birth_acts ADD CONSTRAINT birth_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text]))");

        // 2. marriage_acts
        DB::statement('ALTER TABLE marriage_acts DROP CONSTRAINT IF EXISTS marriage_acts_status_check');
        DB::statement("ALTER TABLE marriage_acts ADD CONSTRAINT marriage_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text]))");

        // 3. death_acts
        DB::statement('ALTER TABLE death_acts DROP CONSTRAINT IF EXISTS death_acts_status_check');
        DB::statement("ALTER TABLE death_acts ADD CONSTRAINT death_acts_status_check CHECK (status::text = ANY (ARRAY['brouillon'::text, 'valide'::text, 'signe'::text, 'annule'::text]))");
    }
};
