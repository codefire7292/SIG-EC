<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BirthActTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Naissances';
    }

    public function headings(): array
    {
        return [
            'reference_number',
            'first_name',
            'last_name',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'father_name',
            'mother_name',
            'father_profession',
            'mother_profession',
            'residence',
        ];
    }

    public function array(): array
    {
        // Example row to guide the user
        return [
            [
                'N-2026-001',
                'Jean',
                'Dupont',
                '2026-01-15',
                'Dakar',
                'M',
                'Pierre Dupont',
                'Marie Dupont',
                'Commerçant',
                'Infirmière',
                'Médina, Dakar',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 20, 'C' => 20, 'D' => 16,
            'E' => 20, 'F' => 10, 'G' => 25, 'H' => 25,
            'I' => 22, 'J' => 22, 'K' => 25,
        ];
    }
}
