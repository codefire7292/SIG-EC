<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'officier@sig-ec.sn')->first();
if(!$user) die("No user");

$request = Illuminate\Http\Request::create('/civil-certificates', 'POST', [
    'type' => 'residence',
    'center' => 'DEF',
    'applicant_first_name' => 'Jean',
    'applicant_last_name' => 'Dupont',
    'applicant_cni' => '12345',
    'data' => [
        'adresse' => 'Dakar',
        'temoin_1_identite' => 'A',
        'temoin_2_identite' => 'B'
    ]
]);

$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        if(session()->has('errors')) {
            echo "Errors: " . json_encode(session('errors')->getBag('default')->getMessages()) . "\n";
        }
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    } else {
        echo "Body: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
