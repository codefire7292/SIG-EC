<?php

namespace App\Exports\Scolarite;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\IOFactory;

class WordReportGenerator
{
    protected $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->setupStyles();
    }

    private function setupStyles(): void
    {
        $this->phpWord->addTitleStyle(1, ['bold' => true, 'size' => 24, 'color' => '1B3A60'], ['alignment' => Jc::CENTER, 'spaceAfter' => 240]);
        $this->phpWord->addTitleStyle(2, ['bold' => true, 'size' => 16, 'color' => '2E5077'], ['spaceBefore' => 240, 'spaceAfter' => 120]);
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(11);
    }

    public function generate(array $data): string
    {
        $section = $this->phpWord->addSection([
            'marginTop' => 1200,
            'marginRight' => 1200,
            'marginBottom' => 1200,
            'marginLeft' => 1200,
        ]);

        // --- Institutional Header ---
        $header = $section->addHeader();
        $header->addText("Ministère de l’Enseignement Supérieur de la Recherche et de l’Innovation", ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
        $header->addText("Direction Générale de la Recherche", ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
        $header->addText("Centre de Recherche et d’Essais de Kolda", ['bold' => true, 'size' => 10, 'color' => '1B3A60'], ['alignment' => Jc::CENTER]);
        $header->addTextBreak(1);

        // --- Title Section ---
        $section->addTitle("RAPPORT DE SYNTHÈSE D'ACTIVITÉ", 1);
        $section->addText("Période : " . $data['period']['label'], ['italic' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(2);

        // --- Executive Summary ---
        $section->addTitle("1. Résumé Exécutif", 2);
        $section->addText("Ce rapport présente une synthèse des activités du Centre de Recherche et d'Essais (CRE) de Kolda pour la période mentionnée. Il agrège les données relatives à l'assiduité des apprenants, aux performances académiques, à la parité de genre et à l'état des infrastructures.");
        $section->addTextBreak(1);

        // --- KPIs Table ---
        $section->addTitle("2. Indicateurs Clés de Performance (KPIs)", 2);
        $styleTable = ['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80];
        $styleFirstRow = ['bgColor' => 'F2F2F2', 'bold' => true];
        $this->phpWord->addTableStyle('KPI Table', $styleTable, $styleFirstRow);
        
        $table = $section->addTable('KPI Table');
        $table->addRow();
        $table->addCell(4000)->addText("Indicateur");
        $table->addCell(4000)->addText("Valeur");

        $metrics = $data['metrics'];
        $this->addKpiRow($table, "Effectif Total Apprenants", $metrics['total_learners']);
        $this->addKpiRow($table, "Nouvelles Inscriptions", $metrics['new_enrollments']);
        $this->addKpiRow($table, "Taux d'Assiduité", $metrics['attendance_rate'] . "%");
        $this->addKpiRow($table, "Certificats Délivrés", $metrics['certification_count']);
        $this->addKpiRow($table, "Taux de Réussite", $metrics['success_rate'] . "%");
        $this->addKpiRow($table, "Parité (Femmes %)", $metrics['parity_female_ratio'] . "%");
        $this->addKpiRow($table, "Matériel Opérationnel", $metrics['hardware_operational_rate'] . "%");
        $section->addTextBreak(1);

        // --- Analysis Section ---
        $section->addTitle("3. Analyse et Constats", 2);
        foreach ($data['insights']['analysis'] as $constat) {
            $section->addListItem($constat, 0, null, 'bullet');
        }
        $section->addTextBreak(1);

        // --- Projections ---
        $section->addTitle("4. Projections et Perspectives", 2);
        foreach ($data['insights']['projections'] as $projection) {
            $section->addListItem($projection, 0, null, 'bullet');
        }
        $section->addTextBreak(1);

        // --- Recommendations (AI Generated) ---
        $section->addTitle("5. Recommandations Stratégiques", 2);
        $section->addText("Sur la base des données analysées, les recommandations suivantes sont suggérées pour optimiser les performances du centre :", ['italic' => true]);
        $section->addTextBreak(1);
        foreach ($data['insights']['recommendations'] as $rec) {
            $listItemRun = $section->addListItemRun(0, 'bullet');
            $listItemRun->addText($rec, ['bold' => true, 'color' => 'CC0000']);
        }
        $section->addTextBreak(2);

        // --- Footer ---
        $footer = $section->addFooter();
        $footer->addPreserveText('Page {PAGE} sur {NUMPAGES}', null, ['alignment' => Jc::CENTER]);

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'cre_report_');
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return $tempFile;
    }

    private function addKpiRow($table, $label, $value): void
    {
        $table->addRow();
        $table->addCell(4000)->addText($label);
        $table->addCell(4000)->addText((string)$value, ['bold' => true]);
    }
}
