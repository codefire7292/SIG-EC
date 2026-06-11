<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'officier@sig-ec.sn')->first();
$cert = App\Models\CivilCertificate::first();

$request = Illuminate\Http\Request::create('/civil-certificates/' . $cert->id, 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = app()->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
        if(session()->has('errors')) {
            echo "Errors: " . json_encode(session('errors')->getBag('default')->getMessages()) . "\n";
        }
    } else {
        echo "Body: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
