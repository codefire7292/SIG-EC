<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CivilCertificate;
use App\Enums\CivilCertificateType;
use Carbon\Carbon;

class CivilCertificateRegistryService
{
    /**
     * Generates a unique registry reference based on certificate rules.
     */
    public function generateReference(CivilCertificateType $type, string $centerCode = 'DEF'): string
    {
        $year = now()->format('Y');
        $prefix = $this->getPrefix($type);

        // TECHNICAL RULE: Distinct registry per center + year + type for ALL certificates
        $count = CivilCertificate::where('type', $type)
            ->where('center', $centerCode)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%s-%s-%04d', $prefix, $centerCode, $year, $count);
    }

    public function getPrefix(CivilCertificateType $type): string
    {
        return match($type) {
            CivilCertificateType::RESIDENCE => 'RES',
            CivilCertificateType::COUTUME => 'COU',
            CivilCertificateType::INDIGENCE => 'IND',
            CivilCertificateType::INDIVIDUALITE => 'IDV',
            CivilCertificateType::VIE_COLLECTIVE => 'VCL',
            CivilCertificateType::VIE_INDIVIDUEL => 'VIL',
            CivilCertificateType::NON_INSCRIT_NAISSANCE => 'NIN',
            CivilCertificateType::ACTE_NON_INEXISTANT => 'ANI',
        };
    }
}
