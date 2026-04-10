<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CERTIFICATION OFFICIELLE - {{ $student->name }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #020617;
            color: #ffffff;
            width: 297mm;
            height: 210mm;
        }
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .bg-img {
            width: 100%;
            height: 100%;
        }
        .wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 10;
            text-align: center;
        }
        
        /* Content Positioning */
        .header {
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
        }
        .header-title {
            font-size: 48pt;
            font-weight: bold;
            letter-spacing: 15px;
            margin: 0;
            color: #ffffff;
        }
        .header-subtitle {
            font-size: 10pt;
            letter-spacing: 8px;
            color: #fbbf24;
            margin-top: 5px;
        }

        .content {
            position: absolute;
            top: 220px;
            left: 0;
            right: 0;
        }
        .label {
            font-size: 9pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 20px;
        }
        .student-name {
            font-size: 50pt;
            font-weight: bold;
            color: #ffffff;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .module-label {
            font-size: 9pt;
            color: #94a3b8;
            margin-top: 40px;
            letter-spacing: 4px;
        }
        .module-title {
            font-size: 24pt;
            font-weight: bold;
            color: #22d3ee;
            margin-top: 10px;
            font-style: italic;
        }

        .footer {
            position: absolute;
            bottom: 70px;
            left: 100px;
            right: 100px;
        }
        .signature-table {
            width: 100%;
        }
        .sig-col {
            width: 30%;
            text-align: center;
        }
        .sig-line {
            width: 150px;
            border-top: 1px solid #334155;
            margin: 10px auto;
        }
        .sig-text {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
        }
        .qr-col {
            width: 40%;
            text-align: center;
        }
        .qr-container {
            background: white;
            padding: 10px;
            display: inline-block;
            border-radius: 10px;
            position: relative;
        }
        .qr-img {
            width: 80px;
            height: 80px;
        }
        .qr-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            margin-left: -10px;
            background: white;
            padding: 1px;
            border-radius: 4px;
        }
        .qr-label {
            font-size: 7px;
            color: #22d3ee;
            margin-top: 5px;
            font-weight: bold;
        }

        .photo-container {
            width: 70px;
            height: 70px;
            border: 1px solid #1e293b;
            padding: 2px;
            background: #0f172a;
            border-radius: 12px;
            display: inline-block;
            position: relative;
        }
        .photo-img {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            object-fit: cover;
        }
        .photo-badge {
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 50px;
            margin-left: -25px;
            background: #22d3ee;
            color: #020617;
            font-size: 5px;
            font-weight: bold;
            padding: 1px 0;
            border-radius: 2px;
        }

        .meta {
            position: absolute;
            bottom: 30px;
            left: 100px;
            right: 100px;
            font-size: 7pt;
            color: #1e293b;
        }
        .meta-id { float: left; }
        .meta-date { float: right; color: #475569; font-weight: bold; }
    </style>
</head>
<body>
    <div class="background">
        <img src="{{ public_path('images/quantum-certificate-bg.png') }}" class="bg-img">
    </div>

    <div class="wrapper">
        <div class="header">
            <h1 class="header-title">CERTIFICATION</h1>
            <div class="header-subtitle">QUANTUM NEXUS INSTITUTE</div>
        </div>

        <div class="content">
            <div class="label">Ce document certifie officiellement que</div>
            <div class="student-name">{{ $student->name }}</div>
            <div class="module-label">A validé avec distinction le module</div>
            <div class="module-title">{{ $module->titre ?? $module->title }}</div>
        </div>

        <div class="footer">
            <table class="signature-table">
                <tr>
                    <td class="sig-col">
                        <div class="photo-container">
                            @if($student->profile_photo_path)
                                <img src="{{ public_path('storage/' . $student->profile_photo_path) }}" class="photo-img">
                            @else
                                <div style="color: #1e293b; padding-top: 15px; font-size: 20px;">👤</div>
                            @endif
                            <div class="photo-badge">VERIFIED ID</div>
                        </div>
                        <div class="sig-line"></div>
                        <div class="sig-text">Direction du Centre</div>
                    </td>
                    <td class="qr-col">
                        <div class="qr-container">
                            <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-img">
                            <div class="qr-logo-overlay">
                                <img src="{{ public_path('images/logo-cre.png') }}" style="width: 100%; height: 100%;">
                            </div>
                        </div>
                        <div class="qr-label">SCELLÉ DIGITAL AUTHENTIQUE</div>
                    </td>
                    <td class="sig-col">
                        <div style="height: 70px;"></div> {{-- Spacer for symmetry with photo --}}
                        <div class="sig-line"></div>
                        <div class="sig-text">Bureau du Registraire</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="meta">
            <span class="meta-id">AUTH-TOKEN: {{ $certificate->uuid }}</span>
            <span class="meta-date">DÉLIVRÉ LE {{ $certificate->issued_at->format('d/m/Y') }}</span>
        </div>
    </div>
</body>
</html>
