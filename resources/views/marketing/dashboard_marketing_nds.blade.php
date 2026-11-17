@extends('layouts.index')

{{-- Dashboard Marketing NDS: isi dari MarketingDashboardController::board() (data contoh), dibungkus iframe setinggi sisa layar. --}}
@section('custom-link')
    <style>
        .mkt-dash-frame { display: block; width: 100%; min-height: 640px; border: 0; border-radius: 12px; background: #f3f4f7; }
    </style>
@endsection

@section('content')
    <iframe id="mkt-dash-frame" class="mkt-dash-frame" src="{{ route('dashboard-marketing-board') }}" title="Dashboard Marketing"></iframe>
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
        })();
    </script>
@endsection
