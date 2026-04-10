<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\Module;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $userId,
        private readonly int $moduleId,
    ) {}

    public function handle(): void
    {
        $user   = User::findOrFail($this->userId);
        $module = Module::findOrFail($this->moduleId);

        // Create certificate record
        $certificate = Certificate::firstOrCreate(
            [
                'user_id'   => $user->id,
                'module_id' => $module->id,
            ],
            [
                'issued_at' => now(),
            ],
        );

        // Generate QR Code pointing to public verification route
        $verificationUrl = url("/verify-certificate/{$certificate->uuid}");
        $qrCode       = base64_encode((string)QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($verificationUrl));

        // Generate PDF
        $pdf = Pdf::loadView('pdf.attestation', [
            'student'     => $user,
            'module'      => $module,
            'certificate' => $certificate,
            'qrCode'      => $qrCode,
            'issuedAt'    => $certificate->issued_at->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');

        // Store PDF
        $pdfPath = "certificates/{$certificate->uuid}.pdf";
        Storage::disk('local')->put($pdfPath, $pdf->output());

        // Update certificate with PDF path
        $certificate->update(['pdf_path' => $pdfPath]);
    }
}
