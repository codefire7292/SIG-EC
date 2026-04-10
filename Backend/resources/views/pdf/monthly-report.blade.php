<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Mensuel — E-CRE Kolda</title>
    <style>
        @page { margin: 190px 40px 60px 40px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; }
        header { position: fixed; top: -175px; left: 0; right: 0; height: 170px; text-align: center; padding-bottom: 0px; }
        .header-text { margin-bottom: 5px; font-weight: bold; line-height: 1.2; font-size: 10px; text-transform: uppercase; }
        .report-title { color: #1a365d; font-size: 20px; margin: 5px 0 0; text-transform: uppercase; letter-spacing: 1px; }
        .report-meta { color: #666; margin: 2px 0 0; font-weight: bold; font-size: 10px; }
        .flag-separator { width: 100%; height: 6px; margin: 10px 0; border: none; }
        .flag-green { background: #00853f; width: 33.33%; height: 6px; display: inline-block; float: left; }
        .flag-yellow { background: #fdef42; width: 33.34%; height: 6px; display: inline-block; float: left; text-align: center; line-height: 6px; font-size: 14px; color: #00853f; font-weight: bold; }
        .flag-red { background: #e31b23; width: 33.33%; height: 6px; display: inline-block; float: left; }
        .kpi-grid { margin: 20px 0; }
        .kpi-card { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 12px; }
        .kpi-card h3 { font-size: 13px; color: #718096; margin: 0 0 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-card .value { font-size: 28px; font-weight: bold; color: #1a365d; }
        .kpi-card .unit { font-size: 14px; color: #a0aec0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 12px; text-align: left; }
        th { background: #1a365d; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f7fafc; }
        .footer { text-align: center; margin-top: 30px; color: #a0aec0; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
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
    <div class="report-title">📊 Rapport Mensuel</div>
    <div class="report-meta">Période : {{ $month }} · Généré le {{ $generated }}</div>
    <div class="flag-separator">
        <div class="flag-green"></div><div class="flag-yellow">★</div><div class="flag-red"></div>
    </div>
</header>

<div class="kpi-grid">
    <div class="kpi-card">
        <h3>Taux d'Assiduité</h3>
        <span class="value">{{ $kpis['attendance_rate'] }}</span>
        <span class="unit">%</span>
    </div>

    <div class="kpi-card">
        <h3>Taux de Réussite</h3>
        <span class="value">{{ $kpis['success_rate'] }}</span>
        <span class="unit">%</span>
    </div>

    <div class="kpi-card">
        <h3>Total Apprenants</h3>
        <span class="value">{{ $kpis['total_learners'] }}</span>
    </div>

    <div class="kpi-card">
        <h3>Total Groupes</h3>
        <span class="value">{{ $kpis['total_groups'] }}</span>
    </div>

    <div class="kpi-card">
        <h3>Attestations Délivrées</h3>
        <span class="value">{{ $kpis['total_certificates'] }}</span>
    </div>
</div>

<h2 style="color: #1a365d;">Parité Hommes / Femmes</h2>
<table>
    <thead>
        <tr>
            <th>Genre</th>
            <th>Nombre</th>
            <th>Pourcentage</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Hommes</td>
            <td>{{ $kpis['gender_parity']['male'] }}</td>
            <td>{{ $kpis['gender_parity']['total'] > 0 ? round(100 - $kpis['gender_parity']['ratio'], 1) : 0 }}%</td>
        </tr>
        <tr>
            <td>Femmes</td>
            <td>{{ $kpis['gender_parity']['female'] }}</td>
            <td>{{ $kpis['gender_parity']['ratio'] }}%</td>
        </tr>
    </tbody>
</table>

<div class="footer">
    E-CRE Kolda — Système de Gestion Intégrée · Document généré automatiquement
</div>

</body>
</html>
