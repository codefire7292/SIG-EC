<?php

namespace App\Exports\Scolarite;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TrainerReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['trainer_stats']);
    }

    public function title(): string
    {
        return 'Effectifs par Formateur';
    }

    public function headings(): array
    {
        return [
            'Formateur',
            'Total Apprenants',
            'Hommes',
            'Femmes',
            '% de l\'Effectif Global',
        ];
    }

    public function map($stat): array
    {
        return [
            $stat['name'],
            $stat['count'],
            $stat['males'],
            $stat['females'],
            $stat['percentage'] . '%',
        ];
    }
}
