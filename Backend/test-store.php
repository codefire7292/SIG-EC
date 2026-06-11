<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\CivilCertificateRegistryService::class);
$enumType = App\Enums\CivilCertificateType::RESIDENCE;
$centerCode = 'DEF';
$year = now()->year;
$centerId = 1;

try {
    $registry = \App\Models\Registry::firstOrCreate(
        [
            'civil_registration_center_id' => $centerId,
            'type' => $enumType->value,
            'year' => $year,
        ],
        [
            'status' => 'open',
            'opening_date' => now(),
            'reference_prefix' => $service->getPrefix($enumType) . '-' . now()->year . '-' . $centerCode,
        ]
    );

    $certificate = new \App\Models\CivilCertificate();
    $certificate->registry_id = $registry->id;
    $certificate->type = $enumType;
    $certificate->center = $centerCode;
    $certificate->reference_number = $service->generateReference($enumType, $centerCode);
    $certificate->applicant_first_name = "Test";
    $certificate->applicant_last_name = "User";
    $certificate->applicant_cni = "123456789";
    $certificate->data = ["adresse" => "Dakar", "temoin_1_identite" => "A", "temoin_2_identite" => "B"];
    $certificate->status = 'brouillon';
    $certificate->save();
    echo "SUCCESS: Certificate ID " . $certificate->id . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
