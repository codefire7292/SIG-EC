@php
    // ===== French number-to-words helper (defined at top level, not inside @if) =====
    if (!function_exists('toFrWords')) {
        function toFrWords(int $n): string {
            $ones = ['','un','deux','trois','quatre','cinq','six','sept','huit','neuf','dix',
                     'onze','douze','treize','quatorze','quinze','seize','dix-sept','dix-huit','dix-neuf'];
            $tens = ['','dix','vingt','trente','quarante','cinquante','soixante','soixante','quatre-vingt','quatre-vingt'];
            if ($n === 0) return 'zéro';
            if ($n < 0) return 'moins ' . toFrWords(-$n);
            $str = '';
            if ($n >= 1000) { $str .= toFrWords((int)($n/1000)) . ' mille '; $n %= 1000; }
            if ($n >= 100) {
                $h = (int)($n/100);
                $str .= ($h > 1 ? toFrWords($h) . ' ' : '') . 'cent' . ($h > 1 && $n % 100 === 0 ? 's' : '') . ' ';
                $n %= 100;
            }
            if ($n >= 20) {
                $t = (int)($n/10);
                $u = $n % 10;
                if ($t === 7 || $t === 9) { $str .= $tens[$t] . '-' . $ones[10 + $u]; }
                else { $str .= $tens[$t] . ($u === 1 && $t !== 8 ? '-et-' : ($u ? '-' : '')) . ($u ? $ones[$u] : ''); }
            } elseif ($n > 0) { $str .= $ones[$n]; }
            return rtrim($str);
        }
    }

    $months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

    // ===== Ref year & number =====
    preg_match('/(\d+)$/', $act->reference_number, $refMatches);
    $refNum = isset($refMatches[1]) ? intval($refMatches[1]) : '';

    if ($type === 'naissance') {
        $refYear = $act->date_of_birth ? $act->date_of_birth->format('Y') : ($act->registry?->year ?? now()->year);
        $dob      = $act->date_of_birth;
        $yearFr   = $dob ? ucfirst(toFrWords((int)$dob->format('Y'))) : '';
        $dayFr    = $dob ? ($dob->day === 1 ? 'premier' : toFrWords($dob->day)) : '';
        $monthWord = $dob ? $months[$dob->month - 1] : '';
        // Normalize time to avoid format mismatch
        $rawTime = $act->time_of_birth;
        if ($rawTime) {
            // Accept both H:i and H:i:s
            $timeParts = explode(':', $rawTime);
            $timeDisplay = sprintf('%02d:%02d', $timeParts[0] ?? 0, $timeParts[1] ?? 0);
        } else {
            $timeDisplay = null;
        }
        // Mother name split
        $motherFirst = '';
        $motherLast  = '';
        if ($act->mother_name) {
            $parts       = explode(' ', $act->mother_name, 2);
            $motherFirst = $parts[0] ?? '';
            $motherLast  = $parts[1] ?? '';
        }
        if (is_array($act->parents_metadata)) {
            $motherFirst = $motherFirst ?: ($act->parents_metadata['mother_first_name'] ?? '');
            $motherLast  = $motherLast ?: ($act->parents_metadata['mother_last_name'] ?? '');
        }
        $isFatherUnrecognized = is_array($act->parents_metadata) && !empty($act->parents_metadata['is_father_unrecognized']);
        $isFoundling          = is_array($act->parents_metadata) && !empty($act->parents_metadata['is_foundling']);
    } elseif ($type === 'mariage') {
        $refYear = $act->marriage_date ? $act->marriage_date->format('Y') : ($act->registry?->year ?? now()->year);
        $md      = $act->marriage_date;
        $mMonth  = $md ? $months[$md->month - 1] : '';
    } else {
        $refYear = $act->date_of_death ? $act->date_of_death->format('Y') : ($act->registry?->year ?? now()->year);
        $dd      = $act->date_of_death ?? null;
        $dMonth  = $dd ? $months[$dd->month - 1] : '';
        $timeOfDeathDisplay = isset($act->time_of_death) && $act->time_of_death
            ? \Carbon\Carbon::createFromFormat('H:i:s', strlen($act->time_of_death) === 5 ? $act->time_of_death . ':00' : $act->time_of_death)->format('H:i:s')
            : null;
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Extrait d'Acte — {{ $act->reference_number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; line-height: 1.5; margin: 13mm 12mm; }

        .outer-border-table { border: 1px solid #000; width: 100%; border-collapse: collapse; }
        .outer-border-table > tbody > tr > td { padding: 0; vertical-align: top; }

        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 5px 10px; }
        .header-left { width: 48%; border-right: 1px solid #000; text-align: center; }
        .header-right { width: 52%; text-align: center; }
        .header-left p { font-size: 11px; line-height: 1.5; }
        .header-logo { width: 52px; height: 52px; margin: 3px auto; display: block; }
        .commune-label { font-size: 11px; font-weight: bold; margin-top: 2px; }
        .republic-label { font-size: 11px; margin-bottom: 1px; }
        .republic-motto { font-size: 10px; margin-bottom: 1px; }
        .republic-divider { font-size: 11px; letter-spacing: 2px; margin: 1px 0; }
        .etat-civil-title { font-size: 19px; font-weight: bold; letter-spacing: 1px; margin: 4px 0 3px; }
        .centre-label { font-size: 10px; line-height: 1.4; }

        .extrait-title-row { border-top: 1px solid #000; border-bottom: 1px solid #000; width: 100%; border-collapse: collapse; }
        .extrait-title-row td { padding: 6px 12px; }
        .extrait-title-cell { border-right: 1px solid #000; font-size: 13px; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
        .extrait-ref-cell { width: 22%; text-align: center; font-size: 11px; line-height: 1.8; }
        .extrait-ref-label { font-size: 9px; color: #555; }

        .body-content { padding: 6px 14px 4px; }
        .narrative { font-size: 12px; margin-bottom: 5px; }

        .field-row { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .field-row td { vertical-align: top; padding: 1px 0; }
        .field-label { font-size: 9px; text-transform: uppercase; color: #444; letter-spacing: 0.5px; display: block; margin-top: 1px; }
        .field-value { font-size: 12px; display: block; }
        .field-value-bold { font-size: 13px; font-weight: bold; }

        .jugement-table { width: 100%; border-collapse: collapse; border-top: 1px solid #000; }
        .jugement-table td { vertical-align: top; padding: 4px 8px; }
        .jugement-label-cell { width: 55px; border-right: 1px solid #000; text-align: center; vertical-align: middle; }
        .jugement-label-vertical { font-size: 8px; text-transform: uppercase; writing-mode: vertical-rl; transform: rotate(180deg); letter-spacing: 1px; white-space: nowrap; }
        .jugement-content-cell { font-size: 11px; line-height: 1.8; border-right: 1px solid #000; }
        .jugement-ref-cell { width: 95px; text-align: center; font-size: 11px; line-height: 2.0; }
        .jugement-ref-small { font-size: 9px; color: #555; white-space: nowrap; }

        .mentions-box { border-top: 1px solid #000; padding: 5px 14px; min-height: 30px; }
        .mentions-label { font-size: 9px; text-transform: uppercase; color: #444; letter-spacing: 0.5px; margin-bottom: 3px; }
        .mentions-content { font-size: 11px; min-height: 20px; }

        .footer-row { width: 100%; border-collapse: collapse; }
        .footer-row td { vertical-align: top; padding: 7px 14px; }
        .footer-qr-cell { width: 100px; text-align: center; border-right: 1px solid #ccc; }
        .footer-qr-label { font-size: 9px; margin-bottom: 3px; }
        .footer-signature-cell { text-align: right; font-size: 11px; line-height: 1.7; }
        .signature-content { display: inline-block; text-align: center; padding-right: 15px; }

        .dotted-line { display: inline-block; width: 75%; border-bottom: 1px dotted #555; vertical-align: middle; }
    </style>
</head>
<body>

<table class="outer-border-table">
<tbody>
<tr>
<td class="content-cell">

    {{-- ===== HEADER (bicolonne) ===== --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                <p>REGION DE <strong>{{ strtoupper($center?->region ?? 'ZIGUINCHOR') }}</strong></p>
                <p>DEPARTEMENT DE <strong>{{ strtoupper($center?->region ?? 'ZIGUINCHOR') }}</strong></p>
                <img src="data:image/png;base64,{{ $logo }}" class="header-logo" alt="Logo">
                <p class="commune-label">COMMUNE DE <strong>{{ strtoupper($center?->commune ?? 'ENNAMPORE') }}</strong></p>
            </td>
            <td class="header-right">
                <p class="republic-label">REPUBLIQUE DU SENEGAL</p>
                <p class="republic-motto">Un-Peuple Un-But Une-Foi</p>
                <p class="republic-divider">- - - - - - - - - - - -</p>
                <p class="etat-civil-title">ETAT-CIVIL</p>
                <p class="centre-label">
                    CENTRE PRINCIPAL (1)<br>
                    {{ strtoupper($center?->name ?? 'ENAMPORE CENTRE PRINCIPAL') }}
                </p>
            </td>
        </tr>
    </table>

    {{-- ===== TITRE + RÉFÉRENCE ===== --}}
    <table class="extrait-title-row">
        <tr>
            <td class="extrait-title-cell">
                @if($type === 'naissance')
                    EXTRAIT DU REGISTRE DES ACTES DE NAISSANCE<br>
                    <span style="font-size:10px; font-weight:normal;">Pour l'année <strong>{{ strtoupper($yearFr ?? '') }}</strong></span><br>
                    <span style="font-size:10px;">NUMERO : <strong>{{ strtoupper(toFrWords($refNum)) }}</strong></span>
                @elseif($type === 'mariage') EXTRAIT DU REGISTRE DES ACTES DE MARIAGE
                @else EXTRAIT DU REGISTRE DES ACTES DE DÉCÈS
                @endif
            </td>
            <td class="extrait-ref-cell">
                <strong>AN {{ $refYear }}</strong><br>
                <strong>{{ $refNum }}</strong><br>
                <span class="extrait-ref-label">N° dans le registre en chiffres</span>
            </td>
        </tr>
    </table>

    {{-- ===== BODY CONTENT ===== --}}
    <div class="body-content">

        @if($type === 'naissance')

        {{-- Ligne narrative : L'an ... le ... du mois de ... --}}
        <p class="narrative">
            L'an <strong>{{ strtoupper($yearFr) }}</strong>, le <strong>{{ $dayFr }}</strong> du mois de <strong>{{ strtoupper($monthWord) }}</strong>
            @if($dob)({{ $dob->format('d/m/Y') }})@endif
        </p>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    @if($timeDisplay)
                    <span class="field-value">Heures : <strong>{{ \Carbon\Carbon::parse('1970-01-01 ' . $timeDisplay)->format('H\hi') }}</strong></span>
                    @else
                    <span class="field-value">Heures : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    @endif
                    <span class="field-label">Heure de Naissance</span>
                </td>
                <td style="width:50%">
                    <span class="field-value">Est né(e) à : <strong>{{ strtoupper($act->place_of_birth ?? '') }}</strong></span>
                </td>
            </tr>
        </table>

        <p class="narrative">de sexe : <strong>{{ $act->gender === 'M' ? 'MASCULIN' : ($act->gender === 'F' ? 'FÉMININ' : 'N/A') }}</strong></p>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ strtoupper($act->first_name) }}</span>
                    <span class="field-label">PRENOM(S)</span>
                </td>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ strtoupper($act->last_name) }}</span>
                    <span class="field-label">NOM DE FAMILLE</span>
                </td>
            </tr>
        </table>

        <table class="field-row">
            <tr>
                <td>
                    @if($isFatherUnrecognized)
                    <span class="field-value" style="font-style:italic; color:#777;">Père non désigné à l'officier d'état-civil</span>
                    @elseif($isFoundling)
                    <span class="field-value" style="font-style:italic; color:#777;">non dénommé</span>
                    @else
                    <span class="field-value">de <strong>{{ strtoupper($act->father_name ?? '') }}</strong></span>
                    @endif
                    <span class="field-label">PRENOM(S) DU PERE</span>
                </td>
            </tr>
        </table>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    @if($isFoundling)
                    <span class="field-value" style="font-style:italic; color:#777;">et de non dénommée</span>
                    @else
                    <span class="field-value">et de <strong>{{ strtoupper($motherFirst) }}</strong></span>
                    @endif
                    <span class="field-label">PRENOM(S) DE LA MERE</span>
                </td>
                <td style="width:50%">
                    @if(!$isFoundling)
                    <span class="field-value"><strong>{{ strtoupper($motherLast) }}</strong></span>
                    <span class="field-label">NOM DE FAMILLE DE LA MERE</span>
                    @endif
                </td>
            </tr>
        </table>

        <p style="font-size:8.5px; color:#555; margin-top:2px;">Le pays de naissance pour les naissances à l'étranger (3)</p>

        @elseif($type === 'mariage')

        <p class="narrative">
            Le {{ $md ? $md->day : '' }} {{ $mMonth }} {{ $md ? $md->format('Y') : '' }} — Lieu : {{ $act->marriage_place ?? '' }}
        </p>
        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->husband_first_name }}</span>
                    <span class="field-label">PRENOM(S) DE L'ÉPOUX</span>
                </td>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->husband_last_name }}</span>
                    <span class="field-label">NOM DE L'ÉPOUX</span>
                </td>
            </tr>
        </table>
        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->wife_first_name }}</span>
                    <span class="field-label">PRENOM(S) DE L'ÉPOUSE</span>
                </td>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->wife_last_name }}</span>
                    <span class="field-label">NOM DE L'ÉPOUSE</span>
                </td>
            </tr>
        </table>

        @else

        <p class="narrative">
            Le {{ $dd ? $dd->day : '' }} {{ $dMonth }} {{ $dd ? $dd->format('Y') : '' }}, est décédé(e) à : {{ $act->place_of_death ?? '' }}
        </p>
        @if($timeOfDeathDisplay)
        <p class="narrative">à {{ $timeOfDeathDisplay }}</p>
        @endif
        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->deceased_first_name }}</span>
                    <span class="field-label">PRENOM(S)</span>
                </td>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->deceased_last_name }}</span>
                    <span class="field-label">NOM DE FAMILLE</span>
                </td>
            </tr>
        </table>
        <p class="narrative">de sexe : {{ $act->gender === 'M' ? 'Masculin' : ($act->gender === 'F' ? 'Féminin' : 'N/A') }}</p>
        @if($act->date_of_birth)
        <p class="narrative">né(e) le : {{ $act->date_of_birth->format('d/m/Y') }}</p>
        @endif

        @endif

    </div>

    {{-- ===== JUGEMENT (naissance uniquement) ===== --}}
    @if($type === 'naissance')
    <table class="jugement-table">
        <tr>
            <td class="jugement-label-cell">
                <img src="data:image/png;base64,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" alt="Jugement" style="width: 50px;">
            </td>
            <td class="jugement-content-cell">
                <p>Délivré par le Juge du tribunal de {{ strtoupper($center?->region ?? 'ZIGUINCHOR') }}</p>
                <p>le, <span class="dotted-line">{{ $act->is_judgment && $act->judgment_date ? \Carbon\Carbon::parse($act->judgment_date)->format('d/m/Y') : '' }}</span></p>
                <p>sous le numéro <span class="dotted-line">{{ $act->judgment_number ?? '' }}</span></p>
                <p>Transcrit-le <span class="dotted-line"></span></p>
                <p>Sur le régistre des Actes de Naissance de l'année</p>
            </td>
            <td class="jugement-ref-cell">
                <span class="field-value-bold">AN</span><br>
                <span class="field-value-bold">N°</span><br>
                <span class="jugement-ref-small">N° du jugement en chiffres</span><br><br>
                <span class="field-value-bold">AN</span>
            </td>
        </tr>
    </table>
    @endif

    {{-- ===== MENTIONS MARGINALES ===== --}}
    <div class="mentions-box">
        <p class="mentions-label">Mentions Marginales</p>
        <p class="mentions-content">{{ $act->officer_comments ?? '' }}</p>
    </div>

    {{-- ===== FOOTER ===== --}}
    <table class="footer-row">
        <tr>
            <td class="footer-qr-cell">
                <p class="footer-qr-label">QRcode</p>
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="80" alt="QR Code">
            </td>
            <td class="footer-signature-cell">
                <div class="signature-content">
                    POUR EXTRAIT CERTIFIE CONFORME<br>
                    Fait à {{ strtoupper($center?->commune ?? 'ENAMPORE') }} le, {{ \Carbon\Carbon::parse($act->validated_at ?? now())->locale('fr')->isoFormat('D MMMM YYYY') }}<br>
                    L'officier d'Etat-civil soussigné
                </div>
            </td>
        </tr>
    </table>

</td>
</tr>
</tbody>
</table>

</body>
</html>
