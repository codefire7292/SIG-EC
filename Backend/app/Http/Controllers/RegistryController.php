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

    public function close(Registry $registry)
    {
        $registry->update([
            'status' => 'closed',
            'closing_date' => now(),
        ]);

        return back()->with('success', 'Registre clôturé avec succès.');
    }

    public function reopen(Registry $registry)
    {
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
