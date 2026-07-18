<?php

namespace App\Imports;

use App\Models\DeathAct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class DeathActsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private int $currentCount = -1;

    public function __construct(private int $registryId) {}

    public function model(array $row)
    {
        if ($this->currentCount === -1) {
            $this->currentCount = DeathAct::where('registry_id', $this->registryId)->count();
        }

        // Skip if reference already exists
        if (DeathAct::where('reference_number', $row['reference_number'])->exists()) {
            return null;
        }

        if ($this->currentCount >= 100) {
            throw new \Exception("La limite de 100 actes par registre est dépassée.");
        }

        $this->currentCount++;

        return new DeathAct([
            'registry_id'         => $this->registryId,
            'reference_number'    => $row['reference_number'],
            'deceased_first_name' => $row['deceased_first_name'],
            'deceased_last_name'  => $row['deceased_last_name'],
            'date_of_death'       => $row['date_of_death'],
            'place_of_death'      => $row['place_of_death'] ?? null,
            'cause_of_death'      => $row['cause_of_death'] ?? null,
            'status'              => 'brouillon',
            'is_current'          => true,
            'version_number'      => 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'reference_number'    => 'required|string',
            'deceased_first_name' => 'required|string',
            'deceased_last_name'  => 'required|string',
            'date_of_death'       => 'required',
        ];
    }
}
