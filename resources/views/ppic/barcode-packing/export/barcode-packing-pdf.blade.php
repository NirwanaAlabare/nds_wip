<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Barcode Label</title>
    <style>
        @page { margin: 5px; }

        body { margin: 5px; }

        .label-container {
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 15px;
            border: 1px solid #ccc;
            padding-top: 15px;
            padding-bottom: 5px;
        }

        .barcode {
            width: 70%;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 15px;
            margin: auto;
        }

        .barcode img {
            width: 100%;
            height: auto;
        }

        .barcode-number {
            text-align: center;
            font-size: 25px;
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .label-info {
            text-align: center;
            line-height: 1.5;
            padding: 10px;
        }

        .label-info table {
            width: auto;
            margin: auto;
        }

        .label-info td:first-child {
            font-weight: bold;
        }

        .label-info td:nth-child(2) {
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>
</head>
<body>
    <div class="label-container">
        <div class="barcode">
            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($masterSo->id, 'C128') }}" alt="Barcode">
        </div>
        <div class="barcode-number">{{ $masterSo->id }}</div>
        <div class="label-info">
            <table>
                <tr>
                    <td>WS</td>
                    <td><b>:</b></td>
                    <td>{{ $masterSo->kpno }}</td>
                </tr>
                <tr>
                    <td>STYLE</td>
                    <td><b>:</b></td>
                    <td>{{ $masterSo->styleno }}</td>
                </tr>
                <tr>
                    <td>COLOR</td>
                    <td><b>:</b></td>
                    <td>{{ $masterSo->color }}</td>
                </tr>
                <tr>
                    <td>SIZE</td>
                    <td><b>:</b></td>
                    <td>{{ $masterSo->size.($masterSo->dest && $masterSo->dest != "-" ? $masterSo->dest : "") }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
