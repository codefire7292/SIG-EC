<?php

namespace App\Exports\Scolarite;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class GlobalKpiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $kpis = $this->data['kpis'];
        return collect([
            ['label' => 'Total Apprenants', 'value' => $kpis['total_learners']],
            ['label' => 'Total Formateurs', 'value' => $kpis['total_trainers']],
            ['label' => 'Groupes Actifs', 'value' => $kpis['total_groups']],
            ['label' => 'Certificats Délivrés', 'value' => $kpis['total_certificates']],
            ['label' => 'Parité (Femmes %)', 'value' => $kpis['gender_parity']['ratio'] . '%'],
            ['label' => 'Taux de Certification (%)', 'value' => $kpis['success_rate'] . '%'],
            ['label' => 'Assiduité Globale (%)', 'value' => $kpis['attendance_rate'] . '%'],
            ['label' => 'Matériel Opérationnel (%)', 'value' => $kpis['operational_hardware'] . '%'],
        ]);
    }

    public function title(): string
    {
        return 'Synthèse KPIs - ' . $this->data['month'];
    }

    public function headings(): array
    {
        return [
            'Indicateur de Performance (KPI)',
            'Valeur',
        ];
    }

    public function map($kpi): array
    {
        return [
            $kpi['label'],
            $kpi['value'],
        ];
    }
}
