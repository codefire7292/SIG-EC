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
    public function generateActExtractPdf($act, string $type, ?int $volet = null): string
    {
        // 1. Generate QR Code pointing to the public verification URL
        $verificationUrl = route('acts.verify.show', ['type' => $type, 'uuid' => $act->uuid, 't' => time()]);
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

        // Load center from registry
        $center = null;
        if ($act->registry && $act->registry->civil_registration_center_id) {
            $center = \App\Models\CivilRegistrationCenter::find($act->registry->civil_registration_center_id);
        }

        // 2. Prepare Data
        $data = [
            'act'       => $act,
            'type'      => $type,
            'title'     => $title,
            'center'    => $center,
            'qrCode'    => $qrCodeBase64,
            'logo'      => base64_encode(file_get_contents(public_path('images/logo.png'))),
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'volet'     => $volet,
        ];

        // 3. Render PDF (Use pdf.volet when a volet is requested)
        $viewName = ($volet !== null && view()->exists('pdf.volet')) ? 'pdf.volet' : 'pdf.act';
        $pdf = Pdf::loadView($viewName, $data);
        
        // Finalize PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'margin_top'           => $volet ? 6 : 15,
            'margin_right'         => $volet ? 10 : 14,
            'margin_bottom'        => $volet ? 6 : 15,
            'margin_left'          => $volet ? 10 : 14,
        ]);

        return $pdf->output();
    }
}
