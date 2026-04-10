<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Effectifs par Module — E-CRE</title>
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
        .info { margin-bottom: 20px; padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #0891b2; }
        .summary-box { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #020617; color: white; border-radius: 8px; font-weight: bold; font-size: 14px; }
        h2 { color: #020617; font-size: 14px; border-left: 4px solid #0891b2; padding-left: 10px; margin: 25px 0 10px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; }
        th { background: #0f172a; color: white; font-weight: bold; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:nth-child(even) { background: #f1f5f9; }
        .pct-bar-container { width: 80px; background: #e2e8f0; height: 8px; border-radius: 4px; display: inline-block; vertical-align: middle; margin-right: 10px; }
        .pct-bar-fill { height: 100%; background: #0891b2; border-radius: 4px; }
        .pct-text { font-weight: bold; color: #0891b2; }
        .gender-m { color: #2563eb; font-weight: bold; }
        .gender-f { color: #db2777; font-weight: bold; }
        .footer { text-align: center; margin-top: 40px; color: #9ca3af; font-size: 9px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
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
    <div class="report-title">📈 Rapport Effectifs par Module</div>
    <div class="report-meta">Généré le {{ $generated }}</div>
    <div class="flag-separator">
        <div class="flag-green"></div><div class="flag-yellow">★</div><div class="flag-red"></div>
    </div>
</header>

<div class="info">
    <strong>Groupe filtré :</strong> {{ $data['group'] }} <br>
    <strong>Période :</strong> {{ $data['period'] }}
    <br>
    <div class="summary-box">
        Total Apprenants : {{ $data['total_students'] }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Titre du Module</th>
            <th style="text-align: center;">Total</th>
            <th style="text-align: center;">H</th>
            <th style="text-align: center;">F</th>
            <th>Distribution (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['modules'] as $module)
        <tr>
            <td style="width: 70px;"><code>{{ $module['code'] }}</code></td>
            <td><strong>{{ $module['name'] }}</strong></td>
            <td style="text-align: center; font-size: 12px;"><strong>{{ $module['count'] }}</strong></td>
            <td style="text-align: center;" class="gender-m">{{ $module['males'] }}</td>
            <td style="text-align: center;" class="gender-f">{{ $module['females'] }}</td>
            <td style="white-space: nowrap;">
                <div class="pct-bar-container">
                    <div class="pct-bar-fill" style="width: {{ $module['percentage'] }}%;"></div>
                </div>
                <span class="pct-text">{{ $module['percentage'] }}%</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    E-CRE Kolda — Analyse de la Distribution Pédagogique · Document confidentiel
</div>

</body>
</html>
