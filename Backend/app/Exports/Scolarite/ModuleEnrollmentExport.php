<?php

namespace App\Exports\Scolarite;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ModuleEnrollmentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['modules']);
    }

    public function title(): string
    {
        return 'Effectifs par Module';
    }

    public function headings(): array
    {
        return [
            'Module',
            'Total Apprenants',
            'Hommes',
            'Femmes',
            '% du Total',
        ];
    }

    public function map($module): array
    {
        return [
            $module['name'],
            $module['count'],
            $module['males'],
            $module['females'],
            $module['percentage'] . '%',
        ];
    }
}
