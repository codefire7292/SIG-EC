<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->reference_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a365d; padding-bottom: 10px; }
        .logo { width: 80px; margin-bottom: 10px; }
        .republic { font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .document-title { font-size: 22px; font-weight: 800; color: #1a365d; text-transform: uppercase; margin: 20px 0; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; color: #4a5568; font-size: 12px; }
        .value { font-size: 14px; font-weight: 600; }
        .content-box { border: 1px solid #e2e8f0; padding: 20px; background-color: #f8fafc; border-radius: 8px; }
        .footer { margin-top: 50px; border-top: 1px solid #e2e8f0; pt: 20px; position: relative; }
        .qr-code { position: absolute; right: 0; top: 10px; width: 100px; }
        .signature-box { margin-top: 30px; }
        .watermark { position: absolute; top: 40%; left: 15%; font-size: 60px; color: rgba(26, 54, 93, 0.05); transform: rotate(-45deg); z-index: -1; }
        .meta { font-size: 10px; color: #a0aec0; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="watermark">SIG-EC RÉPUBLIQUE</div>

    <div class="header">
        <img src="data:image/png;base64,{{ $logo }}" class="logo">
        <div class="republic">République du Sénégal</div>
        <div class="republic">Ministère de l'Intérieur</div>
        <div class="republic">Direction de l'État Civil</div>
        <div class="document-title">{{ $certificate->type->label() }}</div>
    </div>

    <div class="content-box">
        <div class="section">
            <span class="label">Numéro de Registre (Référence) :</span>
            <span class="value">{{ $certificate->reference_number }}</span>
        </div>
        <div class="section">
            <span class="label">Centre d'État Civil :</span>
            <span class="value">{{ $certificate->center }}</span>
        </div>
        
        <div style="margin-top: 30px;">
            <div class="section">
                <span class="label">Prénom & Nom :</span>
                <span class="value">{{ $certificate->applicant_first_name }} {{ $certificate->applicant_last_name }}</span>
            </div>
            @if($certificate->applicant_cni)
            <div class="section">
                <span class="label">Numéro CNI :</span>
                <span class="value">{{ $certificate->applicant_cni }}</span>
            </div>
            @endif
        </div>

        <div style="margin-top: 20px; border-top: 1px solid #edf2f7; padding-top: 15px;">
            @foreach($certificate->data as $key => $val)
                <div class="section">
                    <span class="label">{{ ucfirst(str_replace('_', ' ', $key)) }} :</span>
                    <span class="value">{{ $val }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="footer">
        <div class="signature-box">
            <div class="label">Fait à {{ $certificate->center }}, le {{ $certificate->validated_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            <div style="margin-top: 10px; font-weight: bold;">L'Officier de l'État Civil</div>
            <div style="margin-top: 5px; font-style: italic; font-size: 11px;">(Signé Électroniquement)</div>
        </div>

        <div class="qr-code">
            <img src="data:image/png;base64,{{ $qrCode }}" width="100">
            <div style="font-size: 8px; text-align: center; margin-top: 5px;">Scanner pour vérifier l'authenticité</div>
        </div>
        
        <div class="meta">
            Généré par SIG-EC le {{ $timestamp }} | ID Unique: {{ $certificate->uuid }}
        </div>
    </div>
</body>
</html>
