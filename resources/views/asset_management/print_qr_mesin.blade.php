<!DOCTYPE html>
<html>
<head>
    <title>QR Mesin</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: sans-serif;
            text-align: center;
        }

        img {
            width: 150px;
        }

        h5 {
            margin-top: 6px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(300)->generate($kode_qr)) !!}">
    <h5>{{ $kode_qr }}</h5>
</body>
</html>
