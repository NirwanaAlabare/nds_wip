{{-- Pesan error untuk link PDF Barcode per Rak. Meta brk-error dibaca halaman daftar rak (tab pembuka). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="brk-error" content="1">
    <title>Barcode per Rak</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f5f7fb; color: #0f2942; font-family: system-ui, "Segoe UI", sans-serif; }
        .kartu { max-width: 440px; margin: 16px; background: #fff; border: 1px solid #dde5f0; border-radius: 14px; padding: 26px 24px; text-align: center; box-shadow: 0 12px 26px -16px rgba(30, 58, 95, .45); }
        .ikon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 16px; background: #fee2e2; color: #b91c1c; display: grid; place-items: center; font-size: 26px; font-weight: 700; }
        h1 { font-size: 17px; margin: 0 0 8px; }
        p { font-size: 14px; color: #475569; margin: 0 0 18px; line-height: 1.5; }
        a { display: inline-block; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; padding: 9px 16px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="kartu">
        <div class="ikon">!</div>
        <h1>PDF barcode tidak bisa dibuat</h1>
        <p id="brk-pesan">{{ $pesan }}</p>
        <a href="{{ route('barcode-rak') }}">Kembali ke Barcode per Rak</a>
    </div>
</body>
</html>
