<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        // Seed default settings if empty or missing mayor_signature
        if ($settings->isEmpty() || !Setting::where('key', 'mayor_signature')->exists()) {
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
            'mayor_signature_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'remove_mayor_signature' => 'nullable|boolean',
        ]);

        foreach ($validated['settings'] as $item) {
            // Avoid overwriting signature with text string if handled via file
            if ($item['key'] === 'mayor_signature' && $request->hasFile('mayor_signature_file')) {
                continue;
            }
            Setting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        // Handle signature removal
        if ($request->boolean('remove_mayor_signature')) {
            $setting = Setting::where('key', 'mayor_signature')->first();
            if ($setting && $setting->value) {
                $relative = str_replace('/storage/', '', $setting->value);
                Storage::disk('public')->delete($relative);
                $setting->update(['value' => null]);
            }
        }

        // Handle signature file upload
        if ($request->hasFile('mayor_signature_file')) {
            // Remove previous signature if exists
            $setting = Setting::where('key', 'mayor_signature')->first();
            if ($setting && $setting->value) {
                $relative = str_replace('/storage/', '', $setting->value);
                Storage::disk('public')->delete($relative);
            }

            $path = $request->file('mayor_signature_file')->store('signatures', 'public');
            $url = '/storage/' . $path;
            
            Setting::updateOrCreate(
                ['key' => 'mayor_signature'],
                [
                    'value' => $url,
                    'display_name' => 'Signature du Maire / Officier',
                    'group' => 'institutionnel',
                ]
            );
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    protected function seedDefaults()
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'SIG-EC', 'display_name' => 'Nom de l\'application', 'group' => 'institutionnel'],
            ['key' => 'institution_name', 'value' => 'République du Sénégal - Ministère de l\'Intérieur', 'display_name' => 'Nom de l\'institution', 'group' => 'institutionnel'],
            ['key' => 'mayor_signature', 'value' => null, 'display_name' => 'Signature du Maire / Officier', 'group' => 'institutionnel'],
            ['key' => 'closing_time', 'value' => '00:00', 'display_name' => 'Heure de clôture journalière', 'group' => 'opérationnel'],
            ['key' => 'registry_prefix', 'value' => 'EC', 'display_name' => 'Préfixe des registres', 'group' => 'opérationnel'],
        ];

        foreach ($defaults as $default) {
            Setting::updateOrCreate(['key' => $default['key']], $default);
        }
    }
}
