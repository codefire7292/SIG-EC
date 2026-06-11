<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarriageActTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Mariages';
    }

    public function headings(): array
    {
        return [
            'reference_number',
            'husband_first_name',
            'husband_last_name',
            'wife_first_name',
            'wife_last_name',
            'marriage_date',
            'marriage_place',
            'witness1_name',
            'witness2_name',
        ];
    }

    public function array(): array
    {
        return [
            [
                'M-2026-001',
                'Jean',
                'Dupont',
                'Marie',
                'Martin',
                '2026-02-14',
                'Dakar',
                'Auguste Tall',
                'Fatou Diallo',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF7C3AED']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 22, 'C' => 22, 'D' => 22,
            'E' => 22, 'F' => 16, 'G' => 22, 'H' => 25, 'I' => 25,
        ];
    }
}
