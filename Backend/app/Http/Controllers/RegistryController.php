<?php

namespace App\Http\Controllers;

use App\Models\Registry;
use App\Models\CivilRegistrationCenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class RegistryController extends Controller
{
    public function index()
    {
        $registries = Registry::with(['center'])->paginate(15);

        return Inertia::render('Admin/Registries/Index', [
            'registries' => $registries,
        ]);
    }

    public function create()
    {
        $centers = CivilRegistrationCenter::all();
        $types = ['naissance', 'mariage', 'deces', 'divers'];

        return Inertia::render('Admin/Registries/Form', [
            'centers' => $centers,
            'types' => $types,
            'is_edit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'number' => $request->input('number') ?: 1,
        ]);

        $validated = $request->validate([
            'civil_registration_center_id' => 'required|exists:civil_registration_centers,id',
            'number' => 'nullable|integer|min:1',
            'type' => [
                'required',
                'in:naissance,mariage,deces,divers',
                Rule::unique('registries')->where(function ($query) use ($request) {
                    return $query->where('civil_registration_center_id', $request->civil_registration_center_id)
                                 ->where('year', $request->year)
                                 ->where('number', $request->number);
                })
            ],
            'year' => 'required|integer|min:1900|max:'.(now()->year + 1),
            'reference_prefix' => 'required|string|max:50|unique:registries,reference_prefix',
            'status' => 'required|in:open,closed',
            'opening_date' => 'nullable|date',
        ], [
            'type.unique' => 'Un registre de ce type avec ce numéro de volume existe déjà pour ce centre et cette année.',
            'reference_prefix.unique' => 'Ce préfixe de référence est déjà utilisé par un autre registre.'
        ]);

        Registry::create($validated);

        return redirect()->route('admin.registries.index')->with('success', 'Registre ouvert avec succès.');
    }

    public function edit(Registry $registry)
    {
        if (!auth()->user()->hasRole(\App\Enums\UserRole::ADMIN->value)) {
            abort(403, "Seul l'Administrateur technique est autorisé à modifier un registre.");
        }

        $centers = CivilRegistrationCenter::all();
        $types = ['naissance', 'mariage', 'deces', 'divers'];

        return Inertia::render('Admin/Registries/Form', [
            'centers' => $centers,
            'types' => $types,
            'registry' => $registry,
            'is_edit' => true,
        ]);
    }

    public function update(Request $request, Registry $registry)
    {
        if (!auth()->user()->hasRole(\App\Enums\UserRole::ADMIN->value)) {
            abort(403, "Seul l'Administrateur technique est autorisé à modifier un registre.");
        }

        $request->merge([
            'number' => $request->input('number') ?: 1,
        ]);

        $validated = $request->validate([
            'civil_registration_center_id' => 'required|exists:civil_registration_centers,id',
            'number' => 'nullable|integer|min:1',
            'type' => [
                'required',
                'in:naissance,mariage,deces,divers',
                Rule::unique('registries')->where(function ($query) use ($request, $registry) {
                    return $query->where('civil_registration_center_id', $request->civil_registration_center_id)
                                 ->where('year', $request->year)
                                 ->where('number', $request->number);
                })->ignore($registry->id)
            ],
            'year' => 'required|integer|min:1900|max:'.(now()->year + 1),
            'reference_prefix' => 'required|string|max:50|unique:registries,reference_prefix,'.$registry->id,
            'status' => 'required|in:open,closed',
            'opening_date' => 'nullable|date',
        ], [
            'type.unique' => 'Un registre de ce type avec ce numéro de volume existe déjà pour ce centre et cette année.',
            'reference_prefix.unique' => 'Ce préfixe de référence est déjà utilisé par un autre registre.'
        ]);

        $oldPrefix = $registry->reference_prefix;
        $newPrefix = $validated['reference_prefix'];

        $registry->update($validated);

        if ($oldPrefix !== $newPrefix) {
            \DB::transaction(function () use ($registry, $oldPrefix, $newPrefix) {
                foreach (['birth_acts', 'marriage_acts', 'death_acts', 'civil_certificates'] as $table) {
                    $acts = \DB::table($table)->where('registry_id', $registry->id)->get();
                    foreach ($acts as $act) {
                        $oldRef = $act->reference_number;
                        if ($oldRef && str_starts_with($oldRef, $oldPrefix . '-')) {
                            $suffix = substr($oldRef, strlen($oldPrefix) + 1);
                            $newRef = $newPrefix . '-' . $suffix;

                            \DB::table($table)->where('id', $act->id)->update([
                                'reference_number' => $newRef
                            ]);
                        }
                    }
                }
            });
        }

        return redirect()->route('admin.registries.index')->with('success', 'Registre modifié avec succès.');
    }

    public function destroy(Registry $registry)
    {
        if (!auth()->user()->hasRole(\App\Enums\UserRole::ADMIN->value)) {
            abort(403, "Seul l'Administrateur technique est autorisé à supprimer un registre.");
        }

        $hasActs = $registry->birthActs()->exists() ||
                   $registry->marriageActs()->exists() ||
                   $registry->deathActs()->exists();

        if ($hasActs) {
            return back()->with('error', 'Impossible de supprimer ce registre car il contient déjà des actes enregistrés.');
        }

        $registry->delete();

        return redirect()->route('admin.registries.index')->with('success', 'Registre supprimé avec succès.');
    }

    public function close(Registry $registry)
    {
        if (auth()->user()->hasRole(\App\Enums\UserRole::SUPERVISEUR->value)) {
            abort(403, "Le superviseur n'est pas autorisé à clôturer un registre.");
        }

        $registry->update([
            'status' => 'closed',
            'closing_date' => now(),
        ]);

        return back()->with('success', 'Registre clôturé avec succès.');
    }

    public function reopen(Registry $registry)
    {
        if (auth()->user()->hasRole(\App\Enums\UserRole::SUPERVISEUR->value)) {
            abort(403, "Le superviseur n'est pas autorisé à réouvrir un registre.");
        }

        if ($registry->year != now()->year) {
            return back()->withErrors(['registry' => 'La réouverture n\'est possible que pour l\'année en cours.']);
        }

        $registry->update([
            'status' => 'open',
            'closing_date' => null,
        ]);

        return back()->with('success', 'Registre réouvert avec succès.');
    }
}
