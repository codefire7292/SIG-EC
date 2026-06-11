<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CivilCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerificationController extends Controller
{
    /**
     * Display the public verification search page.
     */
    public function verify(): Response
    {
        return Inertia::render('Public/Verify');
    }

    /**
     * Display the public view of a certificate.
     */
    public function show(string $uuid): Response
    {
        $certificate = CivilCertificate::where('uuid', $uuid)
            ->where('status', 'signe')
            ->firstOrFail();

        return Inertia::render('Public/View', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Search for a certificate by reference number (Public).
     */
    public function search(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        $certificate = CivilCertificate::where('reference_number', $request->reference)
            ->where('status', 'signe')
            ->first();

        if (!$certificate) {
            return back()->with('error', 'Aucun certificat validé trouvé pour cette référence.');
        }

        return redirect()->route('certificates.view', $certificate->uuid);
    }
}
