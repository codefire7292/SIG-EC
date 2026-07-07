<?php

use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CivilCertificateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email')
    ->middleware('throttle:3,1');

Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('change-password', [AuthenticatedSessionController::class, 'changePassword'])
        ->name('password.change');
    Route::post('change-password', [AuthenticatedSessionController::class, 'updatePassword'])
        ->name('password.change.update');
});

// -----------------------------------------------------------------------
// Public Routes
// -----------------------------------------------------------------------
Route::get('/verify-certificate/{uuid}', [CertificateVerificationController::class, 'verify'])
    ->name('certificates.verify')
    ->middleware('throttle:15,1');

Route::post('/verify-certificate/search', [CertificateVerificationController::class, 'search'])
    ->name('certificates.search')
    ->middleware('throttle:10,1');

Route::get('/certificates/v/{uuid}', [CertificateVerificationController::class, 'show'])
    ->name('certificates.view')
    ->middleware('throttle:20,1');

// -----------------------------------------------------------------------
// Authenticated Routes (Civil Certificates)
// -----------------------------------------------------------------------
Route::middleware(['auth'])->group(function (): void {
    // Dashboard
    Route::get('/dashboard', [CivilCertificateController::class, 'dashboard'])
        ->name('dashboard');

    // Profile Management
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');

    // Civil Certificates Modules
    Route::get('/civil-certificates', [CivilCertificateController::class, 'index'])->name('civil-certificates.index');
    Route::get('/civil-certificates/create', [CivilCertificateController::class, 'create'])->name('civil-certificates.create');
    Route::post('/civil-certificates', [CivilCertificateController::class, 'store'])->name('civil-certificates.store');
    Route::get('/civil-certificates/{civilCertificate}', [CivilCertificateController::class, 'show'])->name('civil-certificates.show');
    Route::get('/civil-certificates/{civilCertificate}/edit', [CivilCertificateController::class, 'edit'])->name('civil-certificates.edit');
    Route::patch('/civil-certificates/{civilCertificate}', [CivilCertificateController::class, 'update'])->name('civil-certificates.update');
    Route::post('/civil-certificates/{civilCertificate}/approve', [CivilCertificateController::class, 'approve'])->name('civil-certificates.approve');
    Route::post('/civil-certificates/{civilCertificate}/sign', [CivilCertificateController::class, 'sign'])->name('civil-certificates.sign');
    Route::post('/civil-certificates/{civilCertificate}/observe', [CivilCertificateController::class, 'observe'])->name('civil-certificates.observe');
    Route::post('/civil-certificates/{civilCertificate}/request-correction', [CivilCertificateController::class, 'requestCorrection'])->name('civil-certificates.request-correction');
    Route::post('/civil-certificates/{civilCertificate}/rectify', [CivilCertificateController::class, 'rectify'])->name('civil-certificates.rectify');

    // 📂 État-Civil Acts (Unified Controller)
    Route::prefix('acts')->name('acts.')->group(function () {
        foreach (['naissance', 'mariage', 'deces'] as $type) {
            Route::prefix($type)->name($type . '.')->group(function () use ($type) {
                Route::get("/", [\App\Http\Controllers\CivilActController::class, 'index'])->name('index')->defaults('type', $type);
                Route::get("/create", [\App\Http\Controllers\CivilActController::class, 'create'])->name('create')->defaults('type', $type);
                Route::post("/", [\App\Http\Controllers\CivilActController::class, 'store'])->name('store')->defaults('type', $type);
                Route::get("/template", [\App\Http\Controllers\CivilActImportController::class, 'downloadTemplate'])->name('template')->defaults('type', $type);
                Route::post("/import", [\App\Http\Controllers\CivilActImportController::class, 'import'])->name('import')->defaults('type', $type);
                Route::get("/{id}", [\App\Http\Controllers\CivilActController::class, 'show'])->name('show')->defaults('type', $type);
                Route::get("/{id}/edit", [\App\Http\Controllers\CivilActController::class, 'edit'])->name('edit')->defaults('type', $type);
                Route::patch("/{id}", [\App\Http\Controllers\CivilActController::class, 'update'])->name('update')->defaults('type', $type);
                Route::post("/{id}/status", [\App\Http\Controllers\CivilActController::class, 'updateStatus'])->name('status')->defaults('type', $type);
            });
        }
    });

    // ⚙️ Administration Suite
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('permission:manage-users');
        Route::resource('centers', \App\Http\Controllers\CenterController::class)->middleware('permission:manage-centers');
        Route::resource('registries', \App\Http\Controllers\RegistryController::class)->middleware('permission:manage-centers');
        Route::post('registries/{registry}/close', [\App\Http\Controllers\RegistryController::class, 'close'])
            ->name('registries.close')
            ->middleware('permission:manage-centers');
        Route::post('registries/{registry}/reopen', [\App\Http\Controllers\RegistryController::class, 'reopen'])
            ->name('registries.reopen')
            ->middleware('permission:manage-centers');
        
        Route::middleware('permission:manage-centers')->group(function() {
            Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        });
    });
});
