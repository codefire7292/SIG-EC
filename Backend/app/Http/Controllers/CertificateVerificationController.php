<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BirthAct;
use App\Models\CivilCertificate;
use App\Models\DeathAct;
use App\Models\MarriageAct;
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
     * Search for any civil act or certificate by reference number (Public).
     * Searches across: birth_acts, marriage_acts, death_acts, civil_certificates
     */
    public function search(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9\-\/]+$/'],
        ], [
            'reference.regex'   => 'Le format du numéro de référence est invalide.',
            'reference.max'     => 'Le numéro de référence ne doit pas dépasser 60 caractères.',
            'reference.required' => 'Veuillez saisir une référence.',
        ]);

        $ref = strtoupper(trim($request->reference));

        // 1. Actes de Naissance
        $birth = BirthAct::where('reference_number', $ref)
            ->whereIn('status', ['valide', 'signe'])
            ->first();
        if ($birth) {
            return redirect()->route('acts.verify.show', ['type' => 'naissance', 'uuid' => $birth->uuid]);
        }

        // 2. Actes de Mariage
        $marriage = MarriageAct::where('reference_number', $ref)
            ->whereIn('status', ['valide', 'signe'])
            ->first();
        if ($marriage) {
            return redirect()->route('acts.verify.show', ['type' => 'mariage', 'uuid' => $marriage->uuid]);
        }

        // 3. Actes de Décès
        $death = DeathAct::where('reference_number', $ref)
            ->whereIn('status', ['valide', 'signe'])
            ->first();
        if ($death) {
            return redirect()->route('acts.verify.show', ['type' => 'deces', 'uuid' => $death->uuid]);
        }

        // 4. Certificats civils (ancienne table)
        $certificate = CivilCertificate::where('reference_number', $ref)
            ->where('status', 'signe')
            ->first();
        if ($certificate) {
            return redirect()->route('certificates.view', $certificate->uuid);
        }

        return back()->with('error', 'Aucun acte ou certificat validé trouvé pour cette référence.');
    }

    /**
     * Display the public view of a civil act (naissance, mariage, décès).
     */
    public function showAct(string $type, string $uuid): Response
    {
        $act = match ($type) {
            'naissance' => BirthAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            'mariage'   => MarriageAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            'deces'     => DeathAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            default     => abort(404),
        };

        return Inertia::render('Public/ActView', [
            'act'  => $act,
            'type' => $type,
        ]);
    }

    /**
     * Display the public view of a civil certificate (legacy).
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
     * Download the PDF extract of a civil act (Public/Internal).
     */
    public function downloadExtract(string $type, string $uuid, \App\Services\DocumentGenerationService $documentService)
    {
        $act = match ($type) {
            'naissance' => BirthAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            'mariage'   => MarriageAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            'deces'     => DeathAct::with('registry')->where('uuid', $uuid)
                ->whereIn('status', ['valide', 'signe'])
                ->firstOrFail(),
            default     => abort(404),
        };

        $pdfContent = $documentService->generateActExtractPdf($act, $type);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="extrait_' . $type . '_' . $act->reference_number . '.pdf"',
        ]);
    }
}
