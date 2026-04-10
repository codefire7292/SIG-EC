<?php

namespace App\Exports\Scolarite;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AcademicExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['students']);
    }

    public function title(): string
    {
        return 'Performance Académique - ' . $this->data['group'];
    }

    public function headings(): array
    {
        return [
            'Nom complet',
            'Email',
            'Nombre de Certificats Obtenus',
        ];
    }

    public function map($student): array
    {
        return [
            $student['name'],
            $student['email'],
            $student['certificates'],
        ];
    }
}
