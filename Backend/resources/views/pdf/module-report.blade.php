<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Formés par Module — E-CRE</title>
    <style>
        @page { margin: 190px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; margin: 0; }
        header { position: fixed; top: -175px; left: 0; right: 0; height: 170px; text-align: center; padding-bottom: 0px; }
        .header-text { margin-bottom: 5px; font-weight: bold; line-height: 1.2; font-size: 10px; text-transform: uppercase; }
        .report-title { color: #020617; font-size: 16px; margin: 5px 0 0; text-transform: uppercase; letter-spacing: 1px; }
        .report-meta { color: #64748b; margin: 2px 0 0; font-weight: bold; font-size: 9px; }
        .flag-separator { width: 100%; height: 6px; margin: 10px 0; border: none; }
        .flag-green { background: #00853f; width: 33.33%; height: 6px; display: inline-block; float: left; }
        .flag-yellow { background: #fdef42; width: 33.34%; height: 6px; display: inline-block; float: left; text-align: center; line-height: 6px; font-size: 14px; color: #00853f; font-weight: bold; }
        .flag-red { background: #e31b23; width: 33.33%; height: 6px; display: inline-block; float: left; }
        .info { margin-bottom: 20px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #0891b2; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
        th { background: #0f172a; color: white; font-weight: bold; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer { text-align: center; margin-top: 40px; color: #9ca3af; font-size: 9px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

<header>
    <div class="header-text">
        Ministère de l’Enseignement Supérieur de la Recherche et de l’Innovation<br>
        Direction Générale de la Recherche<br>
        Centre de Recherche et d’Essais de Kolda
    </div>
    <img src="{{ public_path('images/logo-cre.png') }}" style="height: 65px; margin: 5px 0;">
    <div class="report-title">📜 Rapport Formés par Module</div>
    <div class="report-meta">Généré le {{ $generated }}</div>
    <div class="flag-separator">
        <div class="flag-green"></div><div class="flag-yellow">★</div><div class="flag-red"></div>
    </div>
</header>

<div class="info">
    <strong>Module :</strong> {{ $data['module'] }} <br>
    <strong>Description :</strong> Liste des apprenants ayant obtenu une attestation pour ce module.
</div>

<table>
    <thead>
        <tr>
            <th style="width: 70%;">Nom de l'Apprenant</th>
            <th style="text-align: center;">Date d'Obtention</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($data['students']) && count($data['students']) > 0)
            @foreach($data['students'] as $student)
            <tr>
                <td><strong>{{ $student['name'] }}</strong></td>
                <td style="text-align: center;">{{ $student['date'] }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2" style="text-align: center; color: #6b7280; font-style: italic;">Aucun apprenant formé trouvé pour ce module.</td>
            </tr>
        @endif
    </tbody>
</table>

<div class="footer">
    E-CRE Kolda — Système de Gestion Intégrée · Document officiel
</div>

</body>
</html>
