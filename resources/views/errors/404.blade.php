<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            max-width: 400px;
        }

        .code {
            font-size: 64px;
            font-weight: bold;
            color: #2563eb;
            margin: 0;
        }

        h1 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <p class="code">404</p>
        <h1>Not Found</h1>
        <p>{{ $message ?? 'Halaman yang Anda cari tidak ditemukan.' }}</p>
        <br>
        {{-- Kembali ke halaman sebelumnya, ke home jika tidak ada riwayat --}}
        <a href="{{ route('home') }}" class="btn" onclick="if (history.length > 1) { event.preventDefault(); history.back(); }">Back</a>
    </div>
</body>
</html>
