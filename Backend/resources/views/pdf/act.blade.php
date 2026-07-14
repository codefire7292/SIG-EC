<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Extrait d'Acte — {{ $act->reference_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e690f; padding-bottom: 10px; }
        .logo { width: 60px; margin-bottom: 5px; }
        .republic { font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 2px; }
        .document-title { font-size: 18px; font-weight: 800; color: #1e690f; text-transform: uppercase; margin: 10px 0; }
        .section-title { font-weight: bold; font-size: 10px; color: #1e690f; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #edf2f7; padding-bottom: 3px; }
        .grid { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .grid td { padding: 4px 0; vertical-align: top; }
        .label { font-weight: bold; color: #4a5568; width: 35%; }
        .value { font-weight: 600; color: #1a202c; }
        .content-box { border: 1px solid #e2e8f0; padding: 15px; background-color: #f8fafc; border-radius: 8px; }
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 15px; position: relative; }
        .qr-code { position: absolute; right: 0; top: 15px; width: 90px; }
        .signature-box { margin-top: 15px; width: 60%; }
        .watermark { position: absolute; top: 35%; left: 15%; font-size: 55px; color: rgba(30, 105, 15, 0.04); transform: rotate(-45deg); z-index: -1; }
        .meta { font-size: 9px; color: #a0aec0; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="watermark">SIG-EC RÉPUBLIQUE</div>

    <div class="header">
        <img src="data:image/png;base64,{{ $logo }}" class="logo">
        <div class="republic">République du Sénégal</div>
        <div class="republic">Mairie de Enampore</div>
        <div class="republic">Direction de l'État Civil</div>
        <div class="document-title">Extrait d'{{ $title }}</div>
    </div>

    <div class="content-box">
        <table class="grid">
            <tr>
                <td class="label">Numéro de Registre :</td>
                <td class="value">{{ $act->reference_number }}</td>
            </tr>
            @if(isset($act->registry) && $act->registry)
            <tr>
                <td class="label">Registre :</td>
                <td class="value">{{ $act->registry->name }} (Année {{ $act->registry->year }})</td>
            </tr>
            @endif
        </table>

        <!-- NAISSANCE -->
        @if($type === 'naissance')
            <div class="section-title">Identité du nouveau-né</div>
            <table class="grid">
                <tr>
                    <td class="label">Prénom(s) & Nom :</td>
                    <td class="value" style="font-size: 14px; text-transform: uppercase;">{{ $act->first_name }} {{ $act->last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Sexe :</td>
                    <td class="value">{{ $act->gender === 'M' ? 'Masculin' : ($act->gender === 'F' ? 'Féminin' : 'N/A') }}</td>
                </tr>
                <tr>
                    <td class="label">Date de naissance :</td>
                    <td class="value">{{ $act->date_of_birth ? $act->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                @if($act->time_of_birth)
                <tr>
                    <td class="label">Heure de naissance :</td>
                    <td class="value">{{ $act->time_of_birth }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Lieu de naissance :</td>
                    <td class="value">{{ $act->place_of_birth ?? 'N/A' }}</td>
                </tr>
                @if($act->health_facility)
                <tr>
                    <td class="label">Formation sanitaire :</td>
                    <td class="value">{{ $act->health_facility }}</td>
                </tr>
                @endif
            </table>

            @if(is_array($act->parents_metadata))
                <div class="section-title">Filiation</div>
                <table class="grid">
                    @if(isset($act->parents_metadata['father']))
                    <tr>
                        <td class="label">Père :</td>
                        <td class="value">{{ $act->parents_metadata['father']['first_name'] ?? '' }} {{ $act->parents_metadata['father']['last_name'] ?? '' }}@if(isset($act->parents_metadata['father']['profession'])), {{ $act->parents_metadata['father']['profession'] }}@endif</td>
                    </tr>
                    @endif
                    @if(isset($act->parents_metadata['mother']))
                    <tr>
                        <td class="label">Mère :</td>
                        <td class="value">{{ $act->parents_metadata['mother']['first_name'] ?? '' }} {{ $act->parents_metadata['mother']['last_name'] ?? '' }}@if(isset($act->parents_metadata['mother']['profession'])), {{ $act->parents_metadata['mother']['profession'] }}@endif</td>
                    </tr>
                    @endif
                </table>
            @endif

        <!-- MARIAGE -->
        @elseif($type === 'mariage')
            <div class="section-title">Les Époux</div>
            <table class="grid">
                <tr>
                    <td class="label">Époux :</td>
                    <td class="value" style="font-size: 13px; text-transform: uppercase;">{{ $act->husband_first_name }} {{ $act->husband_last_name }}</td>
                </tr>
                @if(isset($act->spouses_metadata['husband']['date_of_birth']))
                <tr>
                    <td class="label">Date de naissance époux :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($act->spouses_metadata['husband']['date_of_birth'])->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if(isset($act->spouses_metadata['husband']['profession']))
                <tr>
                    <td class="label">Profession époux :</td>
                    <td class="value">{{ $act->spouses_metadata['husband']['profession'] }}</td>
                </tr>
                @endif
                <tr style="border-top: 1px dashed #edf2f7;">
                    <td class="label">Épouse :</td>
                    <td class="value" style="font-size: 13px; text-transform: uppercase;">{{ $act->wife_first_name }} {{ $act->wife_last_name }}</td>
                </tr>
                @if(isset($act->spouses_metadata['wife']['date_of_birth']))
                <tr>
                    <td class="label">Date de naissance épouse :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($act->spouses_metadata['wife']['date_of_birth'])->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if(isset($act->spouses_metadata['wife']['profession']))
                <tr>
                    <td class="label">Profession épouse :</td>
                    <td class="value">{{ $act->spouses_metadata['wife']['profession'] }}</td>
                </tr>
                @endif
            </table>

            <div class="section-title">Détails du mariage</div>
            <table class="grid">
                <tr>
                    <td class="label">Date de célébration :</td>
                    <td class="value">{{ $act->marriage_date ? $act->marriage_date->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Lieu de célébration :</td>
                    <td class="value">{{ $act->marriage_place ?? 'N/A' }}</td>
                </tr>
                @if($act->marriage_option)
                <tr>
                    <td class="label">Option matrimoniale :</td>
                    <td class="value" style="text-transform: capitalize;">{{ $act->marriage_option }}</td>
                </tr>
                @endif
                @if($act->matrimonial_regime)
                <tr>
                    <td class="label">Régime matrimonial :</td>
                    <td class="value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $act->matrimonial_regime) }}</td>
                </tr>
                @endif
            </table>

        <!-- DECÈS -->
        @elseif($type === 'deces')
            <div class="section-title">Identité du défunt</div>
            <table class="grid">
                <tr>
                    <td class="label">Prénom(s) & Nom :</td>
                    <td class="value" style="font-size: 14px; text-transform: uppercase;">{{ $act->deceased_first_name }} {{ $act->deceased_last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Sexe :</td>
                    <td class="value">{{ $act->gender === 'M' ? 'Masculin' : ($act->gender === 'F' ? 'Féminin' : 'N/A') }}</td>
                </tr>
                @if($act->date_of_birth)
                <tr>
                    <td class="label">Date de naissance :</td>
                    <td class="value">{{ $act->date_of_birth ? $act->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Date du décès :</td>
                    <td class="value">{{ $act->date_of_death ? $act->date_of_death->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                @if($act->time_of_death)
                <tr>
                    <td class="label">Heure du décès :</td>
                    <td class="value">{{ $act->time_of_death }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Lieu du décès :</td>
                    <td class="value">{{ $act->place_of_death ?? 'N/A' }}</td>
                </tr>
            </table>

            @if(is_array($act->death_metadata))
                @if(isset($act->death_metadata['father']) || isset($act->death_metadata['mother']))
                <div class="section-title">Filiation</div>
                <table class="grid">
                    @if(isset($act->death_metadata['father']))
                    <tr>
                        <td class="label">Père :</td>
                        <td class="value">{{ $act->death_metadata['father']['first_name'] ?? '' }} {{ $act->death_metadata['father']['last_name'] ?? '' }}</td>
                    </tr>
                    @endif
                    @if(isset($act->death_metadata['mother']))
                    <tr>
                        <td class="label">Mère :</td>
                        <td class="value">{{ $act->death_metadata['mother']['first_name'] ?? '' }} {{ $act->death_metadata['mother']['last_name'] ?? '' }}</td>
                    </tr>
                    @endif
                </table>
                @endif

                @if(isset($act->death_metadata['declarant']))
                <div class="section-title">Déclarant</div>
                <table class="grid">
                    <tr>
                        <td class="label">Prénom(s) & Nom :</td>
                        <td class="value">{{ $act->death_metadata['declarant']['first_name'] ?? '' }} {{ $act->death_metadata['declarant']['last_name'] ?? '' }}</td>
                    </tr>
                    @if(isset($act->death_metadata['declarant']['relationship']))
                    <tr>
                        <td class="label">Degré de parenté :</td>
                        <td class="value">{{ $act->death_metadata['declarant']['relationship'] }}</td>
                    </tr>
                    @endif
                </table>
                @endif
            @endif
        @endif

        <!-- JUGEMENT (si applicable) -->
        @if($act->is_judgment)
            <div class="section-title">Jugement d'autorisation</div>
            <table class="grid">
                @if($act->judgment_number)
                <tr>
                    <td class="label">N° Jugement :</td>
                    <td class="value">{{ $act->judgment_number }}</td>
                </tr>
                @endif
                @if($act->judgment_date)
                <tr>
                    <td class="label">Date du jugement :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($act->judgment_date)->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($act->judgment_court)
                <tr>
                    <td class="label">Tribunal :</td>
                    <td class="value">{{ $act->judgment_court }}</td>
                </tr>
                @endif
            </table>
        @endif
    </div>

    <div class="footer">
        <div class="signature-box">
            <div class="label">Fait à Enampore, le {{ $act->validated_at ? $act->validated_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
            <div style="margin-top: 10px; font-weight: bold;">L'Officier de l'État Civil</div>
            <div style="margin-top: 5px; font-style: italic; font-size: 11px;">(Signé Électroniquement)</div>
        </div>

        <div class="qr-code">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="90">
            <div style="font-size: 8px; text-align: center; margin-top: 5px;">Scanner pour vérifier l'authenticité</div>
        </div>

        <div class="meta">
            Document officiel généré par SIG-EC le {{ $timestamp }} | ID Unique: {{ $act->uuid }}
        </div>
    </div>
</body>
</html>
