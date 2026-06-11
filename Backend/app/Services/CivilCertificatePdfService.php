<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CivilCertificate;

class CivilCertificatePdfService
{
    /**
     * Generates a PDF for a validated CivilCertificate.
     * In a real application, this would use Snappy or DomPDF.
     * Here, we simulate PDF generation by creating a dummy PDF or text file.
     */
    public function generate(CivilCertificate $certificate): string
    {
        // Define directory
        $dir = storage_path('app/public/civil_certificates/' . $certificate->type->value);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $certificate->reference_number . '.pdf';
        $path = $dir . '/' . $filename;

        // Dummy PDF content
        $content = "--- DOCUMENT SÉCURISÉ ---\n";
        $content .= "Certificat Type: " . $certificate->type->label() . "\n";
        $content .= "Reference: " . $certificate->reference_number . "\n";
        $content .= "Demandeur: " . $certificate->applicant_first_name . ' ' . $certificate->applicant_last_name . "\n";
        $content .= "Validé par: Admin ID " . ($certificate->validated_by ?? 'N/A') . "\n";
        $content .= "Date de validation: " . ($certificate->validated_at?->format('d/m/Y H:i') ?? 'N/A') . "\n";
        
        // TECHNICAL RULE: Signature électronique for Indigence
        if ($certificate->is_signed) {
             $content .= "--- SIGNATURE ÉLECTRONIQUE APPOSÉE LE " . now()->format('d/m/Y') . " ---\n";
             $content .= "Vérification Hash: " . bin2hex(random_bytes(16)) . "\n";
        }

        // SECURITY: Watermark simulation
        $content .= "\n[FILIGRANE DE SÉCURITÉ: REPRODUCTION INTERDITE]";
        
        file_put_contents($path, $content);

        return 'civil_certificates/' . $certificate->type->value . '/' . $filename;
    }
}
