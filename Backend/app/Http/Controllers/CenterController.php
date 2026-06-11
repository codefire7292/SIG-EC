<?php

namespace App\Http\Controllers;

use App\Models\CivilRegistrationCenter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CenterController extends Controller
{
    public function index()
    {
        $centers = CivilRegistrationCenter::paginate(15);

        $centers->getCollection()->transform(function ($center) {
            $center->edit_url = route('admin.centers.edit', $center->id);
            $center->delete_url = route('admin.centers.destroy', $center->id);
            return $center;
        });

        return Inertia::render('Admin/Centers/Index', [
            'centers' => $centers,
            'create_url' => route('admin.centers.create'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Centers/Form', [
            'is_edit' => false,
            'back_url' => route('admin.centers.index'),
            'store_url' => route('admin.centers.store'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:civil_registration_centers',
            'commune' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        CivilRegistrationCenter::create($validated);

        return redirect()->route('admin.centers.index')->with('success', 'Centre créé avec succès.');
    }

    public function edit(CivilRegistrationCenter $center)
    {
        return Inertia::render('Admin/Centers/Form', [
            'center' => $center,
            'is_edit' => true,
            'back_url' => route('admin.centers.index'),
            'update_url' => route('admin.centers.update', $center->id),
        ]);
    }

    public function update(Request $request, CivilRegistrationCenter $center)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:civil_registration_centers,code,'.$center->id,
            'commune' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $center->update($validated);

        return redirect()->route('admin.centers.index')->with('success', 'Centre mis à jour avec succès.');
    }

    public function destroy(CivilRegistrationCenter $center)
    {
        if ($center->registries()->exists()) {
            return back()->with('error', 'Impossible de supprimer un centre ayant des registres actifs.');
        }

        $center->delete();
        return redirect()->route('admin.centers.index')->with('success', 'Centre supprimé avec succès.');
    }
}
