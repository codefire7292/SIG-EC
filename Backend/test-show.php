<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'maire@sig-ec.sn')->first();
$cert = App\Models\CivilCertificate::first();
if(!$cert) die("No cert");

// Act as user using the built-in HTTP tests feature equivalent context
auth()->login($user);

$request = Illuminate\Http\Request::create('/civil-certificates/' . $cert->id, 'GET');
// Attach session explicitly to prevent Auth middleware redirect
$session = $app['session']->driver();
$session->setId(uniqid());
$session->start();
$request->setLaravelSession($session);

$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
