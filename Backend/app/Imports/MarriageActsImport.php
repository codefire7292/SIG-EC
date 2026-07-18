<?php

namespace App\Imports;

use App\Models\MarriageAct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class MarriageActsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private int $currentCount = -1;

    public function __construct(private int $registryId) {}

    public function model(array $row)
    {
        if ($this->currentCount === -1) {
            $this->currentCount = MarriageAct::where('registry_id', $this->registryId)->count();
        }

        // Skip if reference already exists
        if (MarriageAct::where('reference_number', $row['reference_number'])->exists()) {
            return null;
        }

        if ($this->currentCount >= 100) {
            throw new \Exception("La limite de 100 actes par registre est dépassée.");
        }

        $this->currentCount++;

        return new MarriageAct([
            'registry_id'        => $this->registryId,
            'reference_number'   => $row['reference_number'],
            'husband_first_name' => $row['husband_first_name'],
            'husband_last_name'  => $row['husband_last_name'],
            'wife_first_name'    => $row['wife_first_name'],
            'wife_last_name'     => $row['wife_last_name'],
            'marriage_date'      => $row['marriage_date'],
            'marriage_place'     => $row['marriage_place'],
            'marriage_option'     => $row['marriage_option'] ?? 'polygamie',
            'matrimonial_regime' => $row['matrimonial_regime'] ?? 'separation_biens',
            'witnesses_metadata' => [
                'witness1_name' => $row['witness1_name'] ?? null,
                'witness1_cni'  => $row['witness1_cni'] ?? null,
                'witness2_name' => $row['witness2_name'] ?? null,
                'witness2_cni'  => $row['witness2_cni'] ?? null,
                'witness3_name' => $row['witness3_name'] ?? null,
                'witness3_cni'  => $row['witness3_cni'] ?? null,
                'witness4_name' => $row['witness4_name'] ?? null,
                'witness4_cni'  => $row['witness4_cni'] ?? null,
            ],
            'status'             => 'brouillon',
            'is_current'         => true,
            'version_number'     => 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'reference_number'   => 'required|string',
            'husband_first_name' => 'required|string',
            'husband_last_name'  => 'required|string',
            'wife_first_name'    => 'required|string',
            'wife_last_name'     => 'required|string',
            'marriage_date'      => 'required',
            'marriage_place'     => 'required|string',
        ];
    }
}
