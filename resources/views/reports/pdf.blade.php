<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 28px 34px 54px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }

        .header-image,
        .footer-image {
            text-align: center;
        }

        .header-image img,
        .footer-image img {
            max-width: 100%;
            height: auto;
        }

        .header-image {
            margin-bottom: 20px;
        }

        .text-header {
            width: 100%;
            margin-bottom: 22px;
            border-bottom: 1px solid #000;
            padding-bottom: 12px;
        }

        .text-header td {
            vertical-align: top;
        }

        .text-header .right {
            text-align: right;
        }

        h1 {
            text-align: center;
            text-transform: uppercase;
            font-size: 17px;
            margin: 0 0 8px 0;
        }

        h2 {
            text-align: center;
            text-transform: uppercase;
            font-size: 14px;
            margin: 0 0 26px 0;
        }

        .section {
            margin-top: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 7px 10px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            font-weight: bold;
        }

        .meta th {
            width: 22%;
            text-align: left;
            white-space: nowrap;
        }

        .meta td {
            text-align: left;
        }

        .summary {
            margin-top: 20px;
        }

        .summary td {
            width: 25%;
            height: 50px;
            text-align: left;
        }

        .summary-label {
            display: block;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .summary-value {
            display: block;
            font-size: 15px;
        }

        .data-table {
            margin-top: 8px;
            table-layout: auto;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -34px;
            font-size: 10px;
        }

        .footer-text {
            border-top: 1px solid #000;
            padding-top: 6px;
        }

        .page-number {
            text-align: right;
        }
    </style>
</head>
<body>
    @if ($headerImageSrc)
        <div class="header-image">
            <img src="{{ $headerImageSrc }}" alt="En-tête">
        </div>
    @else
        <table class="text-header">
            <tr>
                <td>
                    <strong>{{ $identity['brand_name'] }}</strong><br>
                    {{ $identity['entity_name'] }}<br>
                    {{ $identity['service_name'] }}
                </td>
                <td class="right">
                    <strong>{{ $identity['classification_label'] }}</strong><br>
                    {{ $identity['document_nature'] }}
                </td>
            </tr>
        </table>
    @endif

    @if (! blank($identity['document_nature']))
        <h1>{{ $identity['document_nature'] }}</h1>
    @endif

    <h2>{{ $title }}</h2>

    <div class="section">
        <div class="section-title">Informations du rapport :</div>
        <table class="meta">
            <tr>
                <th>Référence</th>
                <td>{{ $reference }}</td>
                <th>Période</th>
                <td>{{ $periode }}</td>
            </tr>
            <tr>
                <th>Objet</th>
                <td>{{ $title }}</td>
                <th>Établi par</th>
                <td>{{ $user?->name ?? 'Système' }}</td>
            </tr>
            <tr>
                <th>Date de génération</th>
                <td colspan="3">{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    @if ($summary !== [])
        <div class="section summary">
            <div class="section-title">Synthèse :</div>
            <table>
                <tr>
                    @foreach (array_slice($summary, 0, 4, true) as $label => $value)
                        <td>
                            <span class="summary-label">{{ $label }}</span>
                            <span class="summary-value">{{ $value }}</span>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

    <div class="section">
        @if (! blank($identity['table_title']))
            <div class="section-title">{{ $identity['table_title'] }} :</div>
        @endif

        <table class="data-table">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($columns as $column)
                            <td>{{ $row[$column] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        @if ($footerImageSrc)
            <div class="footer-image">
                <img src="{{ $footerImageSrc }}" alt="Pied de page">
            </div>
        @elseif (! blank($identity['footer_label']))
            <div class="footer-text">{{ $identity['footer_label'] }}</div>
        @endif
    </div>
</body>
</html>
