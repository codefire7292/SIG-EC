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
        $timeDisplay = $act->time_of_birth
            ? \Carbon\Carbon::createFromFormat('H:i:s', strlen($act->time_of_birth) === 5 ? $act->time_of_birth . ':00' : $act->time_of_birth)->format('H:i:s')
            : null;
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
        @page { margin: 15mm 14mm 15mm 14mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; line-height: 1.4; }

        .outer-border { border: 2px solid #000; width: 100%; }

        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 8px 10px; }
        .header-left { width: 48%; border-right: 1px solid #000; text-align: center; }
        .header-right { width: 52%; text-align: center; }
        .header-left p { font-size: 10px; line-height: 1.7; }
        .header-logo { width: 50px; height: 50px; margin: 5px auto; display: block; }
        .commune-label { font-size: 10px; font-weight: bold; margin-top: 4px; }
        .republic-label { font-size: 10px; margin-bottom: 2px; }
        .republic-motto { font-size: 9px; margin-bottom: 2px; }
        .republic-divider { font-size: 10px; letter-spacing: 2px; margin: 2px 0; }
        .etat-civil-title { font-size: 18px; font-weight: bold; letter-spacing: 1px; margin: 6px 0 4px; }
        .centre-label { font-size: 10px; line-height: 1.6; }

        .extrait-title-row { border-top: 1px solid #000; border-bottom: 1px solid #000; width: 100%; border-collapse: collapse; }
        .extrait-title-row td { padding: 6px 10px; }
        .extrait-title-cell { border-right: 1px solid #000; font-size: 12px; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
        .extrait-ref-cell { width: 22%; text-align: center; font-size: 10px; line-height: 1.8; }
        .extrait-ref-label { font-size: 8px; color: #555; }

        .body-content { padding: 10px 14px 6px; }
        .narrative { font-size: 11px; margin-bottom: 8px; }

        .field-row { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .field-row td { vertical-align: top; padding: 2px 0; }
        .field-label { font-size: 8px; text-transform: uppercase; color: #444; letter-spacing: 0.5px; display: block; margin-top: 1px; }
        .field-value { font-size: 11.5px; display: block; }
        .field-value-bold { font-size: 12px; font-weight: bold; }

        .jugement-table { width: 100%; border-collapse: collapse; border-top: 1px solid #000; }
        .jugement-table td { vertical-align: top; padding: 6px 8px; }
        .jugement-label-cell { width: 30px; border-right: 1px solid #000; text-align: center; vertical-align: middle; }
        .jugement-label-vertical { font-size: 8px; text-transform: uppercase; writing-mode: vertical-rl; transform: rotate(180deg); letter-spacing: 1px; white-space: nowrap; }
        .jugement-content-cell { font-size: 10.5px; line-height: 2.0; border-right: 1px solid #000; }
        .jugement-ref-cell { width: 60px; text-align: center; font-size: 10px; line-height: 2.2; }
        .jugement-ref-small { font-size: 8px; color: #555; }

        .mentions-box { border-top: 1px solid #000; padding: 6px 14px; min-height: 40px; }
        .mentions-label { font-size: 8px; text-transform: uppercase; color: #444; letter-spacing: 0.5px; margin-bottom: 4px; }
        .mentions-content { font-size: 10px; min-height: 24px; }

        .footer-row { border-top: 1px solid #000; width: 100%; border-collapse: collapse; }
        .footer-row td { vertical-align: bottom; padding: 8px 14px; }
        .footer-qr-cell { width: 110px; text-align: center; border-right: 1px solid #ccc; }
        .footer-qr-label { font-size: 8px; margin-bottom: 3px; }
        .footer-signature-cell { text-align: right; font-size: 10px; line-height: 1.7; }

        .dotted-line { display: inline-block; width: 75%; border-bottom: 1px dotted #555; vertical-align: middle; }
    </style>
</head>
<body>

<div class="outer-border">

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
                @if($type === 'naissance') Extrait du Registre des Actes de Naissance
                @elseif($type === 'mariage') Extrait du Registre des Actes de Mariage
                @else Extrait du Registre des Actes de Décès
                @endif
            </td>
            <td class="extrait-ref-cell">
                <strong>AN {{ $refYear }}</strong><br>
                <strong>{{ $refYear }} - {{ $refNum }}</strong><br>
                <span class="extrait-ref-label">N° dans le registre en chiffres</span>
            </td>
        </tr>
    </table>

    {{-- ===== BODY CONTENT ===== --}}
    <div class="body-content">

        @if($type === 'naissance')

        <p class="narrative">
            L'an {{ strtolower($yearFr) }}, le {{ $dayFr }} du mois de {{ $monthWord }}
            @if($dob)({{ $dob->format('Y-m-d') }})@endif
        </p>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    @if($timeDisplay)
                    <span class="field-value">Heures : {{ $timeDisplay }}</span>
                    @else
                    <span class="field-value">Heures : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    @endif
                    <span class="field-label">Heure de Naissance</span>
                </td>
                <td style="width:50%">
                    <span class="field-value">Est né(e) à : {{ $act->place_of_birth ?? '' }}</span>
                </td>
            </tr>
        </table>

        <p class="narrative">de sexe : {{ $act->gender === 'M' ? 'Masculin' : ($act->gender === 'F' ? 'Féminin' : 'N/A') }}</p>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->first_name }}</span>
                    <span class="field-label">PRENOM(S)</span>
                </td>
                <td style="width:50%">
                    <span class="field-value field-value-bold">{{ $act->last_name }}</span>
                    <span class="field-label">NOM DE FAMILLE</span>
                </td>
            </tr>
        </table>

        <table class="field-row">
            <tr>
                <td>
                    <span class="field-value">de {{ $act->father_name ?? '' }}</span>
                    <span class="field-label">PRENOM(S) DU PERE</span>
                </td>
            </tr>
        </table>

        <table class="field-row">
            <tr>
                <td style="width:50%">
                    <span class="field-value">et de {{ $motherFirst }}</span>
                    <span class="field-label">PRENOM(S) DE LA MERE</span>
                </td>
                <td style="width:50%">
                    <span class="field-value">{{ strtoupper($motherLast) }}</span>
                    <span class="field-label">NOM DE FAMILLE DE LA MERE</span>
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
                <div class="jugement-label-vertical">
                    JUGEMENT<br>d'Autorisation<br>d'Inscription<br>(ex supplétif)
                </div>
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
                POUR EXTRAIT CERTIFIE CONFORME<br>
                Fait à {{ $center?->commune ?? 'ENAMPORE' }} le, {{ \Carbon\Carbon::parse($act->validated_at ?? now())->locale('fr')->isoFormat('D MMMM YYYY') }}<br>
                L'officier d'Etat-civil soussigné
            </td>
        </tr>
    </table>

</div>

</body>
</html>
