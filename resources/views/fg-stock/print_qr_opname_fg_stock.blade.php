<!DOCTYPE html>
<html>

<head>
    <title>QR No. Carton</title>
    <style>
        @page {
            margin: 0;
            size: 74mm 105mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: sans-serif;
        }

        .wrap-table {
            width: 74mm;
            height: 105mm;
            border-collapse: collapse;
        }

        .wrap-table td {
            text-align: center;
            vertical-align: middle;
            padding: 2mm;
        }

        .card {
            width: 68mm;
            text-align: left;
            padding: 3mm;
        }

        .header {
            border-left: 1.2mm solid #222;
            padding-left: 2mm;
            margin-bottom: 2mm;
            padding-top: 2mm;
            padding-bottom: 4mm;
        }

        .title {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            color: #222;
            line-height: 1.1;
        }

        .subtitle {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            line-height: 1.1;
        }

        .qr-wrap {
            width: 100%;
            text-align: left;
            margin-bottom: 2mm;
        }

        img {
            display: block;
            width: 60mm;
            height: 60mm;
            margin: 0;
            margin-left: 2mm;
            /* ubah angka di atas untuk geser QR: 0mm = mepet kiri, makin besar makin ke kanan */
        }

        .info-table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0;
            font-size: 21px;
            font-weight: bold;
            line-height: 1.3;
            color: #222;
            white-space: nowrap;
        }

        .info-table td.label {
            text-align: left;
            padding-right: 1mm;
        }

        .info-table td.colon {
            text-align: center;
            padding: 0 0.5mm;
        }

        .info-table td.value {
            text-align: left;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <table class="wrap-table">
        <tr>
            <td>
                <div class="card">
                    <div class="header">
                        <div class="title">QR Carton</div>
                        <div class="subtitle">Scan untuk cek stok</div>
                    </div>
                    <div class="qr-wrap">
                        <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(300)->generate($no_carton)) !!}">
                    </div>
                    <table class="info-table">
                        @if ($no_pallet)
                            <tr>
                                <td class="label">No. Pallet</td>
                                <td class="colon">:</td>
                                <td class="value">{{ $no_pallet }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">No. Carton</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $no_carton }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
