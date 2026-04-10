<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

function testScheduleEnforcement() {
    echo "Testing Exam Schedule Enforcement...\n";
    
    DB::beginTransaction();
    try {
        $exam = Exam::create([
            'module_id' => 1,
            'titre' => 'Timed Exam',
            'type' => 'online',
            'duree_minutes' => 60,
            'total_points' => 20,
            'scheduled_at' => Carbon::now()->addMinutes(10), // Starts in 10 mins
            'is_active' => true
        ]);

        echo "Exam scheduled at: " . $exam->scheduled_at . "\n";
        echo "Current time: " . Carbon::now() . "\n";
        echo "Can start now? " . ($exam->can_start ? 'YES' : 'NO') . " (Expected: NO)\n";

        // Test start window
        if ($exam->can_start) {
            echo "FAILURE: Exam allowed starting before scheduled time.\n";
        } else {
            echo "SUCCESS: Exam blocked starting before scheduled time.\n";
        }

        // Test expiration
        $exam->scheduled_at = Carbon::now()->subMinutes(70); // Ended 10 mins ago
        $exam->save();
        $exam->refresh();

        echo "Exam scheduled at: " . $exam->scheduled_at . " (Ends at: " . $exam->end_at . ")\n";
        echo "Is expired? " . ($exam->isExpired() ? 'YES' : 'NO') . " (Expected: YES)\n";
        
        if ($exam->isExpired()) {
            echo "SUCCESS: Exam correctly flagged as expired.\n";
        } else {
            echo "FAILURE: Exam missed expiration.\n";
        }

    } finally {
        DB::rollBack();
    }
}

testScheduleEnforcement();
