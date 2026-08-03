<!DOCTYPE html>
<html>
<head>
    <title>QR No. Carton</title>
    <style>
        @page {
            margin: 0;
            size: 180pt 210pt;
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
            width: 180pt;
            height: 210pt;
            border-collapse: collapse;
        }

        .wrap-table td {
            text-align: center;
            vertical-align: middle;
            padding-top: 20pt;
        }

        img {
            width: 150px;
            height: 150px;
            margin-bottom: 8px;
        }

        .no-pallet {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .5px;
            color: #333;
            word-wrap: break-word;
        }

        .no-carton {
            margin: 0;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: .5px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <table class="wrap-table">
        <tr>
            <td>
                @if ($no_pallet)
                    <div class="no-pallet">No. Pallet: {{ $no_pallet }}</div>
                @endif
                <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(300)->generate($no_carton)) !!}">
                <div class="no-carton">{{ $no_carton }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
