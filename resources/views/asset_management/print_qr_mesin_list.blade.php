<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html,
        body {
            font-family: sans-serif;
            margin: 0;
            min-height: 100%;
            background: #f8bbd0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            padding: 1cm;
            box-sizing: border-box;
        }

        .qr-print-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-content: flex-start;
            min-height: calc(100vh - 2cm);
            background: #f8bbd0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .qr-print-item {
            width: 4.5cm;
            height: 6.5cm;
            box-sizing: border-box;
            border: 1px solid #333;
            background: #f8bbd0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4px;
            overflow: hidden;
            page-break-inside: avoid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .qr-print-item img {
            width: 3.8cm;
            height: 3.8cm;
        }

        .qr-print-item .qr-code-label {
            font-weight: bold;
            font-size: 13px;
            margin-top: 6px;
        }

        .qr-print-item .qr-serial-label {
            font-size: 11px;
            color: #555;
        }
    </style>
</head>
<body onload="window.print();">
    <div class="qr-print-grid">
        @forelse ($units as $unit)
            @for ($i = 0; $i < 2; $i++)
                <div class="qr-print-item">
                    <img src="data:image/svg+xml;base64,{{ $unit->qr }}">
                    <div class="qr-code-label">{{ $unit->kode_qr }}</div>
                    <div class="qr-serial-label">{{ $unit->serial_number }}</div>
                </div>
            @endfor
        @empty
            <p>Tidak ada unit dengan Serial Number & Foto lengkap pada periode ini.</p>
        @endforelse
    </div>
</body>
</html>
