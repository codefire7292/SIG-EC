<?php

namespace App\Console\Commands;

use App\Models\CivilCertificate;
use App\Models\BirthAct;
use App\Models\MarriageAct;
use App\Models\DeathAct;
use Illuminate\Console\Command;

class CloseDailyActs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'acts:close-daily';

    /**
     * The console command description.
     */
    protected $description = 'Verrouille automatiquement tous les actes du jour à minuit pour empêcher toute altération ultérieure.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la clôture journalière des actes...');

        $tables = [
            CivilCertificate::class,
            BirthAct::class,
            MarriageAct::class,
            DeathAct::class
        ];

        $count = 0;
        foreach ($tables as $modelClass) {
            $updated = $modelClass::where('is_locked', false)
                ->whereDate('created_at', '<=', now()->toDateString())
                ->update([
                    'is_locked' => true,
                    'locked_at' => now()
                ]);
            
            $count += $updated;
            $this->line("Modèle " . class_basename($modelClass) . " : " . $updated . " actes verrouillés.");
        }

        $this->info("Clôture terminée. Au total, $count actes ont été définitivement verrouillés.");
    }
}
