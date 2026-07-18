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
        $registries = \App\Models\Registry::where('type', $type)
            ->where('status', 'open')
            ->orderBy('year', 'desc')
            ->orderBy('number', 'asc')
            ->get();

        return Inertia::render('CivilActs/Form', [
            'type' => $type,
            'is_edit' => false,
            'registries' => $registries
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
        
        $registries = \App\Models\Registry::where('type', $type)
            ->where('status', 'open')
            ->orderBy('year', 'desc')
            ->orderBy('number', 'asc')
            ->get();

        return Inertia::render('CivilActs/Form', [
            'act' => $act,
            'type' => $type,
            'is_edit' => true,
            'registries' => $registries
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de créer un acte.");
        }

        $type = $request->route('type');
        $rules = $this->getValidationRules($type);
        
        $isOldRegistry = $request->boolean('is_old_registry');
        if ($isOldRegistry) {
            $rules['reference_number'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules, [
            'file' => 'Le fichier doit être valide.',
            'mimes' => 'Le document doit être au format PDF.',
            'max' => 'La taille du fichier ne doit pas dépasser 500 Ko.',
        ]);

        $centerId = 1;
        if (!\App\Models\CivilRegistrationCenter::where('id', $centerId)->exists()) {
            return back()->with('error', "Le centre d'état civil par défaut (ID {$centerId}) n'existe pas. Veuillez le créer dans l'administration.");
        }
        
        $year = now()->year;
        if ($type === 'naissance' && !empty($validated['date_of_birth'])) {
            $year = date('Y', strtotime($validated['date_of_birth']));
        } elseif ($type === 'mariage' && !empty($validated['marriage_date'])) {
            $year = date('Y', strtotime($validated['marriage_date']));
        } elseif ($type === 'deces' && !empty($validated['date_of_death'])) {
            $year = date('Y', strtotime($validated['date_of_death']));
        }

        $model = $this->getModel($type);
        $registryId = $request->input('registry_id');
        $registry = null;
        if ($registryId) {
            $registry = \App\Models\Registry::find($registryId);
        }

        if (!$registry) {
            // Find the first open registry for this year, type, and center that has < 100 acts
            $registries = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                ->where('type', $type)
                ->where('year', $year)
                ->where('status', 'open')
                ->orderBy('number', 'asc')
                ->get();

            foreach ($registries as $r) {
                $count = $model->where('registry_id', $r->id)->count();
                if ($count < 100) {
                    $registry = $r;
                    break;
                }
            }

            // If no open registry with space is found, create a new one (incrementing the number)
            if (!$registry) {
                // If the latest registry of this year is closed, we cannot create/open a new one automatically
                $latestRegistry = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->orderBy('number', 'desc')
                    ->first();

                if ($latestRegistry && $latestRegistry->status === 'closed') {
                    return back()->withErrors(['registry_id' => 'Le registre pour cette année est fermé.']);
                }

                $nextNumber = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->max('number') + 1;
                if ($nextNumber === 0 || !$nextNumber) {
                    $nextNumber = 1;
                }

                $registry = \App\Models\Registry::create([
                    'civil_registration_center_id' => $centerId,
                    'type' => $type,
                    'year' => $year,
                    'number' => $nextNumber,
                    'status' => 'open',
                    'opening_date' => now(),
                    'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1-' . $nextNumber,
                ]);
            }
        }

        if ($registry->status !== 'open') {
            return back()->withErrors(['registry_id' => 'Le registre pour cette année est fermé.']);
        }

        $actCount = $model->where('registry_id', $registry->id)->count();
        if ($actCount >= 100) {
            return back()->withErrors(['registry_id' => 'Ce registre a atteint sa limite maximale de 100 actes.']);
        }

        $referenceNumbers = $model->where('registry_id', $registry->id)
            ->pluck('reference_number')
            ->toArray();
        $maxIncrement = 0;
        foreach ($referenceNumbers as $ref) {
            $escapedPrefix = preg_quote($registry->reference_prefix, '/');
            if (preg_match('/^' . $escapedPrefix . '-(\d+)$/', $ref, $matches)) {
                $val = intval($matches[1]);
                if ($val > $maxIncrement) {
                    $maxIncrement = $val;
                }
            }
        }
        $increment = $maxIncrement + 1;

        if ($increment > 100 && !($isOldRegistry && !empty($validated['reference_number']))) {
            return back()->withErrors(['registry_id' => 'Ce registre a atteint sa limite maximale de 100 actes.']);
        }

        if ($isOldRegistry && !empty($validated['reference_number'])) {
            $referenceNumber = $validated['reference_number'];

            // Vérifier que ce numéro de référence n'existe pas déjà dans ce registre
            $alreadyExists = $model->where('registry_id', $registry->id)
                ->where('reference_number', $referenceNumber)
                ->exists();

            if ($alreadyExists) {
                return back()->withErrors([
                    'reference_number' => "L'acte « {$referenceNumber} » existe déjà dans ce registre (Volume {$registry->number} — {$registry->year}). Veuillez choisir un autre numéro."
                ]);
            }
        } else {
            $referenceNumber = $registry->reference_prefix . '-' . str_pad($increment, 4, '0', STR_PAD_LEFT);
        }

        // TECHNICAL RULE: Filter out dot-notation keys and transient flags
        $data = array_filter($validated, fn($key) => !str_contains($key, '.') && $key !== 'is_old_registry' && $key !== 'reference_number', ARRAY_FILTER_USE_KEY);
        
        $data = $this->formatTextData($data);

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
        } elseif ($type === 'deces') {
            $docFields = [
                'doc_death_cert'    => 'doc_death_cert_path',
                'doc_deceased_id'   => 'doc_deceased_id_path',
                'doc_declarant_id'  => 'doc_declarant_id_path',
                'doc_jugement'      => 'doc_jugement_path',
                'doc_autres'        => 'doc_autres_path',
            ];
        }
        foreach ($docFields as $fileKey => $pathKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('certificates/pieces', 'public');
                $data[$pathKey] = '/storage/' . $path;
            }
        }

        // Témoins dynamiques (avec CNI)
        if ($type === 'mariage' || $type === 'deces') {
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

        $data['created_by'] = $request->user()->id;
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
        $validated = $request->validate($rules, [
            'file' => 'Le fichier doit être valide.',
            'mimes' => 'Le document doit être au format PDF.',
            'max' => 'La taille du fichier ne doit pas dépasser 500 Ko.',
        ]);

        // TECHNICAL RULE: Filter out dot-notation keys
        $data = array_filter($validated, fn($key) => !str_contains($key, '.'), ARRAY_FILTER_USE_KEY);

        $data = $this->formatTextData($data);

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

            if (!\App\Models\CivilRegistrationCenter::where('id', $centerId)->exists()) {
                return back()->with('error', "Le centre d'état civil spécifié (ID {$centerId}) n'existe pas. Veuillez le créer dans l'administration.");
            }

            $targetRegistries = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                ->where('type', $type)
                ->where('year', $year)
                ->where('status', 'open')
                ->orderBy('number', 'asc')
                ->get();

            $newRegistry = null;
            foreach ($targetRegistries as $r) {
                $count = $model->where('registry_id', $r->id)->count();
                if ($count < 100) {
                    $newRegistry = $r;
                    break;
                }
            }

            if (!$newRegistry) {
                // If the latest registry of this year is closed, we cannot create/open a new one automatically
                $latestRegistry = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->orderBy('number', 'desc')
                    ->first();

                if ($latestRegistry && $latestRegistry->status === 'closed') {
                    return back()->with('error', 'Le registre cible pour cette année est fermé.');
                }

                $nextNumber = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->max('number') + 1;
                if ($nextNumber === 0 || !$nextNumber) {
                    $nextNumber = 1;
                }

                $newRegistry = \App\Models\Registry::create([
                    'civil_registration_center_id' => $centerId,
                    'type' => $type,
                    'year' => $year,
                    'number' => $nextNumber,
                    'status' => 'open',
                    'opening_date' => now(),
                    'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1-' . $nextNumber,
                ]);
            }

            // Generate new reference number for the new registry
            $referenceNumbers = $model->where('registry_id', $newRegistry->id)
                ->pluck('reference_number')
                ->toArray();
            $maxIncrement = 0;
            foreach ($referenceNumbers as $ref) {
                $escapedPrefix = preg_quote($newRegistry->reference_prefix, '/');
                if (preg_match('/^' . $escapedPrefix . '-(\d+)$/', $ref, $matches)) {
                    $val = intval($matches[1]);
                    if ($val > $maxIncrement) {
                        $maxIncrement = $val;
                    }
                }
            }
            $increment = $maxIncrement + 1;
            
            $newRegistryActCount = $model->where('registry_id', $newRegistry->id)->count();
            if ($newRegistryActCount >= 100 || $increment > 100) {
                return back()->with('error', "Le registre cible a atteint sa limite maximale de 100 actes.");
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
        } elseif ($type === 'deces') {
            $docFields = [
                'doc_death_cert'    => 'doc_death_cert_path',
                'doc_deceased_id'   => 'doc_deceased_id_path',
                'doc_declarant_id'  => 'doc_declarant_id_path',
                'doc_jugement'      => 'doc_jugement_path',
                'doc_autres'        => 'doc_autres_path',
            ];
        }
        foreach ($docFields as $fileKey => $pathKey) {
            if ($request->hasFile($fileKey)) {
                $path = $request->file($fileKey)->store('certificates/pieces', 'public');
                $data[$pathKey] = '/storage/' . $path;
            }
        }

        // Témoins dynamiques (avec CNI)
        if ($type === 'mariage' || $type === 'deces') {
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
        $isOldRegistry = request()->boolean('is_old_registry');
        $docRule = ($id || $isOldRegistry) ? 'nullable' : 'required';

        $common = [
            'officer_comments' => 'nullable|string',
            'certificate_file' => 'nullable|file|mimes:pdf|max:500',
            'certificate_path' => 'nullable|string',
        ];

        if ($type === 'naissance') {
            return array_merge($common, [
                'first_name'                              => 'required|string',
                'last_name'                               => 'required|string',
                'date_of_birth'                           => 'required|date',
                'time_of_birth'                           => $isOldRegistry ? 'nullable|date_format:H:i' : 'required|date_format:H:i',
                'place_of_birth'                          => 'required|string',
                'health_facility'                         => $isOldRegistry ? 'nullable|string' : 'required|string',
                'act_registration_date'                   => 'required|date',
                'gender'                                  => 'required|in:M,F',
                'is_judgment'                             => 'nullable|boolean',
                'judgment_number'                         => 'nullable|required_if:is_judgment,true|string',
                'judgment_date'                           => 'nullable|required_if:is_judgment,true|date',
                'judgment_court'                          => 'nullable|required_if:is_judgment,true|string',
                'father_name'                             => 'required|string',
                'mother_name'                             => 'required|string',
                'parents_metadata'                        => 'required|array',
                'parents_metadata.father_profession'      => 'required|string',
                'parents_metadata.father_date_of_birth'   => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $childDob = request()->input('date_of_birth');
                        if ($childDob) {
                            $childDate = \Carbon\Carbon::parse($childDob);
                            $parentDate = \Carbon\Carbon::parse($value);
                            if ($parentDate->diffInYears($childDate, false) < 10) {
                                $fail("L'âge du père doit être supérieur d'au moins 10 ans à celui de l'enfant.");
                            }
                        }
                    }
                ],
                'parents_metadata.father_place_of_birth'  => 'required|string',
                'parents_metadata.father_domicile'        => 'required|string',
                'parents_metadata.mother_profession'      => 'required|string',
                'parents_metadata.mother_date_of_birth'   => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        $childDob = request()->input('date_of_birth');
                        if ($childDob) {
                            $childDate = \Carbon\Carbon::parse($childDob);
                            $parentDate = \Carbon\Carbon::parse($value);
                            if ($parentDate->diffInYears($childDate, false) < 10) {
                                $fail("L'âge de la mère doit être supérieur d'au moins 10 ans à celui de l'enfant.");
                            }
                        }
                    }
                ],
                'parents_metadata.mother_place_of_birth'  => 'required|string',
                'parents_metadata.mother_domicile'        => 'required|string',
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
                'doc_cni_pere'                            => $docRule . '|file|mimes:pdf|max:500',
                'doc_cni_mere'                            => $docRule . '|file|mimes:pdf|max:500',
                'doc_acte_naissance'                      => $docRule . '|file|mimes:pdf|max:500',
                'doc_cni_declarant'                       => $docRule . '|file|mimes:pdf|max:500',
                'doc_autres'                              => 'nullable|file|mimes:pdf|max:500',
                'doc_jugement'                            => ($id || $isOldRegistry ? 'nullable' : 'nullable|required_if:is_judgment,true') . '|file|mimes:pdf|max:500',
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
                'spouses_metadata'                             => 'required|array',
                'spouses_metadata.husband_date_of_birth'       => 'required|date',
                'spouses_metadata.husband_place_of_birth'      => 'required|string',
                'spouses_metadata.husband_profession'          => 'required|string',
                'spouses_metadata.husband_domicile'            => 'required|string',
                'spouses_metadata.husband_residence'           => 'required|string',
                'spouses_metadata.husband_married_to'          => 'nullable|string',
                'spouses_metadata.wife_date_of_birth'          => 'required|date',
                'spouses_metadata.wife_place_of_birth'         => 'required|string',
                'spouses_metadata.wife_profession'             => 'required|string',
                'spouses_metadata.wife_domicile'               => 'required|string',
                'spouses_metadata.wife_residence'              => 'required|string',
                // Husband Parents
                'spouses_metadata.husband_father_first_name'   => 'required|string',
                'spouses_metadata.husband_father_last_name'    => 'required|string',
                'spouses_metadata.husband_father_date_of_birth'=> 'required|date',
                'spouses_metadata.husband_father_profession'   => 'required|string',
                'spouses_metadata.husband_father_domicile'     => 'required|string',
                'spouses_metadata.husband_mother_first_name'   => 'required|string',
                'spouses_metadata.husband_mother_last_name'    => 'required|string',
                'spouses_metadata.husband_mother_date_of_birth'=> 'required|date',
                'spouses_metadata.husband_mother_profession'   => 'required|string',
                'spouses_metadata.husband_mother_domicile'     => 'required|string',
                // Wife Parents
                'spouses_metadata.wife_father_first_name'      => 'required|string',
                'spouses_metadata.wife_father_last_name'       => 'required|string',
                'spouses_metadata.wife_father_date_of_birth'   => 'required|date',
                'spouses_metadata.wife_father_profession'      => 'required|string',
                'spouses_metadata.wife_father_domicile'        => 'required|string',
                'spouses_metadata.wife_mother_first_name'      => 'required|string',
                'spouses_metadata.wife_mother_last_name'       => 'required|string',
                'spouses_metadata.wife_mother_date_of_birth'   => 'required|date',
                'spouses_metadata.wife_mother_profession'      => 'required|string',
                'spouses_metadata.wife_mother_domicile'        => 'required|string',
                // Max wives limit
                'spouses_metadata.max_wives'                   => 'nullable|string',
                // Witnesses (dynamic)
                'witnesses_metadata'                           => 'nullable|array',
                'witnesses_metadata.*.first_name'              => 'nullable|string',
                'witnesses_metadata.*.last_name'               => 'nullable|string',
                'witnesses_metadata.*.profession'              => 'nullable|string',
                'witnesses_metadata.*.address'                 => 'nullable|string',
                'witnesses_metadata.*.id_number'               => 'nullable|string',
                'witnesses_metadata.*.cni_file'                => 'nullable|file|mimes:pdf|max:500',
                // Documents PDF separate
                'doc_cni_husband'                              => $docRule . '|file|mimes:pdf|max:500',
                'doc_cni_wife'                                 => $docRule . '|file|mimes:pdf|max:500',
                'doc_birth_husband'                            => $docRule . '|file|mimes:pdf|max:500',
                'doc_birth_wife'                               => $docRule . '|file|mimes:pdf|max:500',
                'doc_domicile'                                 => $docRule . '|file|mimes:pdf|max:500',
                'doc_medical'                                  => $docRule . '|file|mimes:pdf|max:500',
                'doc_parental_auth'                            => 'nullable|file|mimes:pdf|max:500',
                'doc_jugement'                                 => ($id || $isOldRegistry ? 'nullable' : 'nullable|required_if:is_judgment,true') . '|file|mimes:pdf|max:500',
                'doc_autres'                                   => 'nullable|file|mimes:pdf|max:500',
            ]);
        }

        if ($type === 'deces') {
            return array_merge($common, [
                'deceased_first_name'                       => 'required|string',
                'deceased_last_name'                        => 'required|string',
                'gender'                                    => 'required|in:M,F',
                'date_of_birth'                             => 'required|date',
                'date_of_death'                             => 'required|date',
                'time_of_death'                             => 'required|date_format:H:i',
                'place_of_death'                            => 'required|string',
                'health_facility'                           => 'required|string',
                'act_registration_date'                     => 'required|date',
                'cause_of_death'                            => 'nullable|string',
                'is_judgment'                               => 'nullable|boolean',
                'judgment_number'                           => 'nullable|required_if:is_judgment,true|string',
                'judgment_date'                             => 'nullable|required_if:is_judgment,true|date',
                'judgment_court'                            => 'nullable|required_if:is_judgment,true|string',
                // Death Metadata JSON
                'death_metadata'                            => 'required|array',
                'death_metadata.time_of_birth'              => 'nullable|string',
                'death_metadata.place_of_birth'             => 'required|string',
                'death_metadata.profession'                 => 'required|string',
                'death_metadata.domicile'                   => 'required|string',
                'death_metadata.marital_status'             => 'required|string',
                'death_metadata.previously_married_to'      => 'nullable|string',
                // Parents of deceased
                'death_metadata.father_first_name'          => 'required|string',
                'death_metadata.father_last_name'           => 'required|string',
                'death_metadata.father_date_of_birth'       => 'required|date',
                'death_metadata.father_profession'          => 'required|string',
                'death_metadata.father_domicile'            => 'required|string',
                'death_metadata.mother_first_name'          => 'required|string',
                'death_metadata.mother_last_name'           => 'required|string',
                'death_metadata.mother_date_of_birth'       => 'required|date',
                'death_metadata.mother_profession'          => 'required|string',
                'death_metadata.mother_domicile'            => 'required|string',
                // Declarant
                'death_metadata.declarant_first_name'       => 'required|string',
                'death_metadata.declarant_last_name'        => 'required|string',
                'death_metadata.declarant_profession'       => 'required|string',
                'death_metadata.declarant_address'          => 'required|string',
                'death_metadata.declarant_relationship'     => 'required|string',
                'death_metadata.declarant_id_number'        => 'required|string',
                'death_metadata.declarant_date_time'        => 'required|string',
                // Witnesses (dynamic)
                'witnesses_metadata'                        => 'nullable|array',
                'witnesses_metadata.*.first_name'           => 'nullable|string',
                'witnesses_metadata.*.last_name'            => 'nullable|string',
                'witnesses_metadata.*.profession'           => 'nullable|string',
                'witnesses_metadata.*.address'              => 'nullable|string',
                'witnesses_metadata.*.id_number'            => 'nullable|string',
                'witnesses_metadata.*.cni_file'             => 'nullable|file|mimes:pdf|max:500',
                // Documents PDF separate
                'doc_death_cert'                            => $docRule . '|file|mimes:pdf|max:500',
                'doc_deceased_id'                           => $docRule . '|file|mimes:pdf|max:500',
                'doc_declarant_id'                          => $docRule . '|file|mimes:pdf|max:500',
                'doc_jugement'                              => ($id || $isOldRegistry ? 'nullable' : 'nullable|required_if:is_judgment,true') . '|file|mimes:pdf|max:500',
                'doc_autres'                                => 'nullable|file|mimes:pdf|max:500',
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

        // Technical Rule: Valideur is Officier or Superviseur. Signer is Maire.
        if (in_array($newStatus, ['valide', 'rejete', 'a_corriger']) && !$isAdmin && !$user->hasRole(\App\Enums\UserRole::OFFICIER->value) && !$user->hasRole(\App\Enums\UserRole::SUPERVISEUR->value)) {
            abort(403, 'Seul un Officier d\'état-civil ou un Superviseur peut valider ou rejeter un acte.');
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

        // Notifier l'agent émetteur si renvoyé à la correction
        if ($newStatus === 'a_corriger' && $act->creator) {
            $act->creator->notify(new \App\Notifications\ActReturnedForCorrection(
                $type,
                $act->id,
                $act->reference_number
            ));
        }

        return back()->with('success', 'Statut de l\'acte mis à jour avec succès : ' . strtoupper($newStatus));
    }

    private function formatTextData(array $data): array
    {
        $excludeKeys = ['reference_number', 'officer_comments', 'certificate_path', 'judgment_number', 'gender', 'marriage_option', 'matrimonial_regime', 'marital_status', 'judgment_court', 'cause_of_death'];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $isName = str_ends_with($key, 'last_name') || str_contains($key, 'last_name') || $key === 'nom';
                
                if ($isName) {
                    $data[$key] = mb_strtoupper($value, 'UTF-8');
                } else {
                    if (!in_array($key, $excludeKeys) && !preg_match('/_date|_time|_id|doc_|^is_/', $key)) {
                        $data[$key] = mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    }
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->formatTextData($value);
            }
        }
        return $data;
    }
}
