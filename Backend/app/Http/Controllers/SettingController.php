<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        // Seed default settings if empty
        if ($settings->isEmpty()) {
            $this->seedDefaults();
            $settings = Setting::all()->groupBy('group');
        }

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'update_url' => route('admin.settings.update'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $item) {
            Setting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    protected function seedDefaults()
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'SIG-EC', 'display_name' => 'Nom de l\'application', 'group' => 'institutionnel'],
            ['key' => 'institution_name', 'value' => 'République du Sénégal - Ministère de l\'Intérieur', 'display_name' => 'Nom de l\'institution', 'group' => 'institutionnel'],
            ['key' => 'closing_time', 'value' => '00:00', 'display_name' => 'Heure de clôture journalière', 'group' => 'opérationnel'],
            ['key' => 'registry_prefix', 'value' => 'EC', 'display_name' => 'Préfixe des registres', 'group' => 'opérationnel'],
        ];

        foreach ($defaults as $default) {
            Setting::updateOrCreate(['key' => $default['key']], $default);
        }
    }
}
