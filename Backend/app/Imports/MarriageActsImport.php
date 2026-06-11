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

    public function __construct(private int $registryId) {}

    public function model(array $row)
    {
        // Skip if reference already exists
        if (MarriageAct::where('reference_number', $row['reference_number'])->exists()) {
            return null;
        }

        return new MarriageAct([
            'registry_id'        => $this->registryId,
            'reference_number'   => $row['reference_number'],
            'husband_first_name' => $row['husband_first_name'],
            'husband_last_name'  => $row['husband_last_name'],
            'wife_first_name'    => $row['wife_first_name'],
            'wife_last_name'     => $row['wife_last_name'],
            'marriage_date'      => $row['marriage_date'],
            'marriage_place'     => $row['marriage_place'],
            'witnesses_metadata' => [
                'witness1_name' => $row['witness1_name'] ?? null,
                'witness2_name' => $row['witness2_name'] ?? null,
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
