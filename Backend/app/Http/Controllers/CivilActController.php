<?php

namespace App\Http\Controllers;

use App\Models\BirthAct;
use App\Models\MarriageAct;
use App\Models\DeathAct;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class CivilActController extends Controller
{
    protected function getModel(string $type)
    {
        return match ($type) {
            'naissance' => new BirthAct(),
            'mariage' => new MarriageAct(),
            'deces' => new DeathAct(),
            default => throw new \InvalidArgumentException("Type d'acte invalide"),
        };
    }

    public function index(Request $request)
    {
        if (!$request->user()->hasPermissionTo('view-registries')) {
            abort(403, "Vous n'avez pas la permission de voir les registres.");
        }

        $type = $request->route('type') ?? $request->route()->getAction('type');
        $model = $this->getModel($type);
        
        $acts = $model->where('is_current', true)
            ->with(['registry'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('CivilActs/Index', [
            'acts' => $acts,
            'type' => $type
        ]);
    }

    public function show(Request $request, $id)
    {
        if (!$request->user()->hasPermissionTo('view-registries')) {
            abort(403, "Vous n'avez pas la permission de voir les détails de l'acte.");
        }

        $type = $request->route('type') ?? $request->route()->getAction('type');
        $model = $this->getModel($type);
        $act = $model->with(['registry', 'validator'])->findOrFail($id);

        return Inertia::render('CivilActs/Show', [
            'act' => $act,
            'type' => $type,
            'versions' => $act->versions()
        ]);
    }

    public function create(Request $request)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de créer un acte.");
        }

        $type = $request->route('type');
        return Inertia::render('CivilActs/Form', [
            'type' => $type,
            'is_edit' => false
        ]);
    }

    public function edit(Request $request, $id)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de modifier cet acte.");
        }

        $type = $request->route('type');
        $model = $this->getModel($type);
        $act = $model->findOrFail($id);

        return Inertia::render('CivilActs/Form', [
            'act' => $act,
            'type' => $type,
            'is_edit' => true
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de créer un acte.");
        }

        $type = $request->route('type');
        $rules = $this->getValidationRules($type);
        $validated = $request->validate($rules);

        $centerId = 1;
        
        $year = now()->year;
        if ($type === 'naissance' && !empty($validated['date_of_birth'])) {
            $year = date('Y', strtotime($validated['date_of_birth']));
        } elseif ($type === 'mariage' && !empty($validated['marriage_date'])) {
            $year = date('Y', strtotime($validated['marriage_date']));
        } elseif ($type === 'deces' && !empty($validated['date_of_death'])) {
            $year = date('Y', strtotime($validated['date_of_death']));
        }

        $registry = \App\Models\Registry::firstOrCreate(
            [
                'civil_registration_center_id' => $centerId,
                'type' => $type,
                'year' => $year,
            ],
            [
                'status' => 'open',
                'opening_date' => now(),
                'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1',
            ]
        );

        if ($registry->status !== 'open') {
            return back()->withErrors(['registry_id' => 'Le registre pour cette année est fermé.']);
        }

        $model = $this->getModel($type);
        $lastAct = $model->where('registry_id', $registry->id)->latest('id')->first();
        
        $increment = 1;
        if ($lastAct && preg_match('/-(\d+)$/', $lastAct->reference_number, $matches)) {
            $increment = intval($matches[1]) + 1;
        }
        
        $referenceNumber = $registry->reference_prefix . '-' . str_pad($increment, 4, '0', STR_PAD_LEFT);

        // TECHNICAL RULE: Filter out dot-notation keys
        $data = array_filter($validated, fn($key) => !str_contains($key, '.'), ARRAY_FILTER_USE_KEY);
        
        $data['registry_id'] = $registry->id;
        $data['reference_number'] = $referenceNumber;

        if ($request->hasFile('certificate_file')) {
            $path = $request->file('certificate_file')->store('certificates', 'public');
            $data['certificate_path'] = '/storage/' . $path;
        }

        // Pièces justificatives par catégorie
        $docFields = [];
        if ($type === 'naissance') {
            $docFields = [
                'doc_cni_pere'       => 'doc_cni_pere_path',
                'doc_cni_mere'       => 'doc_cni_mere_path',
                'doc_acte_naissance' => 'doc_acte_naissance_path',
                'doc_cni_declarant'  => 'doc_cni_declarant_path',
                'doc_autres'         => 'doc_autres_path',
                'doc_jugement'       => 'doc_jugement_path',
            ];
        } elseif ($type === 'mariage') {
            $docFields = [
                'doc_cni_husband'    => 'doc_cni_husband_path',
                'doc_cni_wife'       => 'doc_cni_wife_path',
                'doc_birth_husband'  => 'doc_birth_husband_path',
                'doc_birth_wife'     => 'doc_birth_wife_path',
                'doc_domicile'       => 'doc_domicile_path',
                'doc_medical'        => 'doc_medical_path',
                'doc_parental_auth'  => 'doc_parental_auth_path',
                'doc_jugement'       => 'doc_jugement_path',
                'doc_autres'         => 'doc_autres_path',
            ];
        }
        foreach ($docFields as $fileKey => $pathKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('certificates/pieces', 'public');
                $data[$pathKey] = '/storage/' . $path;
            }
        }

        // Témoins dynamiques mariage (avec CNI)
        if ($type === 'mariage') {
            $witnesses = $request->input('witnesses_metadata', []);
            if (is_array($witnesses)) {
                foreach ($witnesses as $index => &$witness) {
                    if ($request->hasFile("witnesses_metadata.{$index}.cni_file")) {
                        $path = $request->file("witnesses_metadata.{$index}.cni_file")->store('certificates/pieces', 'public');
                        $witness['doc_cni_path'] = '/storage/' . $path;
                    }
                    unset($witness['cni_file']);
                }
                $data['witnesses_metadata'] = $witnesses;
            }
        }

        $act = $model->create($data);

        return redirect()->route("acts.{$type}.show", $act->id)
            ->with('success', 'Acte enregistré avec succès.');
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de modifier cet acte.");
        }

        $type = $request->route('type');
        $model = $this->getModel($type);
        $act = $model->with('registry')->findOrFail($id);

        $rules = $this->getValidationRules($type, $id);
        $validated = $request->validate($rules);

        // TECHNICAL RULE: Filter out dot-notation keys
        $data = array_filter($validated, fn($key) => !str_contains($key, '.'), ARRAY_FILTER_USE_KEY);

        // Calculate year based on validated dates
        $year = $act->registry ? $act->registry->year : now()->year;
        if ($type === 'naissance' && !empty($validated['date_of_birth'])) {
            $year = date('Y', strtotime($validated['date_of_birth']));
        } elseif ($type === 'mariage' && !empty($validated['marriage_date'])) {
            $year = date('Y', strtotime($validated['marriage_date']));
        } elseif ($type === 'deces' && !empty($validated['date_of_death'])) {
            $year = date('Y', strtotime($validated['date_of_death']));
        }

        // If the year of the event has been modified, we must reassign to the correct registry
        if ($act->registry && $act->registry->year != $year) {
            $centerId = $act->registry->civil_registration_center_id ?? 1;

            $newRegistry = \App\Models\Registry::firstOrCreate(
                [
                    'civil_registration_center_id' => $centerId,
                    'type' => $type,
                    'year' => $year,
                ],
                [
                    'status' => 'open',
                    'opening_date' => now(),
                    'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1',
                ]
            );

            // Generate new reference number for the new registry
            $lastAct = $model->where('registry_id', $newRegistry->id)->latest('id')->first();
            $increment = 1;
            if ($lastAct && preg_match('/-(\d+)$/', $lastAct->reference_number, $matches)) {
                $increment = intval($matches[1]) + 1;
            }
            
            $referenceNumber = $newRegistry->reference_prefix . '-' . str_pad($increment, 4, '0', STR_PAD_LEFT);

            $data['registry_id'] = $newRegistry->id;
            $data['reference_number'] = $referenceNumber;
        }

        if ($request->hasFile('certificate_file')) {
            $path = $request->file('certificate_file')->store('certificates', 'public');
            $data['certificate_path'] = '/storage/' . $path;
        }

        // Pièces justificatives par catégorie
        $docFields = [];
        if ($type === 'naissance') {
            $docFields = [
                'doc_cni_pere'       => 'doc_cni_pere_path',
                'doc_cni_mere'       => 'doc_cni_mere_path',
                'doc_acte_naissance' => 'doc_acte_naissance_path',
                'doc_cni_declarant'  => 'doc_cni_declarant_path',
                'doc_autres'         => 'doc_autres_path',
                'doc_jugement'       => 'doc_jugement_path',
            ];
        } elseif ($type === 'mariage') {
            $docFields = [
                'doc_cni_husband'    => 'doc_cni_husband_path',
                'doc_cni_wife'       => 'doc_cni_wife_path',
                'doc_birth_husband'  => 'doc_birth_husband_path',
                'doc_birth_wife'     => 'doc_birth_wife_path',
                'doc_domicile'       => 'doc_domicile_path',
                'doc_medical'        => 'doc_medical_path',
                'doc_parental_auth'  => 'doc_parental_auth_path',
                'doc_jugement'       => 'doc_jugement_path',
                'doc_autres'         => 'doc_autres_path',
            ];
        }
        foreach ($docFields as $fileKey => $pathKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('certificates/pieces', 'public');
                $data[$pathKey] = '/storage/' . $path;
            }
        }

        // Témoins dynamiques mariage (avec CNI)
        if ($type === 'mariage') {
            $witnesses = $request->input('witnesses_metadata', []);
            if (is_array($witnesses)) {
                foreach ($witnesses as $index => &$witness) {
                    if ($request->hasFile("witnesses_metadata.{$index}.cni_file")) {
                        $path = $request->file("witnesses_metadata.{$index}.cni_file")->store('certificates/pieces', 'public');
                        $witness['doc_cni_path'] = '/storage/' . $path;
                    }
                    unset($witness['cni_file']);
                }
                $data['witnesses_metadata'] = $witnesses;
            }
        }

        $act->update($data);

        return redirect()->route("acts.{$type}.show", $act->id)
            ->with('success', 'Acte mis à jour avec succès.');
    }

    protected function getValidationRules(string $type, $id = null): array
    {
        $common = [
            'officer_comments' => 'nullable|string',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'certificate_path' => 'nullable|string',
        ];

        if ($type === 'naissance') {
            return array_merge($common, [
                'first_name'                              => 'required|string',
                'last_name'                               => 'required|string',
                'date_of_birth'                           => 'required|date',
                'time_of_birth'                           => 'nullable|date_format:H:i',
                'place_of_birth'                          => 'required|string',
                'health_facility'                         => 'nullable|string',
                'act_registration_date'                   => 'nullable|date',
                'gender'                                  => 'required|in:M,F',
                'is_judgment'                             => 'nullable|boolean',
                'judgment_number'                         => 'nullable|required_if:is_judgment,true|string',
                'judgment_date'                           => 'nullable|required_if:is_judgment,true|date',
                'judgment_court'                          => 'nullable|required_if:is_judgment,true|string',
                'father_name'                             => 'nullable|string',
                'mother_name'                             => 'nullable|string',
                'parents_metadata'                        => 'nullable|array',
                'parents_metadata.father_profession'      => 'nullable|string',
                'parents_metadata.father_date_of_birth'   => 'nullable|date',
                'parents_metadata.father_place_of_birth'  => 'nullable|string',
                'parents_metadata.father_domicile'        => 'nullable|string',
                'parents_metadata.mother_profession'      => 'nullable|string',
                'parents_metadata.mother_date_of_birth'   => 'nullable|date',
                'parents_metadata.mother_place_of_birth'  => 'nullable|string',
                'parents_metadata.mother_domicile'        => 'nullable|string',
                // Section Déclarant
                'parents_metadata.declarant_first_name'   => 'nullable|string',
                'parents_metadata.declarant_last_name'    => 'nullable|string',
                'parents_metadata.declarant_profession'   => 'nullable|string',
                'parents_metadata.declarant_address'      => 'nullable|string',
                'parents_metadata.declarant_id_number'    => 'nullable|string',
                'parents_metadata.declarant_date'         => 'nullable|date',
                'parents_metadata.declarant_judgment_ref' => 'nullable|string',
                // Section Jugement (si déclaration sur jugement)
                'parents_metadata.judgment_auth_date'     => 'nullable|date',
                'parents_metadata.judgment_auth_ref'      => 'nullable|string',
                // Section Témoins (0 à 2)
                'parents_metadata.witnesses'               => 'nullable|array',
                'parents_metadata.witnesses.*.first_name'  => 'nullable|string',
                'parents_metadata.witnesses.*.last_name'   => 'nullable|string',
                'parents_metadata.witnesses.*.date_of_birth' => 'nullable|date',
                'parents_metadata.witnesses.*.place_of_birth' => 'nullable|string',
                'parents_metadata.witnesses.*.profession'  => 'nullable|string',
                'parents_metadata.witnesses.*.address'     => 'nullable|string',
                'parents_metadata.witnesses.*.id_number'   => 'nullable|string',
                // Pièces justificatives PDF
                'doc_cni_pere'                            => 'nullable|file|mimes:pdf|max:10240',
                'doc_cni_mere'                            => 'nullable|file|mimes:pdf|max:10240',
                'doc_acte_naissance'                      => 'nullable|file|mimes:pdf|max:10240',
                'doc_cni_declarant'                       => 'nullable|file|mimes:pdf|max:10240',
                'doc_autres'                              => 'nullable|file|mimes:pdf|max:10240',
                'doc_jugement'                            => 'nullable|file|mimes:pdf|max:10240',
            ]);
        }

        if ($type === 'mariage') {
            return array_merge($common, [
                'husband_first_name'                           => 'required|string',
                'husband_last_name'                            => 'required|string',
                'wife_first_name'                              => 'required|string',
                'wife_last_name'                               => 'required|string',
                'marriage_date'                                => 'required|date',
                'marriage_place'                               => 'required|string',
                'marriage_option'                              => 'nullable|string',
                'matrimonial_regime'                           => 'nullable|string',
                'is_judgment'                                  => 'nullable|boolean',
                'judgment_number'                              => 'nullable|required_if:is_judgment,true|string',
                'judgment_date'                                => 'nullable|required_if:is_judgment,true|date',
                // Spouses Metadata JSON
                'spouses_metadata'                             => 'nullable|array',
                'spouses_metadata.husband_date_of_birth'       => 'nullable|date',
                'spouses_metadata.husband_place_of_birth'      => 'nullable|string',
                'spouses_metadata.husband_profession'          => 'nullable|string',
                'spouses_metadata.husband_domicile'            => 'nullable|string',
                'spouses_metadata.husband_residence'           => 'nullable|string',
                'spouses_metadata.husband_married_to'          => 'nullable|string',
                'spouses_metadata.wife_date_of_birth'          => 'nullable|date',
                'spouses_metadata.wife_place_of_birth'         => 'nullable|string',
                'spouses_metadata.wife_profession'             => 'nullable|string',
                'spouses_metadata.wife_domicile'               => 'nullable|string',
                'spouses_metadata.wife_residence'              => 'nullable|string',
                // Husband Parents
                'spouses_metadata.husband_father_first_name'   => 'nullable|string',
                'spouses_metadata.husband_father_last_name'    => 'nullable|string',
                'spouses_metadata.husband_father_date_of_birth'=> 'nullable|date',
                'spouses_metadata.husband_father_profession'   => 'nullable|string',
                'spouses_metadata.husband_father_domicile'     => 'nullable|string',
                'spouses_metadata.husband_mother_first_name'   => 'nullable|string',
                'spouses_metadata.husband_mother_last_name'    => 'nullable|string',
                'spouses_metadata.husband_mother_date_of_birth'=> 'nullable|date',
                'spouses_metadata.husband_mother_profession'   => 'nullable|string',
                'spouses_metadata.husband_mother_domicile'     => 'nullable|string',
                // Wife Parents
                'spouses_metadata.wife_father_first_name'      => 'nullable|string',
                'spouses_metadata.wife_father_last_name'       => 'nullable|string',
                'spouses_metadata.wife_father_date_of_birth'   => 'nullable|date',
                'spouses_metadata.wife_father_profession'      => 'nullable|string',
                'spouses_metadata.wife_father_domicile'        => 'nullable|string',
                'spouses_metadata.wife_mother_first_name'      => 'nullable|string',
                'spouses_metadata.wife_mother_last_name'       => 'nullable|string',
                'spouses_metadata.wife_mother_date_of_birth'   => 'nullable|date',
                'spouses_metadata.wife_mother_profession'      => 'nullable|string',
                'spouses_metadata.wife_mother_domicile'        => 'nullable|string',
                // Max wives limit
                'spouses_metadata.max_wives'                   => 'nullable|string',
                // Witnesses (dynamic)
                'witnesses_metadata'                           => 'nullable|array',
                'witnesses_metadata.*.first_name'              => 'nullable|string',
                'witnesses_metadata.*.last_name'               => 'nullable|string',
                'witnesses_metadata.*.profession'              => 'nullable|string',
                'witnesses_metadata.*.address'                 => 'nullable|string',
                'witnesses_metadata.*.id_number'               => 'nullable|string',
                'witnesses_metadata.*.cni_file'                => 'nullable|file|mimes:pdf|max:10240',
                // Documents PDF separate
                'doc_cni_husband'                              => 'nullable|file|mimes:pdf|max:10240',
                'doc_cni_wife'                                 => 'nullable|file|mimes:pdf|max:10240',
                'doc_birth_husband'                            => 'nullable|file|mimes:pdf|max:10240',
                'doc_birth_wife'                               => 'nullable|file|mimes:pdf|max:10240',
                'doc_domicile'                                 => 'nullable|file|mimes:pdf|max:10240',
                'doc_medical'                                  => 'nullable|file|mimes:pdf|max:10240',
                'doc_parental_auth'                            => 'nullable|file|mimes:pdf|max:10240',
                'doc_jugement'                                 => 'nullable|file|mimes:pdf|max:10240',
                'doc_autres'                                   => 'nullable|file|mimes:pdf|max:10240',
            ]);
        }

        if ($type === 'deces') {
            return array_merge($common, [
                'deceased_first_name' => 'required|string',
                'deceased_last_name' => 'required|string',
                'date_of_death' => 'required|date',
                'place_of_death' => 'required|string',
                'cause_of_death' => 'nullable|string',
            ]);
        }

        return [];
    }

    public function updateStatus(Request $request, $id)
    {
        $type = $request->route('type');
        $model = $this->getModel($type);
        $act = $model->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:valide,rejete,a_corriger,signe'
        ]);

        $newStatus = $validated['status'];
        $user = $request->user();

        // Let Administrator bypass role restrictions for system maintenance and Q&A.
        $isAdmin = $user->hasRole(\App\Enums\UserRole::ADMIN->value);

        // Technical Rule: Valideur is Officier. Signer is Maire.
        if (in_array($newStatus, ['valide', 'rejete', 'a_corriger']) && !$isAdmin && !$user->hasRole(\App\Enums\UserRole::OFFICIER->value)) {
            abort(403, 'Seul un Officier d\'état-civil peut valider ou rejeter un acte.');
        }

        if ($newStatus === 'signe' && !$isAdmin && !$user->hasRole(\App\Enums\UserRole::MAIRE->value)) {
            abort(403, 'Seul le Maire peut signer définitivement un acte.');
        }

        if ($act->status === 'signe') {
            return back()->with('error', 'Cet acte est déjà signé et ne peut plus modifier son statut.');
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'valide' || $newStatus === 'signe') {
            $updateData['validated_by'] = $user->id;
            $updateData['validated_at'] = now();
        }

        if ($newStatus === 'signe') {
            $updateData['locked_at'] = now();
            $updateData['is_locked'] = true;
        }

        // Use direct DB update or unrestricted query to bypass Fillable protection securely
        $model::where('id', $act->id)->update($updateData);

        return back()->with('success', 'Statut de l\'acte mis à jour avec succès : ' . strtoupper($newStatus));
    }
}
