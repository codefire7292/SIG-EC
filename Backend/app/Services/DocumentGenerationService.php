<?php

namespace App\Services;

use App\Models\CivilCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentGenerationService
{
    /**
     * Generate a secure PDF for a civil certificate including a verification QR code.
     */
    public function generateCertificatePdf(CivilCertificate $certificate): string
    {
        // 1. Generate QR Code
        $verificationUrl = route('certificates.verify', $certificate->uuid);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($verificationUrl));

        // 2. Prepare Data
        $data = [
            'certificate' => $certificate,
            'qrCode' => $qrCodeBase64,
            'logo' => base64_encode(file_get_contents(public_path('images/logo.png'))),
            'timestamp' => now()->format('d/m/Y H:i:s'),
        ];

        // 3. Render PDF
        $pdf = Pdf::loadView('pdf.certificate', $data);
        
        // Finalize PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        // 4. Save to Storage
        $directory = 'civil_certificates/' . $certificate->type->value;
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = $certificate->reference_number . '_' . Str::random(5) . '.pdf';
        $path = $directory . '/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate a secure PDF extract for a civil act including a verification QR code.
     */
    public function generateActExtractPdf($act, string $type): string
    {
        // 1. Generate QR Code pointing to the public verification URL
        $verificationUrl = route('acts.verify.show', ['type' => $type, 'uuid' => $act->uuid]);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($verificationUrl));

        // Get french label/title
        $title = match ($type) {
            'naissance' => 'Naissance',
            'mariage' => 'Mariage',
            'deces' => 'Décès',
            default => 'Acte',
        };

        // 2. Prepare Data
        $data = [
            'act' => $act,
            'type' => $type,
            'title' => $title,
            'qrCode' => $qrCodeBase64,
            'logo' => base64_encode(file_get_contents(public_path('images/logo.png'))),
            'timestamp' => now()->format('d/m/Y H:i:s'),
        ];

        // 3. Render PDF
        $pdf = Pdf::loadView('pdf.act', $data);
        
        // Finalize PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->output();
    }
}
