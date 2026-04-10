<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$groups = \App\Models\Group::with('students')->whereIn('id', [1, 2])->get();
foreach ($groups as $group) {
    echo "Group {$group->id} ({$group->nom_groupe}) has students: " . $group->students->pluck('id')->implode(', ') . "\n";
}
