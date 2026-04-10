<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de Réussite — E-CRE Kolda</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 40px; color: #333; }
        .border-frame { border: 3px double #1a365d; padding: 40px; min-height: 600px; position: relative; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1a365d; font-size: 26px; margin: 0; letter-spacing: 2px; text-transform: uppercase; }
        .header h2 { color: #2b6cb0; font-size: 18px; margin: 10px 0 0; font-weight: normal; }
        .header .subtitle { color: #718096; font-size: 12px; margin-top: 5px; }
        .content { text-align: center; margin: 40px 0; }
        .content .certify { font-size: 14px; color: #718096; margin-bottom: 15px; }
        .content .name { font-size: 28px; font-weight: bold; color: #1a365d; border-bottom: 2px solid #2b6cb0; display: inline-block; padding-bottom: 5px; margin-bottom: 15px; }
        .content .module { font-size: 16px; color: #4a5568; margin: 10px 0; }
        .content .module strong { color: #1a365d; }
        .meta { margin-top: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .meta .date { font-size: 12px; color: #718096; }
        .meta .qr { text-align: right; }
        .meta .qr p { font-size: 9px; color: #a0aec0; margin: 5px 0 0; }
        .uuid { text-align: center; margin-top: 20px; font-size: 10px; color: #a0aec0; letter-spacing: 1px; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; color: #a0aec0; }
    </style>
</head>
<body>

<div class="border-frame">
    <div class="header">
        <h1>Attestation de Réussite</h1>
        <h2>Centre de Recherche et d'Essais — Kolda</h2>
        <p class="subtitle">République du Sénégal</p>
    </div>

    <div class="content">
        <p class="certify">Le Directeur du CRE de Kolda certifie que</p>
        <p class="name">{{ $user->name }}</p>
        <p class="module">a suivi avec succès le module</p>
        <p class="module"><strong>{{ $module->titre }}</strong> ({{ $module->code_module }})</p>
        <p class="module">d'une durée de <strong>{{ $module->quota_heures }} heures</strong></p>
    </div>

    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td style="border: none; width: 50%; vertical-align: bottom;">
                <p style="font-size: 12px; color: #718096;">Délivrée le {{ $issuedAt }}</p>
                <p style="font-size: 12px; color: #718096; margin-top: 30px;">Le Directeur du CRE</p>
                <p style="border-top: 1px solid #ccc; width: 200px; margin-top: 40px; padding-top: 5px; font-size: 10px; color: #a0aec0;">Signature</p>
            </td>
            <td style="border: none; width: 50%; text-align: right; vertical-align: bottom;">
                {!! $qrCodeSvg !!}
                <p style="font-size: 9px; color: #a0aec0; margin-top: 5px;">Scannez pour vérifier l'authenticité</p>
            </td>
        </tr>
    </table>

    <div class="uuid">
        N° {{ $certificate->uuid }}
    </div>
</div>

<div class="footer">
    Document infalsifiable — Vérification en ligne : {{ url('/verify-certificate/' . $certificate->uuid) }}
</div>

</body>
</html>
