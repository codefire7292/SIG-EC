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

    public function hub(Request $request)
    {
        $type = $request->route('type') ?? $request->route()->getAction('type');
        $model = $this->getModel($type);
        $year = now()->year;

        $totalActs = $model->where('is_current', true)->count();

        $actsThisYear = $model->where('is_current', true)
            ->whereYear('created_at', $year)
            ->count();

        $pendingActs = $model->where('is_current', true)
            ->where('status', '!=', 'signe')
            ->count();

        $pendingActsThisYear = $model->where('is_current', true)
            ->where('status', '!=', 'signe')
            ->whereYear('created_at', $year)
            ->count();

        $openRegistries = \App\Models\Registry::where('type', $type)
            ->where('status', 'open')
            ->count();

        $totalRegistries = \App\Models\Registry::where('type', $type)->count();

        $totalCertificates = \App\Models\CivilCertificate::count();

        $certificatesThisYear = \App\Models\CivilCertificate::whereYear('created_at', $year)->count();

        return Inertia::render('CivilActs/Hub', [
            'type'  => $type,
            'stats' => [
                'total_acts'             => $totalActs,
                'acts_this_year'         => $actsThisYear,
                'pending_acts'           => $pendingActs,
                'pending_acts_this_year' => $pendingActsThisYear,
                'open_registries'        => $openRegistries,
                'total_registries'       => $totalRegistries,
                'total_certificates'     => $totalCertificates,
                'certificates_this_year' => $certificatesThisYear,
            ],
        ]);
    }

    public function registries(Request $request)
    {
        $type = $request->route('type') ?? $request->route()->getAction('type');
        $model = $this->getModel($type);

        $registries = \App\Models\Registry::where('type', $type)
            ->with('center')
            ->orderBy('year', 'desc')
            ->orderBy('number', 'asc')
            ->get()
            ->map(function ($registry) use ($model) {
                $registry->acts_count = $model->where('registry_id', $registry->id)
                    ->where('status', 'signe')
                    ->count();
                return $registry;
            });

        return Inertia::render('CivilActs/Registries', [
            'type'       => $type,
            'registries' => $registries,
        ]);
    }

    public function index(Request $request)
    {
        if (!$request->user()->hasPermissionTo('view-registries')) {
            abort(403, "Vous n'avez pas la permission de voir les registres.");
        }

        $type = $request->route('type') ?? $request->route()->getAction('type');
        $model = $this->getModel($type);

        $query = $model->where('is_current', true)->with(['registry.center']);

        $activeRegistry = null;
        $registryId = $request->query('registry_id');
        $year = $request->query('year');
        $volumeNumber = $request->query('volume_number');
        $actNumber = $request->query('act_number');
        $status = $request->query('status');
        $sortBy = $request->query('sort_by');
        $sortOrder = strtolower($request->query('sort_order', ''));

        // Handle Active Registry
        if ($registryId) {
            $query->where('registry_id', $registryId);
            $activeRegistry = \App\Models\Registry::with('center')->find($registryId);

            // In register volume view, signed acts are displayed by default unless another status is chosen
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            } elseif (!$status) {
                $query->where('status', '=', 'signe');
            }
        } else {
            // Filter by year if given
            if ($year && $year !== 'all') {
                $query->whereHas('registry', function ($q) use ($year) {
                    $q->where('year', $year);
                });
            }

            // Filter by volume number if given
            if ($volumeNumber && $volumeNumber !== 'all') {
                $query->whereHas('registry', function ($q) use ($volumeNumber) {
                    $q->where('number', $volumeNumber);
                });
            }

            // Status handling when outside specific registry
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            } elseif (!$status) {
                // Default: non-signed acts in declarations list
                $query->where('status', '!=', 'signe');
            }
        }

        // Search by act number specifically ("numéro par numéro")
        if ($actNumber) {
            $cleanActNum = trim($actNumber);
            $padded = str_pad($cleanActNum, 4, '0', STR_PAD_LEFT);
            $query->where(function ($q) use ($cleanActNum, $padded) {
                $q->where('reference_number', 'LIKE', '%' . $padded)
                  ->orWhere('reference_number', 'LIKE', '%-' . $cleanActNum);
            });
        }

        // Global search (text or reference)
        $search = $request->query('search');
        if ($search) {
            $searchTerm = '%' . mb_strtolower(trim($search), 'UTF-8') . '%';
            $query->where(function ($q) use ($searchTerm, $type) {
                $q->whereRaw('LOWER(reference_number) LIKE ?', [$searchTerm]);
                if ($type === 'naissance') {
                    $q->orWhereRaw('LOWER(first_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', [$searchTerm]);
                } elseif ($type === 'mariage') {
                    $q->orWhereRaw('LOWER(husband_first_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(husband_last_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(wife_first_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(wife_last_name) LIKE ?', [$searchTerm]);
                } elseif ($type === 'deces') {
                    $q->orWhereRaw('LOWER(deceased_first_name) LIKE ?', [$searchTerm])
                      ->orWhereRaw('LOWER(deceased_last_name) LIKE ?', [$searchTerm]);
                }
            });
        }

        // Sorting system
        if (!$sortBy) {
            // Default sort: when checking a registry volume, sort by number ascending to verify act-by-act
            $sortBy = $activeRegistry ? 'number' : 'created_at';
            if (empty($sortOrder)) {
                $sortOrder = $activeRegistry ? 'asc' : 'desc';
            }
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = ($sortBy === 'number' || $sortBy === 'reference') ? 'asc' : 'desc';
        }

        switch ($sortBy) {
            case 'number':
            case 'reference':
                $query->orderBy('reference_number', $sortOrder);
                break;
            case 'date':
                $dateCol = match ($type) {
                    'naissance' => 'date_of_birth',
                    'mariage' => 'marriage_date',
                    'deces' => 'date_of_death',
                    default => 'created_at',
                };
                $query->orderBy($dateCol, $sortOrder);
                break;
            case 'name':
                if ($type === 'naissance') {
                    $query->orderBy('last_name', $sortOrder)->orderBy('first_name', $sortOrder);
                } elseif ($type === 'mariage') {
                    $query->orderBy('husband_last_name', $sortOrder);
                } elseif ($type === 'deces') {
                    $query->orderBy('deceased_last_name', $sortOrder);
                }
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $acts = $query->paginate(15)->withQueryString();

        // Metadata for fast navigation and filtering in UI
        $availableYears = \App\Models\Registry::where('type', $type)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $availableRegistries = \App\Models\Registry::where('type', $type)
            ->orderBy('year', 'desc')
            ->orderBy('number', 'asc')
            ->get(['id', 'year', 'number', 'reference_prefix', 'status']);

        $siblingRegistries = [];
        if ($activeRegistry) {
            $siblingRegistries = \App\Models\Registry::where('type', $type)
                ->where('year', $activeRegistry->year)
                ->orderBy('number', 'asc')
                ->get(['id', 'year', 'number', 'reference_prefix', 'status']);
        }

        return Inertia::render('CivilActs/Index', [
            'acts'                => $acts,
            'type'                => $type,
            'activeRegistry'      => $activeRegistry,
            'availableYears'      => $availableYears,
            'availableRegistries' => $availableRegistries,
            'siblingRegistries'   => $siblingRegistries,
            'filters'             => [
                'search'         => $search ?? '',
                'year'           => $year ?? '',
                'volume_number'  => $volumeNumber ?? '',
                'act_number'     => $actNumber ?? '',
                'status'         => $status ?? ($activeRegistry ? 'signe' : 'brouillon'),
                'sort_by'        => $sortBy,
                'sort_order'     => $sortOrder,
                'registry_id'    => $registryId ?? '',
            ],
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
        $registries = $this->getRegistriesWithUsedNumbers($type);

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
        
        $registries = $this->getRegistriesWithUsedNumbers($type, $act->id, $act->registry_id);

        return Inertia::render('CivilActs/Form', [
            'act' => $act,
            'type' => $type,
            'is_edit' => true,
            'registries' => $registries
        ]);
    }

    private function getRegistriesWithUsedNumbers(string $type, $excludeActId = null, $includeRegistryId = null)
    {
        $model = $this->getModel($type);
        $registries = \App\Models\Registry::where('type', $type)
            ->where(function ($q) use ($includeRegistryId) {
                $q->where('status', 'open');
                if ($includeRegistryId) {
                    $q->orWhere('id', $includeRegistryId);
                }
            })
            ->orderBy('year', 'desc')
            ->orderBy('number', 'asc')
            ->get();

        $registryIds = $registries->pluck('id');
        $existingActs = $model->whereIn('registry_id', $registryIds)
            ->select('id', 'registry_id', 'reference_number')
            ->get()
            ->groupBy('registry_id');

        return $registries->map(function ($registry) use ($existingActs, $excludeActId) {
            $acts = $existingActs->get($registry->id, collect());
            if ($excludeActId) {
                $acts = $acts->where('id', '!=', $excludeActId);
            }
            $refNumbers = $acts->pluck('reference_number')->filter()->values()->all();
            $usedNumbers = [];
            foreach ($refNumbers as $ref) {
                if (preg_match('/(?:^|-| )0*(\d+)$/', trim($ref), $matches)) {
                    $usedNumbers[] = (int) $matches[1];
                }
            }
            $data = $registry->toArray();
            $data['existing_reference_numbers'] = $refNumbers;
            $data['used_act_numbers'] = array_values(array_unique($usedNumbers));
            return $data;
        });
    }

    protected function prepareBirthDateInputs(Request $request, string $type): void
    {
        $birthDateType = $request->input('birth_date_type', 'exact');
        if (!in_array($birthDateType, ['exact', 'vers', 'annee', 'age'])) {
            $birthDateType = 'exact';
        }

        if ($type === 'naissance' || $type === 'deces') {
            if ($birthDateType === 'vers' || $birthDateType === 'annee') {
                $year = (int) $request->input('birth_year');
                if ($year > 0) {
                    $request->merge([
                        'birth_year' => $year,
                        'date_of_birth' => sprintf('%04d-01-01', $year),
                    ]);
                }
            } elseif ($birthDateType === 'age') {
                $age = (int) $request->input('presumed_age');
                $refDate = $type === 'deces'
                    ? ($request->input('date_of_death') ?: $request->input('act_registration_date'))
                    : $request->input('act_registration_date');
                $refYear = $refDate ? (int) date('Y', strtotime($refDate)) : (int) date('Y');
                $year = max(1850, $refYear - $age);
                $request->merge([
                    'presumed_age' => $age,
                    'birth_year' => $year,
                    'date_of_birth' => sprintf('%04d-01-01', $year),
                ]);
            } elseif ($birthDateType === 'exact') {
                if ($request->filled('date_of_birth')) {
                    $year = (int) date('Y', strtotime($request->input('date_of_birth')));
                    $request->merge(['birth_year' => $year]);
                }
            }
        }

        // Handle parents_metadata in naissance
        if ($type === 'naissance' && $request->has('parents_metadata')) {
            $parentsMeta = $request->input('parents_metadata', []);
            if (is_array($parentsMeta)) {
                $childYear = (int) ($request->input('birth_year') ?: date('Y'));
                // Father
                $fType = $parentsMeta['father_birth_type'] ?? 'exact';
                if ($fType === 'vers' && !empty($parentsMeta['father_birth_year'])) {
                    $parentsMeta['father_date_of_birth'] = sprintf('%04d-01-01', (int) $parentsMeta['father_birth_year']);
                } elseif ($fType === 'age' && !empty($parentsMeta['father_age'])) {
                    $parentsMeta['father_birth_year'] = max(1850, $childYear - (int) $parentsMeta['father_age']);
                    $parentsMeta['father_date_of_birth'] = sprintf('%04d-01-01', $parentsMeta['father_birth_year']);
                }
                // Mother
                $mType = $parentsMeta['mother_birth_type'] ?? 'exact';
                if ($mType === 'vers' && !empty($parentsMeta['mother_birth_year'])) {
                    $parentsMeta['mother_date_of_birth'] = sprintf('%04d-01-01', (int) $parentsMeta['mother_birth_year']);
                } elseif ($mType === 'age' && !empty($parentsMeta['mother_age'])) {
                    $parentsMeta['mother_birth_year'] = max(1850, $childYear - (int) $parentsMeta['mother_age']);
                    $parentsMeta['mother_date_of_birth'] = sprintf('%04d-01-01', $parentsMeta['mother_birth_year']);
                }
                $request->merge(['parents_metadata' => $parentsMeta]);
            }
        }

        // Handle spouses_metadata in mariage
        if ($type === 'mariage' && $request->has('spouses_metadata')) {
            $spousesMeta = $request->input('spouses_metadata', []);
            if (is_array($spousesMeta)) {
                $mDate = $request->input('marriage_date');
                $mYear = $mDate ? (int) date('Y', strtotime($mDate)) : (int) date('Y');
                if (($spousesMeta['husband_birth_type'] ?? 'exact') === 'vers' && !empty($spousesMeta['husband_birth_year'])) {
                    $spousesMeta['husband_date_of_birth'] = sprintf('%04d-01-01', (int) $spousesMeta['husband_birth_year']);
                } elseif (($spousesMeta['husband_birth_type'] ?? 'exact') === 'age' && !empty($spousesMeta['husband_age'])) {
                    $spousesMeta['husband_birth_year'] = max(1850, $mYear - (int) $spousesMeta['husband_age']);
                    $spousesMeta['husband_date_of_birth'] = sprintf('%04d-01-01', $spousesMeta['husband_birth_year']);
                }
                if (($spousesMeta['wife_birth_type'] ?? 'exact') === 'vers' && !empty($spousesMeta['wife_birth_year'])) {
                    $spousesMeta['wife_date_of_birth'] = sprintf('%04d-01-01', (int) $spousesMeta['wife_birth_year']);
                } elseif (($spousesMeta['wife_birth_type'] ?? 'exact') === 'age' && !empty($spousesMeta['wife_age'])) {
                    $spousesMeta['wife_birth_year'] = max(1850, $mYear - (int) $spousesMeta['wife_age']);
                    $spousesMeta['wife_date_of_birth'] = sprintf('%04d-01-01', $spousesMeta['wife_birth_year']);
                }
                $request->merge(['spouses_metadata' => $spousesMeta]);
            }
        }
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de créer un acte.");
        }

        $type = $request->route('type');
        $this->prepareBirthDateInputs($request, $type);
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
            // RULE: max 50 acts per volume, numbering continues across volumes within the same year
            $registries = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                ->where('type', $type)
                ->where('year', $year)
                ->where('status', 'open')
                ->orderBy('number', 'asc')
                ->get();

            foreach ($registries as $r) {
                $count = $model->where('registry_id', $r->id)->count();
                if ($count < 50) {
                    $registry = $r;
                    break;
                }
            }

            // No open volume with space — create a new one
            if (!$registry) {
                $latestRegistry = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->orderBy('number', 'desc')
                    ->first();

                if ($latestRegistry && $latestRegistry->status === 'closed') {
                    return back()->withErrors(['registry_id' => 'Le registre pour cette année est fermé.']);
                }

                $nextNumber = (\App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->max('number') ?? 0) + 1;

                $registry = \App\Models\Registry::create([
                    'civil_registration_center_id' => $centerId,
                    'type'             => $type,
                    'year'             => $year,
                    'number'           => $nextNumber,
                    'status'           => 'open',
                    'opening_date'     => now(),
                    'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1-' . $nextNumber,
                ]);
            }
        }

        if ($registry->status !== 'open') {
            return back()->withErrors(['registry_id' => 'Le registre pour cette année est fermé.']);
        }

        $actCount = $model->where('registry_id', $registry->id)->count();
        if ($actCount >= 50) {
            return back()->withErrors(['registry_id' => 'Ce volume a atteint sa limite de 50 actes.']);
        }

        // RULE: sequential numbering across ALL volumes of the same year
        // Count all acts for this year+type+center across every volume, ignoring custom non-sequential references
        $allRegistryIds = \App\Models\Registry::where('civil_registration_center_id', $centerId)
            ->where('type', $type)
            ->where('year', $year)
            ->pluck('id');

        $referenceNumbers = $model->whereIn('registry_id', $allRegistryIds)
            ->pluck('reference_number')
            ->toArray();
        $maxIncrement = 0;
        $escapedPrefix = preg_quote($registry->reference_prefix, '/');
        foreach ($referenceNumbers as $ref) {
            if (preg_match('/^' . $escapedPrefix . '-(\d+)$/', $ref, $matches)) {
                $val = intval($matches[1]);
                if ($val > $maxIncrement) {
                    $maxIncrement = $val;
                }
            } elseif (preg_match('/^[A-Z]-\d{4}-C\d+(?:-\d+)?-(\d+)$/', $ref, $matches)) {
                $val = intval($matches[1]);
                if ($val > $maxIncrement) {
                    $maxIncrement = $val;
                }
            }
        }
        $increment = $maxIncrement + 1;

        if ($increment > 9999 && !($isOldRegistry && !empty($validated['reference_number']))) {
            return back()->withErrors(['registry_id' => 'La limite annuelle d\'actes a été atteinte.']);
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
                'doc_consentement'   => 'doc_consentement_path',
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

        // Auto-fermeture du volume s'il atteint 50 actes
        $totalInVolume = $model->where('registry_id', $registry->id)->count();
        if ($totalInVolume >= 50 && $registry->status === 'open') {
            $registry->update([
                'status'       => 'closed',
                'closing_date' => now(),
            ]);
        }

        return redirect()->route("acts.{$type}.show", $act->id)
            ->with('success', 'Acte enregistré avec succès.');
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->hasPermissionTo('create-drafts')) {
            abort(403, "Vous n'avez pas la permission de modifier cet acte.");
        }

        $type = $request->route('type');
        $this->prepareBirthDateInputs($request, $type);
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

        $isOldRegistry = $request->boolean('is_old_registry');
        if ($isOldRegistry && !empty($validated['reference_number'])) {
            $targetRegId = $data['registry_id'] ?? $act->registry_id;
            $alreadyExists = $model->where('registry_id', $targetRegId)
                ->where('reference_number', $validated['reference_number'])
                ->where('id', '!=', $act->id)
                ->exists();

            if ($alreadyExists) {
                return back()->withErrors([
                    'reference_number' => "L'acte « {$validated['reference_number']} » existe déjà dans ce registre. Veuillez choisir un autre numéro."
                ]);
            }
        }

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
                if ($count < 50) {
                    $newRegistry = $r;
                    break;
                }
            }

            if (!$newRegistry) {
                $latestRegistry = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->orderBy('number', 'desc')
                    ->first();

                if ($latestRegistry && $latestRegistry->status === 'closed') {
                    return back()->with('error', 'Le registre cible pour cette année est fermé.');
                }

                $nextNumber = (\App\Models\Registry::where('civil_registration_center_id', $centerId)
                    ->where('type', $type)
                    ->where('year', $year)
                    ->max('number') ?? 0) + 1;

                $newRegistry = \App\Models\Registry::create([
                    'civil_registration_center_id' => $centerId,
                    'type'             => $type,
                    'year'             => $year,
                    'number'           => $nextNumber,
                    'status'           => 'open',
                    'opening_date'     => now(),
                    'reference_prefix' => strtoupper(substr($type, 0, 1)) . '-' . $year . '-C1-' . $nextNumber,
                ]);
            }

            $newRegistryActCount = $model->where('registry_id', $newRegistry->id)->count();
            if ($newRegistryActCount >= 50) {
                return back()->with('error', 'Le volume cible a atteint sa limite de 50 actes.');
            }

            // Sequential numbering across all volumes of the target year
            $allRegistryIds = \App\Models\Registry::where('civil_registration_center_id', $centerId)
                ->where('type', $type)
                ->where('year', $year)
                ->pluck('id');
            $yearActCount = $model->whereIn('registry_id', $allRegistryIds)->count();
            $increment = $yearActCount + 1;

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
                'doc_consentement'   => 'doc_consentement_path',
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
        $isApprox = in_array(request()->input('birth_date_type'), ['vers', 'annee', 'age']);
        $docRule = ($id || $isOldRegistry || $isApprox) ? 'nullable' : 'required';
        $judgmentRule = ($id || $isOldRegistry) ? 'nullable' : 'nullable|required_if:is_judgment,true';
        $oldRegistryMetaRule = ($id || $isOldRegistry || $isApprox) ? 'nullable' : 'required';

        $common = [
            'officer_comments' => 'nullable|string',
            'certificate_file' => 'nullable|file|mimes:pdf|max:500',
            'certificate_path' => 'nullable|string',
        ];

        if ($type === 'naissance') {
            $isFoundling = filter_var(request()->input('parents_metadata.is_foundling', false), FILTER_VALIDATE_BOOLEAN);
            $isFatherUnrecognized = filter_var(request()->input('parents_metadata.is_father_unrecognized', false), FILTER_VALIDATE_BOOLEAN);
            $fatherOptional = $isFoundling || $isFatherUnrecognized || $isOldRegistry || $id;
            $motherOptional = $isFoundling || $isOldRegistry || $id;
            $fatherRule = $fatherOptional ? 'nullable' : 'required';
            $parentRule  = $motherOptional ? 'nullable' : 'required';

            return array_merge($common, [
                'first_name'                              => 'required|string',
                'last_name'                               => 'required|string',
                'birth_date_type'                         => 'nullable|in:exact,vers,annee,age',
                'birth_year'                              => 'nullable|integer|min:1850|max:' . (date('Y') + 1),
                'presumed_age'                            => 'nullable|integer|min:0|max:150',
                'date_of_birth'                           => 'required|date',
                'time_of_birth'                           => ($isOldRegistry || $id || $isApprox) ? 'nullable|date_format:H:i' : 'required|date_format:H:i',
                'place_of_birth'                          => 'required|string',
                'health_facility'                         => ($isOldRegistry || $id || $isApprox) ? 'nullable|string' : 'required|string',
                'act_registration_date'                   => 'required|date',
                'gender'                                  => 'required|in:M,F',
                'is_judgment'                             => 'nullable|boolean',
                'judgment_number'                         => $judgmentRule . '|string',
                'judgment_date'                           => $judgmentRule . '|date',
                'judgment_court'                          => $judgmentRule . '|string',
                'father_name'                             => $fatherRule . '|string',
                'mother_name'                             => $parentRule . '|string',
                'parents_metadata'                        => 'required|array',
                'parents_metadata.is_foundling'           => 'nullable|boolean',
                'parents_metadata.is_father_unrecognized' => 'nullable|boolean',
                'parents_metadata.father_birth_type'      => 'nullable|in:exact,vers,annee,age',
                'parents_metadata.father_birth_year'      => 'nullable|integer',
                'parents_metadata.father_age'             => 'nullable|integer',
                'parents_metadata.father_profession'      => $fatherRule . '|string',
                'parents_metadata.father_date_of_birth'   => [
                    $fatherRule,
                    'date',
                    function ($attribute, $value, $fail) use ($fatherOptional) {
                        if ($fatherOptional || empty($value)) return;
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
                'parents_metadata.father_place_of_birth'  => $fatherRule . '|string',
                'parents_metadata.father_domicile'        => $fatherRule . '|string',
                'parents_metadata.mother_birth_type'      => 'nullable|in:exact,vers,annee,age',
                'parents_metadata.mother_birth_year'      => 'nullable|integer',
                'parents_metadata.mother_age'             => 'nullable|integer',
                'parents_metadata.mother_profession'      => $parentRule . '|string',
                'parents_metadata.mother_date_of_birth'   => [
                    $parentRule,
                    'date',
                    function ($attribute, $value, $fail) use ($isFoundling, $isOldRegistry, $id) {
                        if ($isFoundling || $isOldRegistry || $id || empty($value)) return;
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
                'parents_metadata.mother_place_of_birth'  => $parentRule . '|string',
                'parents_metadata.mother_domicile'        => $parentRule . '|string',
                // Section Déclarant
                'parents_metadata.declarant_relationship' => 'nullable|string',
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
                'doc_jugement'                            => $judgmentRule . '|file|mimes:pdf|max:500',
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
                'judgment_number'                              => $judgmentRule . '|string',
                'judgment_date'                                => $judgmentRule . '|date',
                // Spouses Metadata JSON
                'spouses_metadata'                             => 'required|array',
                'spouses_metadata.husband_birth_type'          => 'nullable|in:exact,vers,annee,age',
                'spouses_metadata.husband_birth_year'          => 'nullable|integer',
                'spouses_metadata.husband_age'                 => 'nullable|integer',
                'spouses_metadata.husband_date_of_birth'       => $oldRegistryMetaRule . '|date',
                'spouses_metadata.husband_place_of_birth'      => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_profession'          => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_domicile'            => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_residence'           => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_married_to'          => 'nullable|string',
                'spouses_metadata.wife_birth_type'             => 'nullable|in:exact,vers,annee,age',
                'spouses_metadata.wife_birth_year'             => 'nullable|integer',
                'spouses_metadata.wife_age'                    => 'nullable|integer',
                'spouses_metadata.wife_date_of_birth'          => $oldRegistryMetaRule . '|date',
                'spouses_metadata.wife_place_of_birth'         => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_profession'             => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_domicile'               => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_residence'              => $oldRegistryMetaRule . '|string',
                // Husband Parents
                'spouses_metadata.husband_father_first_name'   => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_father_last_name'    => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_father_date_of_birth'=> $oldRegistryMetaRule . '|date',
                'spouses_metadata.husband_father_profession'   => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_father_domicile'     => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_mother_first_name'   => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_mother_last_name'    => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_mother_date_of_birth'=> $oldRegistryMetaRule . '|date',
                'spouses_metadata.husband_mother_profession'   => $oldRegistryMetaRule . '|string',
                'spouses_metadata.husband_mother_domicile'     => $oldRegistryMetaRule . '|string',
                // Wife Parents
                'spouses_metadata.wife_father_first_name'      => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_father_last_name'       => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_father_date_of_birth'   => $oldRegistryMetaRule . '|date',
                'spouses_metadata.wife_father_profession'      => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_father_domicile'        => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_mother_first_name'      => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_mother_last_name'       => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_mother_date_of_birth'   => $oldRegistryMetaRule . '|date',
                'spouses_metadata.wife_mother_profession'      => $oldRegistryMetaRule . '|string',
                'spouses_metadata.wife_mother_domicile'        => $oldRegistryMetaRule . '|string',
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
                'doc_consentement'                             => $docRule . '|file|mimes:pdf|max:500',
                'doc_domicile'                                 => $docRule . '|file|mimes:pdf|max:500',
                'doc_medical'                                  => $docRule . '|file|mimes:pdf|max:500',
                'doc_parental_auth'                            => 'nullable|file|mimes:pdf|max:500',
                'doc_jugement'                                 => $judgmentRule . '|file|mimes:pdf|max:500',
                'doc_autres'                                   => 'nullable|file|mimes:pdf|max:500',
            ]);
        }

        if ($type === 'deces') {
            return array_merge($common, [
                'deceased_first_name'                       => 'required|string',
                'deceased_last_name'                        => 'required|string',
                'gender'                                    => 'required|in:M,F',
                'birth_date_type'                           => 'nullable|in:exact,vers,annee,age',
                'birth_year'                                => 'nullable|integer|min:1850|max:' . (date('Y') + 1),
                'presumed_age'                              => 'nullable|integer|min:0|max:150',
                'date_of_birth'                             => 'required|date',
                'date_of_death'                             => 'required|date',
                'time_of_death'                             => 'required|date_format:H:i',
                'place_of_death'                            => 'required|string',
                'health_facility'                           => 'required|string',
                'act_registration_date'                     => 'required|date',
                'cause_of_death'                            => 'nullable|string',
                'is_judgment'                               => 'nullable|boolean',
                'judgment_number'                           => $judgmentRule . '|string',
                'judgment_date'                             => $judgmentRule . '|date',
                'judgment_court'                            => $judgmentRule . '|string',
                // Death Metadata JSON
                'death_metadata'                            => 'required|array',
                'death_metadata.time_of_birth'              => 'nullable|string',
                'death_metadata.place_of_birth'             => $oldRegistryMetaRule . '|string',
                'death_metadata.profession'                 => $oldRegistryMetaRule . '|string',
                'death_metadata.domicile'                   => $oldRegistryMetaRule . '|string',
                'death_metadata.marital_status'             => $oldRegistryMetaRule . '|string',
                'death_metadata.previously_married_to'      => 'nullable|string',
                // Parents of deceased
                'death_metadata.father_first_name'          => $oldRegistryMetaRule . '|string',
                'death_metadata.father_last_name'           => $oldRegistryMetaRule . '|string',
                'death_metadata.father_birth_type'          => 'nullable|in:exact,vers,annee,age',
                'death_metadata.father_birth_year'          => 'nullable|integer',
                'death_metadata.father_age'                 => 'nullable|integer',
                'death_metadata.father_date_of_birth'       => $oldRegistryMetaRule . '|date',
                'death_metadata.father_profession'          => $oldRegistryMetaRule . '|string',
                'death_metadata.father_domicile'            => $oldRegistryMetaRule . '|string',
                'death_metadata.mother_first_name'          => $oldRegistryMetaRule . '|string',
                'death_metadata.mother_last_name'           => $oldRegistryMetaRule . '|string',
                'death_metadata.mother_birth_type'          => 'nullable|in:exact,vers,annee,age',
                'death_metadata.mother_birth_year'          => 'nullable|integer',
                'death_metadata.mother_age'                 => 'nullable|integer',
                'death_metadata.mother_date_of_birth'       => $oldRegistryMetaRule . '|date',
                'death_metadata.mother_profession'          => $oldRegistryMetaRule . '|string',
                'death_metadata.mother_domicile'            => $oldRegistryMetaRule . '|string',
                // Declarant
                'death_metadata.declarant_first_name'       => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_last_name'        => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_profession'       => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_address'          => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_relationship'     => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_id_number'        => $oldRegistryMetaRule . '|string',
                'death_metadata.declarant_date_time'        => $oldRegistryMetaRule . '|string',
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
                'doc_jugement'                              => $judgmentRule . '|file|mimes:pdf|max:500',
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
        $excludeKeys = [
            'reference_number', 'officer_comments', 'certificate_path', 'judgment_number', 'gender',
            'marriage_option', 'matrimonial_regime', 'marital_status', 'judgment_court', 'cause_of_death',
            'birth_date_type', 'father_birth_type', 'mother_birth_type', 'husband_birth_type', 'wife_birth_type',
        ];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $isName = str_ends_with($key, 'last_name') || str_contains($key, 'last_name') || $key === 'nom';
                
                if ($isName) {
                    $data[$key] = mb_strtoupper($value, 'UTF-8');
                } else {
                    if (!in_array($key, $excludeKeys) && !preg_match('/_date|_time|_id|doc_|^is_|_type$/', $key)) {
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
