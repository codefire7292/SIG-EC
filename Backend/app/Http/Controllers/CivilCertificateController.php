<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BirthAct;
use App\Models\MarriageAct;
use App\Models\DeathAct;
use App\Models\Registry;
use App\Models\User;
use App\Models\CivilRegistrationCenter;
use App\Models\CivilCertificate;
use App\Enums\CivilCertificateType;
use App\Services\CivilCertificateRegistryService;
use App\Services\DocumentGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CivilCertificateController extends Controller
{
    public function dashboard()
    {
        $stats = [
            // Certificats
            'total' => CivilCertificate::count(),
            'pending' => CivilCertificate::where('status', 'pending')->count(),
            'validated' => CivilCertificate::where('status', 'validated')->count(),
            'by_type' => CivilCertificate::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->mapWithKeys(fn($item) => [$item->type->value => $item->count]),
            
            // Actes Civils
            'births_count' => BirthAct::where('is_current', true)->count(),
            'marriages_count' => MarriageAct::where('is_current', true)->count(),
            'deaths_count' => DeathAct::where('is_current', true)->count(),
            
            // Actes par statut
            'acts_draft' => BirthAct::where('status', 'draft')->where('is_current', true)->count() +
                            MarriageAct::where('status', 'draft')->where('is_current', true)->count() +
                            DeathAct::where('status', 'draft')->where('is_current', true)->count(),
            
            'acts_valide' => BirthAct::where('status', 'valide')->where('is_current', true)->count() +
                             MarriageAct::where('status', 'valide')->where('is_current', true)->count() +
                             DeathAct::where('status', 'valide')->where('is_current', true)->count(),

            'acts_signe' => BirthAct::where('status', 'signe')->where('is_current', true)->count() +
                            MarriageAct::where('status', 'signe')->where('is_current', true)->count() +
                            DeathAct::where('status', 'signe')->where('is_current', true)->count(),

            'acts_a_corriger' => BirthAct::where('status', 'a_corriger')->where('is_current', true)->count() +
                                 MarriageAct::where('status', 'a_corriger')->where('is_current', true)->count() +
                                 DeathAct::where('status', 'a_corriger')->where('is_current', true)->count(),

            // Info Administrative
            'registries_open' => Registry::where('status', 'open')->count(),
            'registries_closed' => Registry::where('status', 'closed')->count(),
            'users_count' => User::count(),
            'centers_count' => CivilRegistrationCenter::count(),
        ];

        $recent = CivilCertificate::latest()->limit(5)->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
            'can' => [
                'view_registries' => Auth::user()->hasPermissionTo('view-registries'),
                'manage_users' => Auth::user()->hasPermissionTo('manage-users'),
            ]
        ]);
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', CivilCertificate::class);

        $type = $request->query('type');
        $query = CivilCertificate::orderBy('created_at', 'desc');
        
        if ($type && CivilCertificateType::tryFrom($type)) {
            $query->where('type', $type);
        }

        $certificates = $query->paginate(20);

        return Inertia::render('CivilCertificates/Index', [
            'certificates' => $certificates,
            'types' => array_column(CivilCertificateType::cases(), 'value'),
            'can_create' => Auth::user()->can('create', CivilCertificate::class),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', CivilCertificate::class);
        $type = $request->query('type', CivilCertificateType::RESIDENCE->value);
        return Inertia::render('CivilCertificates/Form', [
            'type' => $type,
            'types' => array_column(CivilCertificateType::cases(), 'value'),
        ]);
    }

    public function store(Request $request, CivilCertificateRegistryService $registryService)
    {
        Gate::authorize('create', CivilCertificate::class);
        $validatedData = $request->validate([
            'type' => ['required', 'string'],
            'applicant_first_name' => ['required', 'string', 'max:255'],
            'applicant_last_name' => ['required', 'string', 'max:255'],
            'applicant_cni' => ['nullable', 'string', 'max:50'],
            'data' => ['required', 'array'],
        ]);

        $this->validateCertificateData($validatedData['data']);

        $enumType = CivilCertificateType::from($validatedData['type']);
        $centerCode = $request->input('center', 'DEF');

        // 1. SPECIFIC VALIDATION
        if ($enumType === CivilCertificateType::RESIDENCE) {
            $request->validate([
                'data.adresse' => 'required|string',
                'applicant_cni' => 'required|string',
                'data.temoin_1_identite' => 'required|string',
                'data.temoin_2_identite' => 'required|string',
            ], [
                'applicant_cni.required' => 'Le numéro de CNI est obligatoire pour un certificat de résidence.',
                'data.adresse.required' => 'L\'adresse exacte est obligatoire.',
            ]);
        }

        if ($enumType === CivilCertificateType::INDIGENCE) {
            $request->validate([
                'data.ressources_mensuelles' => 'required|numeric',
                'data.composition_foyer' => 'required|string',
            ]);
        }

        if ($enumType === CivilCertificateType::VIE_COLLECTIVE) {
            $request->validate([
                'data.membres_identites' => 'required|string',
                'data.motif' => 'required|string',
            ]);
        }

        if ($enumType === CivilCertificateType::NON_INSCRIT_NAISSANCE) {
            $request->validate([
                'data.date_naissance' => 'required|date',
                'data.periode_recherche' => 'required|string',
            ]);
        }

        // TECHNICAL RULE: Duplicate control for Vie Collective
        if ($enumType === CivilCertificateType::VIE_COLLECTIVE) {
            $existing = CivilCertificate::where('type', CivilCertificateType::VIE_COLLECTIVE)
                ->where('data->membres_identites', $validatedData['data']['membres_identites'] ?? '')
                ->where('status', 'signe')
                ->first();
            
            if ($existing) {
                return back()->with('error', 'Un certificat identique pour ce groupe d\'individus existe déjà dans le registre.');
            }
        }

        // 2. AUTOMATED SEARCH (Non-inscription de naissance)
        if ($enumType === CivilCertificateType::NON_INSCRIT_NAISSANCE) {
            $found = $this->performAutomatedSearch($validatedData['applicant_first_name'], $validatedData['applicant_last_name'], $validatedData['data']['date_naissance'] ?? null);
            if ($found) {
                 return back()->with('error', 'Recherche automatisée bloquante : Un acte de naissance correspondant a été trouvé dans le registre numérique national. La délivrance du certificat de non-inscription est interdite.');
            }
        }

        $certificate = new CivilCertificate();

        $year = now()->year;
        $centerId = array_search($centerCode, ['DEF' => 1]) ?: 1;

        $registry = \App\Models\Registry::firstOrCreate(
            [
                'civil_registration_center_id' => $centerId,
                'type' => $enumType->value,
                'year' => $year,
            ],
            [
                'status' => 'open',
                'opening_date' => now(),
                'reference_prefix' => $registryService->getPrefix($enumType) . '-' . now()->year . '-' . $centerCode,
            ]
        );

        $certificate->registry_id = $registry->id;
        $certificate->type = $enumType;
        $certificate->center = $centerCode;
        $certificate->reference_number = $registryService->generateReference($enumType, $centerCode);
        $certificate->applicant_first_name = $validatedData['applicant_first_name'];
        $certificate->applicant_last_name = $validatedData['applicant_last_name'];
        $certificate->applicant_cni = $validatedData['applicant_cni'] ?? null;
        $certificate->data = $validatedData['data'];
        $certificate->status = 'brouillon';
        $certificate->save();

        return redirect()->route('civil-certificates.index')->with('success', 'Nouveau brouillon d\'acte enregistré avec succès.');
    }

    public function edit(CivilCertificate $civilCertificate)
    {
        Gate::authorize('update', $civilCertificate);

        return Inertia::render('CivilCertificates/Form', [
            'certificate' => $civilCertificate,
            'type' => $civilCertificate->type->value,
            'types' => array_column(CivilCertificateType::cases(), 'value'),
            'is_edit' => true,
        ]);
    }

    public function update(Request $request, CivilCertificate $civilCertificate)
    {
        Gate::authorize('update', $civilCertificate);

        $validatedData = $request->validate([
            'applicant_first_name' => ['required', 'string', 'max:255'],
            'applicant_last_name' => ['required', 'string', 'max:255'],
            'applicant_cni' => ['nullable', 'string', 'max:50'],
            'data' => ['required', 'array'],
        ]);

        $this->validateCertificateData($validatedData['data']);

        $enumType = $civilCertificate->type;

        // 1. SPECIFIC VALIDATION (Same as store)
        if ($enumType === CivilCertificateType::RESIDENCE) {
            $request->validate([
                'data.adresse' => 'required|string',
                'applicant_cni' => 'required|string',
                'data.temoin_1_identite' => 'required|string',
                'data.temoin_2_identite' => 'required|string',
            ], [
                'applicant_cni.required' => 'Le numéro de CNI est obligatoire pour un certificat de résidence.',
                'data.adresse.required' => 'L\'adresse exacte est obligatoire.',
            ]);
        }

        if ($enumType === CivilCertificateType::INDIGENCE) {
            $request->validate([
                'data.ressources_mensuelles' => 'required|numeric',
                'data.composition_foyer' => 'required|string',
            ]);
        }

        if ($enumType === CivilCertificateType::VIE_COLLECTIVE) {
            $request->validate([
                'data.membres_identites' => 'required|string',
                'data.motif' => 'required|string',
            ]);
        }

        if ($enumType === CivilCertificateType::NON_INSCRIT_NAISSANCE) {
            $request->validate([
                'data.date_naissance' => 'required|date',
                'data.periode_recherche' => 'required|string',
            ]);
        }

        if ($enumType === CivilCertificateType::NON_INSCRIT_NAISSANCE) {
            $found = $this->performAutomatedSearch($validatedData['applicant_first_name'], $validatedData['applicant_last_name'], $validatedData['data']['date_naissance'] ?? null);
            // If they changed the name to someone who exists
            if ($found && ($civilCertificate->applicant_first_name !== $validatedData['applicant_first_name'] || $civilCertificate->applicant_last_name !== $validatedData['applicant_last_name'])) {
                 return back()->with('error', 'Modification bloquée : Un acte de naissance correspondant existe déjà dans le registre numérique.');
            }
        }

        $civilCertificate->applicant_first_name = $validatedData['applicant_first_name'];
        $civilCertificate->applicant_last_name = $validatedData['applicant_last_name'];
        $civilCertificate->applicant_cni = $validatedData['applicant_cni'] ?? null;
        $civilCertificate->data = $validatedData['data'];
        
        if ($civilCertificate->status === 'a_corriger') {
            $civilCertificate->status = 'brouillon'; // Revert to draft after correction
        }
        
        $civilCertificate->save();

        $civilCertificate->recordAuditLog('modification_manuelle');

        return redirect()->route('civil-certificates.show', $civilCertificate->id)->with('success', 'L\'acte a été mis à jour avec succès.');
    }

    /**
     * Simulated automated search in the digital register.
     */
    protected function performAutomatedSearch($firstName, $lastName, $dob = null)
    {
        // Use exact match, never LIKE with unsanitized user input
        $query = BirthAct::whereRaw('LOWER(first_name) = ?', [strtolower(trim($firstName))])
            ->whereRaw('LOWER(last_name) = ?', [strtolower(trim($lastName))]);

        if ($dob) {
            $query->whereDate('date_of_birth', $dob);
        }

        return $query->exists();
    }

    public function show(CivilCertificate $civilCertificate)
    {
        $civilCertificate->recordAuditLog('consultation');

        $root = $civilCertificate->parent_id ? $civilCertificate->parent : $civilCertificate;
        $versions = $root->versions()->with('user:id,name')->get();
        if (!$civilCertificate->parent_id && $civilCertificate->versions()->exists()) {
             // If root has versions but we didn't include it in the collection
             $versions->prepend($civilCertificate);
        }

        return Inertia::render('CivilCertificates/Show', [
            'certificate' => $civilCertificate,
            'audit_logs' => $civilCertificate->auditLogs()->with('user:id,name')->get(),
            'versions' => $versions,
            'can' => [
                'update' => Auth::user()->can('update', $civilCertificate),
                'observe' => Auth::user()->can('observe', $civilCertificate),
                'approve' => Auth::user()->can('approve', $civilCertificate),
                'sign' => Auth::user()->can('sign', $civilCertificate),
                'rectify' => Auth::user()->can('rectify', $civilCertificate),
            ]
        ]);
    }

    public function approve(Request $request, CivilCertificate $civilCertificate)
    {
        Gate::authorize('approve', $civilCertificate);
        
        $civilCertificate->status = 'valide_hierarchie';
        $civilCertificate->validated_by = Auth::id();
        $civilCertificate->validated_at = now();
        $civilCertificate->save();

        $civilCertificate->recordAuditLog('validation', ['level' => 'hierarchique']);

        return back()->with('success', 'Acte validé par la hiérarchie. En attente de signature finale par l\'Officier.');
    }

    public function observe(Request $request, CivilCertificate $civilCertificate)
    {
        Gate::authorize('observe', $civilCertificate);

        $request->validate(['comments' => 'required|string']);

        $civilCertificate->status = 'observation';
        $civilCertificate->officer_comments = $request->comments;
        $civilCertificate->save();

        $civilCertificate->recordAuditLog('observation', ['comments' => $request->comments]);

        return back()->with('success', 'Acte mis en observation avec commentaires.');
    }

    public function requestCorrection(Request $request, CivilCertificate $civilCertificate)
    {
        Gate::authorize('observe', $civilCertificate);

        $civilCertificate->status = 'a_corriger';
        $civilCertificate->save();

        $civilCertificate->recordAuditLog('demande_correction');

        return back()->with('success', 'Acte renvoyé à l\'agent pour correction.');
    }

    public function sign(Request $request, CivilCertificate $civilCertificate, DocumentGenerationService $documentService)
    {
        Gate::authorize('sign', $civilCertificate);

        DB::transaction(function() use ($civilCertificate, $documentService) {
            $civilCertificate->status = 'signe';
            $civilCertificate->is_signed = true;
            
            // Generate final QR-secured PDF
            $pdfPath = $documentService->generateCertificatePdf($civilCertificate);
            $civilCertificate->pdf_path = $pdfPath;
            $civilCertificate->save();

            $civilCertificate->recordAuditLog('signature');
        });

        return back()->with('success', 'Acte signé électroniquement, sécurisé par QR Code et archivé définitivement.');
    }

    public function rectify(Request $request, CivilCertificate $civilCertificate)
    {
        Gate::authorize('rectify', $civilCertificate);

        $request->validate([
            'reason' => 'required|string|min:10',
            'data' => 'required|array'
        ]);

        $this->validateCertificateData($request->input('data'));

        return DB::transaction(function() use ($civilCertificate, $request) {
            // 1. Mark current as no longer current (if we want only one active version)
            $civilCertificate->is_current = false;
            $civilCertificate->save();

            // 2. Create New Version
            $newVersion = $civilCertificate->replicate(['uuid', 'pdf_path', 'signature_path', 'is_signed', 'validated_at', 'validated_by']);
            $newVersion->parent_id = $civilCertificate->parent_id ?? $civilCertificate->id;
            $newVersion->version_number = ($civilCertificate->version_number ?? 1) + 1;
            $newVersion->rectification_reason = $request->reason;
            $newVersion->data = $request->data;
            $newVersion->status = 'brouillon'; // New version starts as draft
            $newVersion->is_current = true;
            $newVersion->save();

            $newVersion->recordAuditLog('rectification', [
                'reason' => $request->reason,
                'parent_id' => $civilCertificate->id
            ]);

            return redirect()->route('civil-certificates.show', $newVersion->id)
                ->with('success', 'Nouvelle version de l\'acte créée pour rectification. L\'original est conservé en historique.');
        });
    }

    /**
     * Valide le format et limite la taille/profondeur du JSON soumis dans le champ 'data'.
     */
    protected function validateCertificateData(array $data): void
    {
        if (count($data) > 30) {
            abort(400, "Trop de champs de données soumis.");
        }
        foreach ($data as $key => $value) {
            if (strlen((string)$key) > 100) {
                abort(400, "Nom de champ invalide.");
            }
            if (is_array($value)) {
                if (count($value) > 20) {
                    abort(400, "Structure de données imbriquée trop grande.");
                }
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        abort(400, "Profondeur de données imbriquée non supportée.");
                    }
                    if (strlen((string)$subKey) > 100 || strlen((string)$subValue) > 1000) {
                        abort(400, "Valeurs de données invalides.");
                    }
                }
            } elseif (strlen((string)$value) > 1000) {
                abort(400, "Valeur de champ trop longue.");
            }
        }
    }
}
