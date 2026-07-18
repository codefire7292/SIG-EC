<?php

namespace App\Imports;

use App\Models\BirthAct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class BirthActsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private int $currentCount = -1;

    public function __construct(private int $registryId) {}

    public function model(array $row)
    {
        if ($this->currentCount === -1) {
            $this->currentCount = BirthAct::where('registry_id', $this->registryId)->count();
        }

        // Skip if reference already exists
        if (BirthAct::where('reference_number', $row['reference_number'])->exists()) {
            return null;
        }

        if ($this->currentCount >= 100) {
            throw new \Exception("La limite de 100 actes par registre est dépassée.");
        }

        $this->currentCount++;

        return new BirthAct([
            'registry_id'      => $this->registryId,
            'reference_number' => $row['reference_number'],
            'first_name'       => $row['first_name'],
            'last_name'        => $row['last_name'],
            'date_of_birth'    => $row['date_of_birth'],
            'place_of_birth'   => $row['place_of_birth'],
            'gender'           => strtoupper($row['gender'] ?? 'M'),
            'father_name'      => $row['father_name'] ?? null,
            'mother_name'      => $row['mother_name'] ?? null,
            'parents_metadata' => [
                'father_profession' => $row['father_profession'] ?? null,
                'mother_profession' => $row['mother_profession'] ?? null,
                'residence'         => $row['residence'] ?? null,
            ],
            'status'           => 'brouillon',
            'is_current'       => true,
            'version_number'   => 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'reference_number' => 'required|string',
            'first_name'       => 'required|string',
            'last_name'        => 'required|string',
            'date_of_birth'    => 'required',
            'place_of_birth'   => 'required|string',
            'gender'           => 'required|in:M,m,F,f',
        ];
    }
}
