<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#0b1120">
    <title>Akses Dikunci</title>
    <style>
        :root {
            color-scheme: dark;

            --bg: #0b1120;
            --glow-1: rgba(99, 102, 241, .22);
            --glow-2: rgba(245, 158, 11, .10);
            --card: #151e32;
            --card-2: #1b2640;
            --text: #f1f5f9;
            --text-2: #cbd5e1;
            --muted: #94a3b8;
            --line: #2a3754;
            --accent: #a5b4fc;
            --accent-bg: #4f46e5;
            --accent-bg-hover: #5b54ec;
            --accent-soft: rgba(129, 140, 248, .14);
            --warn: #fbbf24;
            --warn-soft: rgba(251, 191, 36, .12);
            --ok: #4ade80;
            --focus: #fcd34d;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--text);
            background-color: var(--bg);
            background-image:
                radial-gradient(600px 400px at 50% -10%, var(--glow-1), transparent 70%),
                radial-gradient(500px 300px at 90% 110%, var(--glow-2), transparent 70%);
            -webkit-font-smoothing: antialiased;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: linear-gradient(180deg, var(--card-2), var(--card));
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 36px 32px 24px;
            text-align: center;
            box-shadow: 0 24px 60px -24px rgba(0, 0, 0, .6), inset 0 1px 0 rgba(255, 255, 255, .04);
            animation: rise .5s ease-out;
        }

        .icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--warn-soft);
            color: var(--warn);
            position: relative;
        }
        .icon::after {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid var(--warn);
            opacity: .2;
            animation: pulse 2.4s ease-out infinite;
        }
        .icon svg { width: 40px; height: 40px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .02em;
            margin-bottom: 14px;
        }
        .badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--warn);
            box-shadow: 0 0 0 3px var(--warn-soft);
        }

        h1 { font-size: 24px; line-height: 1.3; margin: 0 0 8px; letter-spacing: -.01em; }
        .lead { color: var(--text-2); line-height: 1.6; margin: 0; font-size: 15px; }

        .info {
            margin: 22px 0 24px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(11, 17, 32, .55);
            border: 1px solid var(--line);
            text-align: left;
            font-size: 13px;
            line-height: 1.55;
            color: var(--text-2);
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .info svg { flex-shrink: 0; width: 18px; height: 18px; margin-top: 1px; color: var(--accent); }

        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 10px;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--line);
            background: var(--card-2);
            color: var(--text);
            text-decoration: none;
            transition: transform .15s, background .15s, border-color .15s;
        }
        .btn:hover { transform: translateY(-1px); border-color: #3b4a6b; background: #22304f; }
        .btn:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }
        .btn-primary { background: var(--accent-bg); border-color: var(--accent-bg); color: #fff; }
        .btn-primary:hover { background: var(--accent-bg-hover); border-color: var(--accent-bg-hover); }
        .btn svg { width: 16px; height: 16px; }
        .btn.loading svg { animation: spin .8s linear infinite; }


        .footer {
            margin-top: 22px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .footer code {
            font-family: ui-monospace, "Cascadia Code", Consolas, monospace;
            color: var(--text-2);
            background: rgba(255, 255, 255, .05);
            padding: 1px 6px;
            border-radius: 4px;
        }

        @keyframes rise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
        @keyframes pulse { 0% { transform: scale(.9); opacity: .35; } 100% { transform: scale(1.25); opacity: 0; } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            *, *::after { animation: none !important; transition: none !important; }
        }
        @media (max-width: 420px) {
            .card { padding: 28px 18px 20px; }
            h1 { font-size: 21px; }
            .btn { flex: 1; }
        }
    </style>
</head>
<body>
    <main class="card" role="main">
        <div class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="11" width="16" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                <circle cx="12" cy="16" r="1.2" fill="currentColor"/>
            </svg>
        </div>

        <span class="badge"><span class="dot"></span>Stock Opname</span>
        <h1>Aplikasi Sedang Dikunci</h1>
        <p class="lead">Mohon maaf, akses ke aplikasi ini sedang ditutup sementara. Data Anda tetap aman.</p>

        <div class="info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
            </svg>
            <span>Biasanya ini terjadi saat proses tutup buku, stock opname, atau pemeliharaan sistem. Silakan coba lagi nanti atau hubungi administrator / tim IT.</span>
        </div>

        <div class="actions">
            <button type="button" class="btn btn-primary" id="btnRetry">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/>
                </svg>
                Coba Lagi
            </button>
            <button type="button" class="btn" onclick="history.length > 1 ? history.back() : location.href='/'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </button>
        </div>

        <div class="footer">
            <span>Kode <code>403</code></span>
            <span>&middot;</span>
            <span>{{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
        </div>
    </main>

    <script>
        document.getElementById('btnRetry').addEventListener('click', function () {
            this.classList.add('loading');
            this.disabled = true;
            location.reload();
        });
    </script>
</body>
</html>
