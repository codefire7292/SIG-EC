<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BirthActsImport;
use App\Imports\MarriageActsImport;
use App\Imports\DeathActsImport;
use App\Exports\BirthActTemplateExport;
use App\Exports\MarriageActTemplateExport;
use App\Exports\DeathActTemplateExport;
use App\Models\Registry;

class CivilActImportController extends Controller
{
    public function downloadTemplate(string $type)
    {
        $exportClass = match ($type) {
            'naissance' => new BirthActTemplateExport(),
            'mariage'   => new MarriageActTemplateExport(),
            'deces'     => new DeathActTemplateExport(),
            default     => null,
        };

        if (!$exportClass) {
            return back()->with('error', "Type d'acte non supporté.");
        }

        $filename = "template_registre_{$type}.xlsx";
        return Excel::download($exportClass, $filename);
    }

    public function import(Request $request, string $type)
    {
        $request->validate([
            'file'        => 'required|mimes:xls,xlsx,csv|max:10240',
            'registry_id' => 'nullable|exists:registries,id',
        ]);

        // Resolve the registry: use the one sent by the client, or auto-detect
        // the open registry matching the act type for the current year.
        $registryId = $request->input('registry_id');

        if (!$registryId) {
            $registry = Registry::where('type', $type)
                ->where('year', now()->year)
                ->where('status', 'open')
                ->first();

            if (!$registry) {
                return back()->with(
                    'error',
                    "Aucun registre ouvert pour les actes de « {$type} » en " . now()->year . ". Veuillez d'abord ouvrir un registre."
                );
            }

            $registryId = $registry->id;
        }

        $importClass = match ($type) {
            'naissance' => new BirthActsImport($registryId),
            'mariage'   => new MarriageActsImport($registryId),
            'deces'     => new DeathActsImport($registryId),
            default     => null,
        };

        if (!$importClass) {
            return back()->with('error', "Type d'acte non supporté pour l'importation.");
        }

        try {
            Excel::import($importClass, $request->file('file'));

            $failures = $importClass->failures();
            if ($failures->count() > 0) {
                return back()->with('warning', "Importation complétée avec {$failures->count()} erreurs de validation.");
            }

            return back()->with('success', "Importation réussie du registre des {$type}s.");
        } catch (\Exception $e) {
            return back()->with('error', "Erreur lors de l'importation : " . $e->getMessage());
        }
    }
}
