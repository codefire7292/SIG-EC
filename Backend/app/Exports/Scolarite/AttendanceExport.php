<?php

namespace App\Exports\Scolarite;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['records']);
    }

    public function title(): string
    {
        return 'Rapport d\'Assiduité - ' . $this->data['group'];
    }

    public function headings(): array
    {
        return [
            'Nom de l\'Apprenant',
            'Total Séances',
            'Présences',
            'Absences',
            'Retards',
            'Taux de Présence (%)',
        ];
    }

    public function map($record): array
    {
        $percentage = $record['total'] > 0 ? round(($record['present'] / $record['total']) * 100, 1) : 0;
        
        return [
            $record['student_name'],
            $record['total'],
            $record['present'],
            $record['absent'],
            $record['late'],
            $percentage . '%',
        ];
    }
}
