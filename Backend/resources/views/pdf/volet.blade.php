<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>VOLET {{ $volet }} - ACTE DE {{ strtoupper($title) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 10mm 6mm 10mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13.5px;
            line-height: 1.45;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            border: 1px dashed #444;
            padding: 10px 12px;
            box-sizing: border-box;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-title {
            font-weight: bold;
            font-size: 13.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .volet-badge {
            font-size: 24px;
            font-weight: 900;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.1;
        }

        .sub-badge {
            font-size: 9.5px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
            color: #333;
        }

        .box-digit {
            display: inline-block;
            border: 1px solid #000;
            width: 14px;
            height: 16px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            line-height: 16px;
            margin: 0 1px;
            vertical-align: middle;
        }

        .box-code {
            display: inline-block;
            border: 1px solid #000;
            width: 12px;
            height: 13px;
            margin: 0 1px;
            vertical-align: middle;
        }

        .act-title-container {
            text-align: center;
            margin: 10px 0 8px 0;
        }

        .act-title {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            line-height: 1.1;
        }

        .act-num {
            font-size: 13.5px;
            font-weight: bold;
            margin-top: 3px;
        }

        .section-header {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            background-color: #f0f0f0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 6px;
            margin-top: 8px;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .field-row {
            margin-bottom: 4.5px;
            width: 100%;
            line-height: 1.45;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            font-weight: bold;
            padding-left: 4px;
            padding-right: 4px;
            min-height: 16px;
            line-height: 1.3;
        }

        .field-label {
            font-size: 13.5px;
        }

        .signatures-table {
            margin-top: 12px;
            border-top: 1px solid #000;
            padding-top: 6px;
        }

        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 12px;
            font-weight: bold;
        }

        .stamp-box {
            margin-top: 6px;
            min-height: 60px;
        }

        .marginal-mentions {
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .marginal-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .marginal-line {
            border-bottom: 1px dotted #888;
            height: 18px;
            width: 100%;
        }

        .watermark-qr {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 60px;
            height: 60px;
        }
    </style>
</head>
<body>

@php
    // Helper to format string into array of single digits/chars
    function toDigitArray($val, $length = null) {
        $str = (string) $val;
        if ($length && strlen($str) < $length) {
            $str = str_pad($str, $length, '0', STR_PAD_LEFT);
        }
        return mb_str_split($str);
    }

    $reg = $act->registry;
    $centerObj = $center ?? ($reg ? $reg->center : null);
    $yearStr = '';
    if (!empty($act->date_of_birth)) {
        $yearStr = $act->date_of_birth->format('Y');
    } elseif (!empty($act->act_registration_date)) {
        $yearStr = $act->act_registration_date->format('Y');
    } else {
        $yearStr = date('Y');
    }

    $refDigits = toDigitArray(preg_replace('/[^0-9]/', '', $act->reference_number ?? '0'), 6);
    $yearDigits = toDigitArray($yearStr, 4);

    $birthDayDigits = $act->date_of_birth ? toDigitArray($act->date_of_birth->format('d'), 2) : ['0','0'];
    $birthMonthDigits = $act->date_of_birth ? toDigitArray($act->date_of_birth->format('m'), 2) : ['0','0'];
    $birthYearDigits = $act->date_of_birth ? toDigitArray($act->date_of_birth->format('Y'), 4) : ['0','0','0','0'];

    $declDate = $act->act_registration_date ?? now();
    $declDayDigits = toDigitArray($declDate->format('d'), 2);
    $declMonthDigits = toDigitArray($declDate->format('m'), 2);
    $declYearDigits = toDigitArray($declDate->format('Y'), 4);
@endphp

<div class="container">

    {{-- ===== HEADER BLOCK ===== --}}
    <table class="header-table">
        <tr>
            <td style="width: 45%;">
                <div class="header-title">REPUBLIQUE DU SENEGAL</div>
                <div style="font-size: 11px; margin-top: 2px; line-height: 1.4;">
                    <strong>REGION :</strong> <span class="dotted-line" style="min-width: 110px;">{{ strtoupper($centerObj->region ?? 'ZIGUINCHOR') }}</span><br>
                    <strong>DEPARTEMENT :</strong> <span class="dotted-line" style="min-width: 90px;">{{ strtoupper($centerObj->departement ?? $centerObj->region ?? 'ZIGUINCHOR') }}</span><br>
                    <strong>ARRONDISSEMENT :</strong> <span class="dotted-line" style="min-width: 80px;">{{ strtoupper($centerObj->arrondissement ?? 'NYASSIA') }}</span><br>
                    <strong>COMMUNE DE :</strong> <span class="dotted-line" style="min-width: 100px;">{{ strtoupper($centerObj->commune ?? 'ENAMPORE') }}</span><br>
                    <strong>CENTRE :</strong> <span class="dotted-line" style="min-width: 80px;">{{ strtoupper($centerObj->name ?? 'CENTRE PRINCIPAL DE ENAMPORE') }}</span>
                </div>
            </td>
            <td style="width: 30%; text-align: center;">
                <div style="font-weight: bold; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">ANNEE</div>
                <div style="margin-top: 2px;">
                    @foreach($yearDigits as $d)
                        <span class="box-digit">{{ $d }}</span>
                    @endforeach
                </div>
            </td>
            <td style="width: 25%; text-align: right;">
                <div class="volet-badge">VOLET {{ $volet ?? 1 }}</div>
                <div class="sub-badge">
                    ({{ ($volet ?? 1) == 2 ? "GREFFE DU TRIBUNAL D'INSTANCE" : (($volet ?? 1) == 3 ? "TITULAIRE DE L'ACTE" : "CENTRE D'ÉTAT CIVIL - MAIRIE") }})
                </div>
                @if(!empty($qrCode))
                <div style="margin-top: 4px;">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 65px; height: 65px; border: 1px solid #ddd; padding: 2px; background: #fff;" alt="QR Code Vérification">
                    <div style="font-size: 7.5px; font-weight: bold; color: #444; margin-top: 1px;">VÉRIFICATION QR</div>
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ===== TITLE BLOCK ===== --}}
    <div class="act-title-container">
        <div class="act-title">ACTE DE {{ strtoupper($title) }}</div>
        <div class="act-num">
            N° 
            @foreach($refDigits as $d)
                <span class="box-digit">{{ $d }}</span>
            @endforeach
            &nbsp; <span class="box-code"></span><span class="box-code"></span> TD
        </div>
    </div>

    @if($type === 'naissance')
    {{-- ===== RENSEIGNEMENTS SUR L'ENFANT ===== --}}
    <div class="section-header">RENSEIGNEMENTS SUR L'ENFANT</div>
    <div class="field-row">
        <strong>Prénoms :</strong> <span class="dotted-line" style="width: 230px;">{{ $act->first_name }}</span>
        <strong style="margin-left: 15px;">NOM :</strong> <span class="dotted-line" style="width: 180px;">{{ strtoupper($act->last_name) }}</span>
    </div>
    <div class="field-row">
        <strong>Sexe :</strong> <span class="dotted-line" style="width: 140px;">{{ in_array(strtoupper($act->gender ?? ''), ['F', 'FEMININ']) ? 'Féminin' : 'Masculin' }}</span>
        <strong style="margin-left: 20px;">Date de Naissance :</strong> 
        @foreach($birthDayDigits as $d)<span class="box-digit">{{ $d }}</span>@endforeach JJ
        @foreach($birthMonthDigits as $d)<span class="box-digit">{{ $d }}</span>@endforeach MM
        @foreach($birthYearDigits as $d)<span class="box-digit">{{ $d }}</span>@endforeach ANNEE
    </div>
    <div class="field-row">
        <strong>Heure :</strong> <span class="dotted-line" style="width: 90px;">{{ $act->time_of_birth ?? '' }}</span>
        <strong style="margin-left: 15px;">Lieu de Naissance :</strong> <span class="dotted-line" style="width: 260px;">{{ $act->place_of_birth }}</span>
    </div>
    <div class="field-row">
        <strong>Formation Sanitaire :</strong> <span class="dotted-line" style="width: 320px;">{{ $act->health_facility ?? 'Né à domicile / Centre de santé' }}</span>
        <span style="float: right;"><span class="box-code"></span> FS</span>
    </div>

    {{-- ===== RENSEIGNEMENTS SUR LE PERE ===== --}}
    @php
        $getMeta = function($keyFlat, $keyNestedCat, $keyNestedField, $default = '') use ($act) {
            $meta = $act->parents_metadata ?? [];
            if (is_array($meta)) {
                if (!empty($meta[$keyFlat])) return $meta[$keyFlat];
                if (isset($meta[$keyNestedCat]) && is_array($meta[$keyNestedCat]) && !empty($meta[$keyNestedCat][$keyNestedField])) {
                    return $meta[$keyNestedCat][$keyNestedField];
                }
            }
            return $default;
        };

        $fatherName = $act->father_name ?? $getMeta('father_name', 'father', 'name', '');
        $fatherParts = explode(' ', trim($fatherName));
        $fatherLastName = count($fatherParts) > 1 ? array_pop($fatherParts) : ($getMeta('father_last_name', 'father', 'last_name') ?: $act->last_name);
        $fatherFirstName = count($fatherParts) > 0 ? implode(' ', $fatherParts) : $fatherName;

        $fatherBirthDate = $getMeta('father_date_of_birth', 'father', 'birth_date', '');
        $hasFBirth = !empty($fatherBirthDate) && strtotime($fatherBirthDate);
        $fDay = $hasFBirth ? toDigitArray(date('d', strtotime($fatherBirthDate)), 2) : ['&nbsp;','&nbsp;'];
        $fMonth = $hasFBirth ? toDigitArray(date('m', strtotime($fatherBirthDate)), 2) : ['&nbsp;','&nbsp;'];
        $fYear = $hasFBirth ? toDigitArray(date('Y', strtotime($fatherBirthDate)), 4) : ['&nbsp;','&nbsp;','&nbsp;','&nbsp;'];
        
        $fatherPlace = $getMeta('father_place_of_birth', 'father', 'birth_place', '');
        $fatherJob = $getMeta('father_profession', 'father', 'profession', '');
        $fatherAddress = $getMeta('father_domicile', 'father', 'residence', '');
    @endphp
    <div class="section-header">RENSEIGNEMENTS SUR LE PERE</div>
    <div class="field-row">
        <strong>Prénoms :</strong> <span class="dotted-line" style="width: 230px;">{{ $fatherFirstName }}</span>
        <strong style="margin-left: 15px;">NOM :</strong> <span class="dotted-line" style="width: 180px;">{{ strtoupper($fatherLastName) }}</span>
    </div>
    <div class="field-row">
        <strong>Date de Naissance :</strong> 
        @foreach($fDay as $d)<span class="box-digit">{!! $d !!}</span>@endforeach JJ
        @foreach($fMonth as $d)<span class="box-digit">{!! $d !!}</span>@endforeach MM
        @foreach($fYear as $d)<span class="box-digit">{!! $d !!}</span>@endforeach ANNEE
    </div>
    <div class="field-row">
        <strong>Lieu de naissance :</strong> <span class="dotted-line" style="width: 320px;">{{ $fatherPlace }}</span>
    </div>
    <div class="field-row">
        <strong>Profession :</strong> <span class="dotted-line" style="width: 220px;">{{ $fatherJob }}</span>
        <strong style="margin-left: 15px;">Domicile :</strong> <span class="dotted-line" style="width: 180px;">{{ $fatherAddress }}</span>
    </div>

    {{-- ===== RENSEIGNEMENTS SUR LA MERE ===== --}}
    @php
        $motherName = $act->mother_name ?? $getMeta('mother_name', 'mother', 'name', '');
        $motherParts = explode(' ', trim($motherName));
        if (count($motherParts) > 1) {
            $motherLastName = array_pop($motherParts);
            $motherFirstName = implode(' ', $motherParts);
        } else {
            $motherFirstName = $motherParts[0] ?? $motherName;
            $motherLastName = $getMeta('mother_last_name', 'mother', 'last_name', '');
        }

        $motherBirthDate = $getMeta('mother_date_of_birth', 'mother', 'birth_date', '');
        $hasMBirth = !empty($motherBirthDate) && strtotime($motherBirthDate);
        $mDay = $hasMBirth ? toDigitArray(date('d', strtotime($motherBirthDate)), 2) : ['&nbsp;','&nbsp;'];
        $mMonth = $hasMBirth ? toDigitArray(date('m', strtotime($motherBirthDate)), 2) : ['&nbsp;','&nbsp;'];
        $mYear = $hasMBirth ? toDigitArray(date('Y', strtotime($motherBirthDate)), 4) : ['&nbsp;','&nbsp;','&nbsp;','&nbsp;'];

        $motherPlace = $getMeta('mother_place_of_birth', 'mother', 'birth_place', '');
        $motherJob = $getMeta('mother_profession', 'mother', 'profession', '');
        $motherAddress = $getMeta('mother_domicile', 'mother', 'residence', '');
    @endphp
    <div class="section-header">RENSEIGNEMENTS SUR LA MERE</div>
    <div class="field-row">
        <strong>Prénoms :</strong> <span class="dotted-line" style="width: 230px;">{{ $motherFirstName }}</span>
        <strong style="margin-left: 15px;">NOM :</strong> <span class="dotted-line" style="width: 180px;">{{ strtoupper($motherLastName) }}</span>
    </div>
    <div class="field-row">
        <strong>Date de Naissance :</strong> 
        @foreach($mDay as $d)<span class="box-digit">{!! $d !!}</span>@endforeach JJ
        @foreach($mMonth as $d)<span class="box-digit">{!! $d !!}</span>@endforeach MM
        @foreach($mYear as $d)<span class="box-digit">{!! $d !!}</span>@endforeach ANNEE
    </div>
    <div class="field-row">
        <strong>Lieu de naissance :</strong> <span class="dotted-line" style="width: 320px;">{{ $motherPlace }}</span>
    </div>
    <div class="field-row">
        <strong>Profession :</strong> <span class="dotted-line" style="width: 220px;">{{ $motherJob }}</span>
        <strong style="margin-left: 15px;">Domicile :</strong> <span class="dotted-line" style="width: 180px;">{{ $motherAddress }}</span>
    </div>

    {{-- ===== SUR LA DECLARATION DE ===== --}}
    @php
        $declarantRel = $getMeta('declarant_relationship', 'declarant', 'relationship', '');
        $declarantFN = $getMeta('declarant_first_name', 'declarant', 'first_name', '');
        $declarantLN = $getMeta('declarant_last_name', 'declarant', 'last_name', '');
        $declarantFull = trim($declarantFN . ' ' . $declarantLN);

        $declLabel = 'PERE';
        if ($fatherFirstName || $fatherName) {
            $declLabel = 'PERE';
        } elseif ($motherFirstName || $motherName) {
            $declLabel = 'MERE';
        } elseif ($declarantRel) {
            $declLabel = strtoupper($declarantRel);
        }
    @endphp
    <div class="section-header">SUR LA DECLARATION DE</div>
    <div class="field-row">
        <strong>SUR LA DECLARATION DE :</strong> <span class="dotted-line" style="width: 140px;">{{ $declLabel }}</span>
        <strong style="margin-left: 10px;">OU DE :</strong> <span class="dotted-line" style="width: 200px;">{{ $declarantFull }}</span>
    </div>
    <div class="field-row">
        <strong>Numéro d'identification / Référence :</strong> <span class="dotted-line" style="width: 310px;">{{ $getMeta('marriage_cert', 'declarant', 'marriage_cert') ?: ($getMeta('declarant_id_number', 'declarant', 'id_number') ?: ('Acte N° ' . $act->reference_number)) }}</span>
    </div>
    <div class="field-row">
        <strong>Date et Heure de la Déclaration :</strong> 
        @foreach($declDayDigits as $d)<span class="box-digit">{!! $d !!}</span>@endforeach JJ
        @foreach($declMonthDigits as $d)<span class="box-digit">{!! $d !!}</span>@endforeach MM
        @foreach($declYearDigits as $d)<span class="box-digit">{!! $d !!}</span>@endforeach ANNEE
    </div>
    @if($act->is_judgment)
    <div class="field-row">
        <strong>Jugement d'Autorisation N° :</strong> <span class="dotted-line" style="width: 120px;">{{ $act->judgment_number }}</span>
        <strong style="margin-left: 10px;">du :</strong> <span class="dotted-line" style="width: 120px;">{{ optional($act->judgment_date)->format('d/m/Y') }}</span>
        <strong style="margin-left: 10px;">par :</strong> <span class="dotted-line" style="width: 130px;">{{ $act->judgment_court ?? 'Tribunal' }}</span>
    </div>
    @endif

    @else
    {{-- Generic / Other Act Types --}}
    <div class="section-header">RENSEIGNEMENTS GENERAUX</div>
    <div class="field-row">
        <strong>Nom & Prénoms :</strong> <span class="dotted-line" style="width: 380px;">{{ $act->first_name }} {{ strtoupper($act->last_name) }}</span>
    </div>
    <div class="field-row">
        <strong>Date et Lieu :</strong> <span class="dotted-line" style="width: 380px;">{{ optional($act->date_of_birth ?? $act->marriage_date ?? $act->date_of_death)->format('d/m/Y') }} à {{ $act->place_of_birth ?? $act->marriage_place ?? $act->place_of_death }}</span>
    </div>
    @endif

    {{-- ===== EN FOI DE QUOI ===== --}}
    <div class="signatures-table">
        <div style="margin-bottom: 6px;">
            <strong>EN FOI DE QUOI, NOUS AVONS REDIGE LE PRESENT ACTE</strong><br>
            Fait à <span class="dotted-line" style="min-width: 130px;">{{ strtoupper($centerObj->commune ?? $centerObj->name ?? 'KOLDA') }}</span>, le 
            <span class="dotted-line" style="min-width: 120px;">{{ $declDate->format('d / m / Y') }}</span>
        </div>
        <table>
            <tr>
                <td>
                    <div style="margin-bottom: 40px; font-size: 10px; font-weight: bold;">L'Officier d'Etat Civil</div>
                    <div class="stamp-box">
                        @if(isset($mayor_signature) && $mayor_signature)
                            <img src="{{ $mayor_signature }}" style="max-height: 65px; margin-top: 2px;">
                        @endif
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 40px; font-size: 10px; font-weight: bold;">Le Déclarant</div>
                    <div class="stamp-box"></div>
                </td>
                <td>
                    <div style="margin-bottom: 40px; font-size: 10px; font-weight: bold;">Les Témoins</div>
                    <div class="stamp-box"></div>
                </td>
            </tr>
        </table>
        @if(!empty($timestamp))
        <div style="font-size: 8px; color: #555; text-align: right; margin-top: 6px;">
            Document officiel numérisé et certifié conforme le {{ $timestamp }} — QR Code de vérification intégré.
        </div>
        @endif
    </div>

    {{-- ===== MENTIONS MARGINALES ===== --}}
    <div class="marginal-mentions" style="margin-top: 14px; border-top: 1.5px solid #000; padding-top: 8px;">
        <div class="marginal-title">MENTIONS MARGINALES</div>
        @if(!empty($act->officer_comments))
            <div style="font-size: 10px; font-weight: bold; font-style: italic; white-space: pre-line; line-height: 1.3;">{!! nl2br(e($act->officer_comments)) !!}</div>
        @else
            <div class="marginal-line"></div>
            <div class="marginal-line"></div>
            <div class="marginal-line"></div>
        @endif
    </div>

</div>

</body>
</html>
