<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            size: 60mm 40mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .label {
            width: 60mm;
            height: 40mm;
            page-break-after: always;
            text-align: center;
            box-sizing: border-box;
        }

        .label:last-child {
            page-break-after: auto;
        }

        .content {
            padding-top: 5mm;
        }

        .qr-code img {
            width: 20mm;
            height: 20mm;
        }

        .designation {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 2mm;
            margin-bottom: 1mm;
            width: 100%;
            overflow: hidden;
        }

        .reference {
            font-size: 7pt;
            color: #333;
        }
    </style>
</head>
<body>
    @foreach($labels as $label)
        <div class="label">
            <div class="content">
                <div class="qr-code">
                    <img src="{{ $label['qrCode'] }}" alt="QR Code">
                </div>
                <div class="designation">{{ $label['article']->designation }}</div>
                <div class="reference">{{ $label['article']->numero_reference }}</div>
            </div>
        </div>
    @endforeach
</body>
</html>
