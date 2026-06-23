<!DOCTYPE html>
<html>
<head>
    <title>QR Mesin Sewa</title>
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
    @foreach ($kodeQrList as $kode)
        <div @if (!$loop->last) style="page-break-after: always;" @endif>
            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(300)->generate($kode)) !!}">
            <h5>{{ $kode }}</h5>
        </div>
    @endforeach
</body>
</html>
