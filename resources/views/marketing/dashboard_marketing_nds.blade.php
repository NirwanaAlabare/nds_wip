@extends('layouts.index')

{{-- Dashboard Marketing NDS: isi dari MarketingDashboardController::board() (desain proto/marketing.html, data contoh), dibungkus iframe setinggi sisa layar.
     Navbar = navbar marketing NDS. Tema terang/gelap ikut tombol tema di navbar (iframe ikut diganti). --}}
@php($mktTheme = true)

@section('custom-link')
    <style>
        .mkt-dash-frame { display: block; width: 100%; min-height: 640px; border: 0; border-radius: 12px; background: #f3f4f7; }
        html[data-theme="dark"] body, html[data-theme="dark"] .content-wrapper { background: #0a1222 !important; }
        html[data-theme="dark"] .mkt-dash-frame { background: #0a1222; }
        html[data-theme="dark"] .main-footer { background: #111c31; border-top-color: #22324e; color: #a6b6cc; }
        html[data-theme="dark"] .main-footer a.text-dark { color: #d3ddec !important; }
    </style>
@endsection

@section('content')
    <iframe id="mkt-dash-frame" class="mkt-dash-frame" src="{{ route('dashboard-marketing-board', request()->boolean('lama') ? ['lama' => 1] : []) }}" title="Dashboard Marketing"></iframe>
@endsection

@section('custom-script')
    <script>
        (function () {
            const frame = document.getElementById('mkt-dash-frame');
            const fit = () => {
                const top = frame.getBoundingClientRect().top + window.scrollY;
                frame.style.height = Math.max(640, window.innerHeight - top - 12) + 'px';
            };
            fit();
            window.addEventListener('resize', fit);

            // Samakan tema iframe dengan halaman (tombol tema ada di navbar marketing).
            const sync = () => {
                try { frame.contentDocument.documentElement.setAttribute('data-theme', document.documentElement.getAttribute('data-theme') || 'light'); } catch (e) {}
            };
            frame.addEventListener('load', sync);
            new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        })();
    </script>
@endsection
