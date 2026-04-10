<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apprenant = \App\Models\User::role('Apprenant')->first();
if (!$apprenant) { echo "No learner found.\n"; exit; }
echo "Learner: {$apprenant->name} (ID: {$apprenant->id})\n";
$groups = $apprenant->studentGroups()->get();
echo "Learner is in groups: " . collect($groups)->pluck('id')->implode(', ') . "\n";
