<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeathActTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Décès';
    }

    public function headings(): array
    {
        return [
            'reference_number',
            'deceased_first_name',
            'deceased_last_name',
            'date_of_death',
            'place_of_death',
            'cause_of_death',
        ];
    }

    public function array(): array
    {
        return [
            [
                'D-2026-001',
                'Pierre',
                'Diallo',
                '2026-03-10',
                'Hôpital Principal de Dakar',
                'Insuffisance cardiaque',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFB91C1C']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 25, 'C' => 25,
            'D' => 16, 'E' => 30, 'F' => 30,
        ];
    }
}
